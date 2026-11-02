<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->timestamp('teacher_acknowledged_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->dropColumn('teacher_acknowledged_at');
        });
    }
};
