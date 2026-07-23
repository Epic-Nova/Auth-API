<?php

namespace App\Console\Commands;

use App\Models\Authentication\Authenticatable;
use App\Models\Authentication\OAuthClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterOAuthClientCommand extends Command
{
    protected $signature = 'oauth:register-client
        {name : Stable human-readable client name}
        {--scope=* : Permitted OAuth scope; repeat for each scope}
        {--audience=* : Permitted token audience; repeat for each audience}
        {--ttl=600 : Access-token lifetime in seconds}';

    protected $description = 'Register a confidential OAuth application and print its secret once.';

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $scopes = array_values(array_filter($this->option('scope')));
        $audiences = array_values(array_filter($this->option('audience')));
        $ttl = (int) $this->option('ttl');

        if ($scopes === [] || $audiences === [] || $ttl < 60 || $ttl > 3600) {
            $this->error('Provide at least one --scope and --audience; --ttl must be between 60 and 3600 seconds.');

            return self::FAILURE;
        }

        if (OAuthClient::query()->where('ClientName', $name)->exists()) {
            $this->error('An OAuth client with this name already exists.');

            return self::FAILURE;
        }

        $clientId = (string) Str::uuid();
        $clientSecret = Str::password(48, symbols: true);

        DB::connection('AuthConnection')->transaction(function () use ($clientId, $clientSecret, $name, $scopes, $audiences, $ttl): void {
            Authenticatable::query()->create([
                'AuthenticatableIdentifier' => $clientId,
                'AuthenticatableType' => 'Application',
            ]);

            OAuthClient::query()->create([
                'OAuthClientIdentifier' => $clientId,
                'ClientName' => $name,
                'ClientSecretHash' => Hash::make($clientSecret),
                'AllowedScopes' => $scopes,
                'AllowedAudiences' => $audiences,
                'AccessTokenTtlSeconds' => $ttl,
                'IsActive' => true,
            ]);
        });

        $this->newLine();
        $this->line('OAuth client registered. Store this secret in KeyForge; it is shown only once.');
        $this->line('client_id='.$clientId);
        $this->line('client_secret='.$clientSecret);

        return self::SUCCESS;
    }
}
