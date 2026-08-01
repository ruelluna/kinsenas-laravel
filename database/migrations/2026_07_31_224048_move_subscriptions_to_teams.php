<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });

        DB::transaction(function () {
            $subscriptions = DB::table('subscriptions')->get();

            foreach ($subscriptions as $subscription) {
                $teamId = DB::table('teams')
                    ->join('team_members', 'teams.id', '=', 'team_members.team_id')
                    ->where('team_members.user_id', $subscription->user_id)
                    ->where('teams.is_personal', true)
                    ->value('teams.id');

                if ($teamId !== null) {
                    DB::table('subscriptions')
                        ->where('id', $subscription->id)
                        ->update(['team_id' => $teamId]);
                }
            }

            $submissions = DB::table('payment_submissions')->get();

            foreach ($submissions as $submission) {
                $teamId = DB::table('teams')
                    ->join('team_members', 'teams.id', '=', 'team_members.team_id')
                    ->where('team_members.user_id', $submission->user_id)
                    ->where('teams.is_personal', true)
                    ->value('teams.id');

                if ($teamId === null) {
                    $teamId = DB::table('users')
                        ->where('id', $submission->user_id)
                        ->value('current_team_id');
                }

                if ($teamId !== null) {
                    DB::table('payment_submissions')
                        ->where('id', $submission->id)
                        ->update(['team_id' => $teamId]);
                }
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
            $table->unique('team_id');
        });

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        DB::transaction(function () {
            $subscriptions = DB::table('subscriptions')->get();

            foreach ($subscriptions as $subscription) {
                $ownerId = DB::table('team_members')
                    ->where('team_id', $subscription->team_id)
                    ->where('role', 'owner')
                    ->value('user_id');

                if ($ownerId !== null) {
                    DB::table('subscriptions')
                        ->where('id', $subscription->id)
                        ->update(['user_id' => $ownerId]);
                }
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropUnique(['team_id']);
            $table->dropColumn('team_id');
            $table->unique('user_id');
        });

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};
