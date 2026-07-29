<?php

namespace App\Console\Commands;

use App\Models\Authentication\Account;
use App\Models\Authentication\AttachedAuthenticationProvider;
use App\Models\Authentication\Authenticatable;
use App\Models\Authentication\AuthenticationProvider;
use App\Models\Authentication\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLocalAdminCommand extends Command
{
    protected $signature = 'nova:local-admin {--name=Admin : Display and profile name for the local administrator}';

    protected $description = 'Create the idempotent local Nova ID administrator used by the Celestia integration test';

    public function handle(): int
    {
        if (! config('keyforge.local_login_bypass')) {
            $this->error('NOVA_LOCAL_LOGIN_BYPASS must be true before a local bypass administrator can be created.');

            return self::FAILURE;
        }

        $name = trim((string) $this->option('name')) ?: 'Admin';
        $profile = Profile::query()->where('ProfileNickname', $name)->first();
        $authenticatable = $profile
            ? Authenticatable::query()->find($profile->AccountIdentifier)
            : null;

        if (! $authenticatable) {
            $authenticatableId = (string) Str::uuid();
            Authenticatable::query()->create([
                'AuthenticatableIdentifier' => $authenticatableId,
                'AuthenticatableType' => 'Account',
            ]);
            Account::query()->create([
                'AccountIdentifier' => $authenticatableId,
                'Region' => 'Europe',
            ]);
            Profile::query()->create([
                'ProfileIdentifier' => (string) Str::ulid(),
                'ProfileName' => $name,
                'ProfileHashtagIdentifier' => '0001',
                'AccountIdentifier' => $authenticatableId,
                'ProfileNickname' => $name,
                'bIsProfileVerified' => true,
            ]);
            $authenticatable = Authenticatable::query()->findOrFail($authenticatableId);
        }

        $provider = AuthenticationProvider::query()->firstOrCreate(
            ['ProviderFriendlyIdentifier' => 'com.epicnova.authentication_provider.local-bypass'],
            [
                'AuthenticationProviderIdentifier' => (string) Str::ulid(),
                'ProviderDescription' => 'Local Celestia Nova integration-test bypass provider.',
                'ProviderLocation' => 'CDN',
                'ProviderState' => 'CreatorOnly',
                'bCanUserLoginWithProvider' => true,
            ]
        );

        AttachedAuthenticationProvider::query()->firstOrCreate(
            [
                'AuthenticatableIdentifier' => $authenticatable->AuthenticatableIdentifier,
                'AuthenticationProviderIdentifier' => $provider->AuthenticationProviderIdentifier,
            ],
            [
                'AttachedAuthenticationProviderIdentifier' => (string) Str::ulid(),
                'bIsPrimary' => true,
                'ProviderData' => json_encode(['local_test' => true], JSON_THROW_ON_ERROR),
                'ProviderState' => 'Verified',
            ]
        );

        $role = 'com.epicnova.roles.admin';
        $shard = 'celestianova';
        $scope = 'deployment.remote.control';
        $restrictions = DB::connection('RestrictionsConnection');

        $restrictions->table('Shards')->updateOrInsert(
            ['ShardIdentifier' => $shard],
            ['ShardDescription' => 'Celestia Nova local integration-test shard.', 'ShardPublicKey' => 'local-bypass']
        );
        $restrictions->table('Scopes')->updateOrInsert(
            ['ScopeIdentifier' => $scope],
            ['ScopeDescription' => 'Operate Celestia Nova deployments.', 'ScopeShard' => $shard, 'bIsActive' => true]
        );
        $restrictions->table('Roles')->updateOrInsert(['RoleIdentifier' => $role], []);
        $restrictions->table('RoleScopeReferences')->updateOrInsert(
            ['RoleIdentifier' => $role, 'ScopeIdentifier' => $scope],
            ['RoleScopeReferenceIdentifier' => (string) Str::ulid()]
        );
        $restrictions->table('AuthenticatableRoleReferences')->updateOrInsert(
            ['AuthenticatableIdentifier' => $authenticatable->AuthenticatableIdentifier, 'RoleIdentifier' => $role],
            ['AuthenticatableRoleReferenceIdentifier' => (string) Str::ulid()]
        );

        $this->info('Local Nova ID administrator is ready.');
        $this->line('Profile: '.$name);
        $this->line('Authenticatable identifier: '.$authenticatable->AuthenticatableIdentifier);
        $this->line('Provider: '.$provider->ProviderFriendlyIdentifier);
        $this->comment('This account uses the explicitly enabled local login bypass; no password is stored or required.');

        return self::SUCCESS;
    }
}
