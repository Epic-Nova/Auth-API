<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AttachableData', function (Blueprint $table) {

            $table->string('AttachableDataName')
                ->comment('The name of the AttachableData.');

            $table->set('AttachableForType', ['Authenticatable', 'Account', 'Profile'])
                ->comment('Where the AttachableData can be attached to.
                    Authenticatable: AttachableData can be attached to any Authenticatable.
                    Account: AttachableData can be attached to any Account.
                    Profile: AttachableData can be attached to any Profile.');

            $table->enum('AuthenticationType', ['NoAuthenticationNeeded', 'NeedsAuthentication', 'NeedsApproval', 'NeedsApprovalAndAuthentication'])
                ->default('NoAuthenticationNeeded')
                ->comment('The type of authentication needed for the AttachableData when some action is performed on it.
                    NoAuthenticationNeeded: No authentication is needed.
                    NeedsAuthentication: The action needs to be authenticated.
                    NeedsApproval: The action needs to be approved by an ApprovalEntity.
                    NeedsApprovalAndAuthentication: The action needs to be approved by an ApprovalEntity and authenticated.');

            $table->enum('MultiFactorAuthenticationType', ['NoMultiFactorAuthenticationNeeded', 'UseMultiFactorIfAvailable', 'ForceMultiFactor'])
                ->default('NoMultiFactorAuthenticationNeeded')
                ->comment('The type of multi factor authentication needed for the AttachableData when some action is performed on it.
                    NoMultiFactorAuthenticationNeeded: No multi factor authentication is needed.
                    UseMultiFactorIfAvailable: Multi factor authentication is needed if available.
                    ForceMultiFactor: Multi factor authentication is needed.');

            $table->set('AuthenticationAndOrApprovalOn', ['Create', 'Update', 'Delete'])
                ->nullable()
                ->comment('When the authentication should be performed.
                    Create: When the AttachableData is created.
                    Update: When the AttachableData is updated.
                    Delete: When the AttachableData is deleted.');

            $table->ulid('AuthenticationProviderIdentifier')
                ->nullable()
                ->comment('The reference to the AuthenticationProvider. Only used if AuthenticationType is NeedsAuthentication or NeedsApprovalAndAuthentication.');

            $table->foreign('AuthenticationProviderIdentifier', 'AttachableData_AuthenticationProviderIdentifier_Foreign')
                ->references('AuthenticationProviderIdentifier')
                ->on('AuthenticationProviders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUlid('ApprovalEntityIdentifier')
                ->nullable()
                ->comment('The ApprovalEntityIdentifier of the ApprovalEntity that this parameter belongs to. Only used if AuthenticationType is NeedsApproval or NeedsAuthenticationAndApproval.')
                ->constrained('ApprovalEntities', 'ApprovalEntityIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('bIsUnique')
                ->default(false)
                ->comment('If true, then the AttachableData must be unique for the AttachableForType.');

            $table->boolean('bCanHaveMultiple')
                ->default(true)
                ->comment('If true, then the AttachableData can have multiple instances for the AttachableForType.');

            $table->integer('MaxAllowedCharacters')
                ->nullable(false)
                ->default(128)
                ->comment('The maximum allowed characters for the AttachableData.');

            $table->boolean('bInvertSafeList')
                ->default(false)
                ->comment('If true, then SafeList will be a list of characters that are not allowed, otherwise it will be a list of characters that only are allowed.');

            $table->string('SafeList')
                ->nullable()
                ->default('!,@,#,$,%,^,&,*,(,),{,},[,],|,\,/,<,>,?,~,`')
                ->comment('A list of characters that are allowed or not allowed depending on bInvertSafeList.');

            $table->string('ValueMatch')
                ->nullable()
                ->comment('If set, then the value should match the requirements in ValueMatchType.')
                ->default(null)
                ->comment('The value that should be matched.');

            $table->enum('ValueMatchType', ['NoMatchType', 'Contains', 'StartsWith', 'EndsWith', 'RegExp'])
                ->default('NoMatchType')
                ->comment('The type of match that should be performed on the value.
                    None: No match should be performed.
                    Contains: The value should contain the ValueMatch.
                    StartsWith: The value should start with the ValueMatch.
                    EndsWith: The value should end with the ValueMatch.
                    RegExp: The value should match the ValueMatch as a regular expression.');

            $table->set('AttachmentPossibility', ['OnAccountCreation', 'OnApplicationCreation', 'OnProfileCreation', 'InSettings'])
                ->nullable(false)
                ->default('InSettings')
                ->comment('Where the AttachableData can be attached to.
                    OnAccountCreation: The AttachableData can be attached to an Account when it is created.
                    OnApplicationCreation: The AttachableData can be attached to an Application when it is created.
                    OnProfileCreation: The AttachableData can be attached to a Profile when it is created.
                    InSettings: The AttachableData can be attached to an Account, Application or Profile in the settings.');

            $table->enum('AccessType', ['NoAccess', 'PublicAvailable', 'NeedsAuthentication', 'NeedsExtraMultiFactorAuthenticationIfAvailable', 'TrustedApplicationsOnly'])
                ->default('NeedsAuthentication')
                ->comment('The type of access needed for the AttachableData.
                    NoAccess: The AttachableData is not accessible.
                    PublicAvailable: The AttachableData is public available.
                    NeedsAuthentication: The AttachableData needs authentication to be accessed.
                    NeedsExtraMultiFactorAuthenticationIfAvailable: The AttachableData needs extra multi factor authentication if available to be accessed.
                    TrustedApplicationsOnly: The AttachableData is only available for trusted applications to be accessed.');

            $table->primary('AttachableDataName', 'AttachableData_PrimaryIndex');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AttachableData');
    }
};
