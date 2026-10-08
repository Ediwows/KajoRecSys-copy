<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        Schema::table('slip_submissions', function (Blueprint $table) {
            $table->string('review_status', 20)->default('submitted')->after('pdf_path')->index();
            $table->foreignId('approved_by')->nullable()->after('review_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        Schema::table('cns_requests', function (Blueprint $table) {
            $table->string('review_status', 20)->default('submitted')->after('pdf_path')->index();
            $table->foreignId('approved_by')->nullable()->after('review_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        DB::table('slip_assignments')
            ->where('status', 'completed')
            ->update(['status' => 'submitted']);
    }

    public function down(): void
    {
        Schema::table('cns_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['review_status', 'approved_at']);
        });

        Schema::table('slip_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['review_status', 'approved_at']);
        });

        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
        });
    }
};
