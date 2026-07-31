<?php

use App\Models\Team;
use App\Models\User;
use App\Services\Teams\PersonalTeamNaming;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $naming = app(PersonalTeamNaming::class);

        Team::query()
            ->where('is_personal', true)
            ->where('name', 'Personal')
            ->each(function (Team $team) use ($naming): void {
                $owner = $team->owner();

                if (! $owner instanceof User) {
                    return;
                }

                $team->update([
                    'name' => $naming->nameFor($owner),
                    'slug' => $naming->slugFor($owner, $team->id),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
