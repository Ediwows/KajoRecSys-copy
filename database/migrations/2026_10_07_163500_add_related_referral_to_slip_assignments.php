<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->foreignId('related_assignment_id')
                ->nullable()
                ->after('student_id')
                ->constrained('slip_assignments')
                ->nullOnDelete();
            $table->unique(['related_assignment_id', 'slip']);
        });
    }

    public function down(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->dropUnique(['related_assignment_id', 'slip']);
            $table->dropConstrainedForeignId('related_assignment_id');
        });
    }
};
