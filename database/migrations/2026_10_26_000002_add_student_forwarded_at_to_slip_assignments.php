<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->timestamp('student_sent_to_teacher_at')->nullable()->after('teacher_acknowledged_at');
        });
    }

    public function down(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->dropColumn('student_sent_to_teacher_at');
        });
    }
};
