<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->text('concerns')->nullable()->after('purpose');
            $table->foreignId('referral_id')->nullable()->after('user_id')->constrained('slip_assignments')->nullOnDelete();
            $table->index(['referral_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex(['referral_id', 'status']);
            $table->dropConstrainedForeignId('referral_id');
            $table->dropColumn('concerns');
        });
    }
};
