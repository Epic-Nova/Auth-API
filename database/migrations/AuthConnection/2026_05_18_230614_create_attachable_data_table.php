<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('AttachableData', function (Blueprint $table) {
            $table->string('AttachableDataName', 64)
                ->comment('The name of the AttachableData.')
                ->primary();

            $table->string('AttachableDataDescription', 256)
                ->nullable()
                ->comment('A description of the AttachableData.');

            $table->string('AttachableDataCategory', 64)
                ->nullable()
                ->comment('A category of the AttachableData.');

            $table->set('AttachableForType', ['Authenticatable', 'Account', 'Profile', 'AuthenticationProvider'])
                ->comment('Where the AttachableData can be attached to.');

            $table->enum(
                'AuthenticationType',
                ['NoAuthenticationNeeded', 'NeedsAuthentication', 'NeedsApproval', 'NeedsApprovalAndAuthentication']
            )
                ->default('NoAuthenticationNeeded')
                ->comment('The authentication required for this data.');

            $table->enum(
                'MultiFactorAuthenticationType',
                ['NoMultiFactorAuthenticationNeeded', 'UseMultiFactorIfAvailable', 'ForceMultiFactor']
            )
                ->default('NoMultiFactorAuthenticationNeeded')
                ->comment('Multi-factor authentication requirements.');

            $table->set('AuthenticationAndOrApprovalOn', ['Execute', 'Create', 'Update', 'Delete'])
                ->nullable()
                ->comment('When authentication or approval should occur.');

            $table->foreignUlid('AuthenticationProviderIdentifier')
                ->nullable()
                ->comment('Authentication provider used for this data when needed.')
                ->constrained('AuthenticationProviders', 'AuthenticationProviderIdentifier')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUlid('ApprovalEntityIdentifier')
                ->nullable()
                ->comment('Approval entity used for this data when needed.')
                ->constrained('ApprovalEntities', 'ApprovalEntityIdentifier')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('bIsUnique')
                ->default(false)
                ->comment('If true, the data must be unique for its attachment scope.');

            $table->boolean('bCanHaveMultiple')
                ->default(true)
                ->comment('If true, multiple instances are allowed per scope.');

            $table->integer('MaxAllowedCharacters')
                ->default(128)
                ->comment('The maximum allowed characters for this data.');

            $table->boolean('bInvertSafeList')
                ->default(false)
                ->comment('If true, SafeList becomes a deny list.');

            $table->boolean('bUseSafeList')
                ->default(false)
                ->comment('If true, SafeList will be enforced.');

            $table->string('SafeList')
                ->nullable()
                ->default('!,@,#,$,%,^,&,*,(,),{,},[,],|,\\,/,<,>,?,~,`')
                ->comment('A list of characters allowed or denied depending on settings.');

            $table->string('ValueMatch')
                ->nullable()
                ->comment('A value to validate against using ValueMatchType.');

            $table->enum('ValueMatchType', ['NoMatchType', 'Contains', 'StartsWith', 'EndsWith', 'RegExp'])
                ->default('NoMatchType')
                ->comment('The validation type for ValueMatch.');

            $table->set(
                'AttachmentPossibility',
                ['OnAccountCreation', 'OnApplicationCreation', 'OnProfileCreation', 'InSettings']
            )
                ->default('InSettings')
                ->comment('Where this data can be attached.');

            $table->enum(
                'AccessType',
                [
                    'NoAccess',
                    'PublicAvailable',
                    'NeedsAuthentication',
                    'NeedsExtraMultiFactorAuthenticationIfAvailable',
                    'TrustedApplicationsOnly',
                ]
            )
                ->default('NeedsAuthentication')
                ->comment('The access level required to read or mutate this data.')
                ->index();

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time this AttachableData was created.')
                ->index();

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('The date and time this AttachableData was last updated.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AttachableData');
    }
};
