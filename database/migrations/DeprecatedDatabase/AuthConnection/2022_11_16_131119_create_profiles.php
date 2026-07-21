<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // @TODO: Create a cron job on the Database itself that fetches the languages every 24 hours (for when new languages are added to the db)
    public function GetAllowedPreferredLanguages(): array
    {
        return ['EN_US'];
        $Response = Http::get(config('nova.LanguageAPI.LanguagesEndpoint'));

        $AvailableLanguages = [];
        foreach ($Response->object() as $LanguageObject) {
            $AvailableLanguages[] = $LanguageObject->LanguageCode;
        }

        return $AvailableLanguages;
    }

    public function up()
    {
        Schema::create('Profiles', function (Blueprint $table) {

            $table->ulid('ProfileIdentifier')
                ->nullable(false)
                ->comment('The ulid of the profile.');

            $table->string('ProfileName', 32)
                ->comment('The identifier of the profile.');

            $table->string('ProfileHashtagIdentifier', 4)
                ->comment('The hashtag identifier that is used to identify the profile.');

            $table->foreignUuid('AccountIdentifier')
                ->comment('The reference to the AuthenticatableResource.')
                ->constrained('Accounts', 'AccountIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('ProfileNickname', 64)
                ->comment('The nickname of the profile.');

            $table->enum('PreferredLanguage', $this->GetAllowedPreferredLanguages())
                ->nullable(false)
                ->default('EN_US')
                ->comment('The preferred language of the profile.');

            $table->boolean('bIsProfilePublic')
                ->default(true)
                ->comment('If the profile is public, it can be found by other users.');

            $table->boolean('bIsProfileSearchable')
                ->default(true)
                ->comment('If the profile is searchable, it can be found by other users, even if they are not friends. The profile must be public to be searchable.');

            $table->boolean('bIsProfileVerified')
                ->default(false)
                ->comment('If the profile is verified, it is a real person. This is only used for celebrities and other public figures.');

            $table->boolean('bIsProfileDeletable')
                ->default(true)
                ->comment('If the profile is deletable, it can be deleted by the user.');

            $table->enum('ProfileState', ['Online', 'Offline', 'Away', 'Busy', 'DoNotDisturb'])
                ->default('Offline')
                ->comment('The state of the profile before the last change.
                    Online: The profile is online and can be interacted with.
                    Offline: The profile is offline and cannot be interacted with.
                    Away: The profile is away and cannot be interacted with.
                    Busy: The profile is busy and cannot be interacted with.
                    DoNotDisturb: The profile is in do not disturb mode and cannot be interacted with.');

            $table->enum('ProfileStateBeforeChange', ['Online', 'Offline', 'Away', 'Busy', 'DoNotDisturb'])
                ->default('Offline')
                ->comment('The state of the profile before the last change.
                    Online: The profile is online and can be interacted with.
                    Offline: The profile is offline and cannot be interacted with.
                    Away: The profile is away and cannot be interacted with.
                    Busy: The profile is busy and cannot be interacted with.
                    DoNotDisturb: The profile is in do not disturb mode and cannot be interacted with.');

            $table->timestamp('LastStateChange')
                ->useCurrent()
                ->comment('The date and time when the state of the profile was last changed.');

            $table->enum('ProfileStatus', ['Active', 'Banned', 'Suspended', 'Locked', 'Restricted'])
                ->default('Active')
                ->comment('The status of the profile.
                    Active: The profile is active and can be interacted with.
                    Banned: The profile is banned and cannot be interacted with.
                    Suspended: The profile is suspended and cannot be interacted with.
                    Locked: The profile is locked and cannot be interacted with.
                    Restricted: The profile is restricted and cannot be interacted with.');

            $table->primary('ProfileIdentifier', 'Profiles_PrimaryIndex');
            $table->index(['ProfileName', 'AccountIdentifier'], 'Profiles_IdentifierIndex');

        });
    }

    public function down()
    {
        Schema::dropIfExists('Profiles');
    }
};
