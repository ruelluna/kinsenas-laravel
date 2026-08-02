<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('marketing_emails_opt_in')->default(false)->after('beta_launch_discount_eligible');
            $table->timestamp('marketing_emails_opted_in_at')->nullable()->after('marketing_emails_opt_in');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['marketing_emails_opt_in', 'marketing_emails_opted_in_at']);
        });
    }
};
