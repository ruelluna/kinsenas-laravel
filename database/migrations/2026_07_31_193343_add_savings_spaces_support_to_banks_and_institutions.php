<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_institutions', function (Blueprint $table) {
            $table->json('features')->nullable()->after('is_active');
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->uuid('bank_account_group_id')->nullable()->after('bank_institution_id');
            $table->string('space_role')->nullable()->after('account_label');

            $table->index('bank_account_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropIndex(['bank_account_group_id']);
            $table->dropColumn(['bank_account_group_id', 'space_role']);
        });

        Schema::table('bank_institutions', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }
};
