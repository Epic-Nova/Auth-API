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
        Schema::create('Profiles', function (Blueprint $table) {
            $table->ulid('ProfileIdentifier')
                ->comment('The ULID of the profile.')
                ->primary();

            $table->string('ProfileName', 32)
                ->comment('The identifier of the profile.');

            $table->string('ProfileHashtagIdentifier', 4)
                ->comment('The hashtag identifier used to identify the profile.');

            $table->foreignUuid('AccountIdentifier')
                ->comment('The reference to the owning account.')
                ->constrained('Accounts', 'AccountIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('ProfileNickname', 64)
                ->comment('The nickname of the profile.');

            $table->string('PreferredLanguage', 8)
                ->default('EN_US')
                ->comment('The preferred language of the profile.');

            $table->boolean('bIsProfilePublic')
                ->default(true)
                ->comment('If true, the profile can be discovered by other users.');

            $table->boolean('bIsProfileSearchable')
                ->default(true)
                ->comment('If true, the profile can be found by search.');

            $table->boolean('bIsProfileVerified')
                ->default(false)
                ->comment('If true, the profile is verified.');

            $table->boolean('bIsProfileDeletable')
                ->default(true)
                ->comment('If true, the profile can be deleted by the user.');

            $table->enum('ProfileState', ['Online', 'Offline', 'Away', 'Busy', 'DoNotDisturb'])
                ->default('Offline')
                ->comment('The current state of the profile.');

            $table->enum('ProfileStateBeforeChange', ['Online', 'Offline', 'Away', 'Busy', 'DoNotDisturb'])
                ->default('Offline')
                ->comment('The previous state of the profile before the last change.');

            $table->timestamp('LastStateChange')
                ->useCurrent()
                ->comment('The date and time when the profile state last changed.');

            $table->enum('ProfileStatus', ['Active', 'Banned', 'Suspended', 'Locked', 'Restricted'])
                ->default('Active')
                ->comment('The status of the profile.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the profile was created.')
                ->index();

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('The date and time the profile was last updated.');

            $table->timestamp('DeletedAt')
                ->nullable()
                ->comment('Soft-delete marker for retention workflows.')
                ->index();

            $table->index(['ProfileName', 'AccountIdentifier'], 'Profiles_Index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Profiles');
    }
};
