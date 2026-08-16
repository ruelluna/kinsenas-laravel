<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_spends', function (Blueprint $table) {
            $table->boolean('expects_reimbursement')->default(false)->after('recipient_id');
            $table->foreignUuid('expected_from_recipient_id')
                ->nullable()
                ->after('expects_reimbursement')
                ->constrained('recipients')
                ->nullOnDelete();
            $table->timestamp('reimbursement_closed_at')->nullable()->after('expected_from_recipient_id');
        });
    }

    public function down(): void
    {
        Schema::table('fund_spends', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expected_from_recipient_id');
            $table->dropColumn(['expects_reimbursement', 'reimbursement_closed_at']);
        });
    }
};
