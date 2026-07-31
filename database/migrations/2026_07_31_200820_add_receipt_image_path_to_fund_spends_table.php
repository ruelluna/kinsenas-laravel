<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_spends', function (Blueprint $table) {
            $table->string('receipt_image_path')->nullable()->after('recipient_id');
        });
    }

    public function down(): void
    {
        Schema::table('fund_spends', function (Blueprint $table) {
            $table->dropColumn('receipt_image_path');
        });
    }
};
