<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_plans', function (Blueprint $table) {
            $table->boolean('allow_editing_spends')->default(false)->after('is_shared_with_team');
        });
    }

    public function down(): void
    {
        Schema::table('savings_plans', function (Blueprint $table) {
            $table->dropColumn('allow_editing_spends');
        });
    }
};
