<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('beta_application_status')->nullable()->after('beta_enrolled_at');
            $table->timestamp('beta_approved_at')->nullable()->after('beta_application_status');
            $table->foreignId('beta_approved_by')->nullable()->after('beta_approved_at')->constrained('users')->nullOnDelete();
        });

        DB::table('users')
            ->whereNotNull('beta_enrolled_at')
            ->whereNull('beta_application_status')
            ->update([
                'beta_application_status' => 'approved',
                'beta_approved_at' => DB::raw('beta_enrolled_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('beta_approved_by');
            $table->dropColumn(['beta_application_status', 'beta_approved_at']);
        });
    }
};
