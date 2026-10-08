<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cns_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('student_id', 50);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('degree', 20);
            $table->string('year_level', 20);
            $table->string('school_year', 50);
            $table->string('purpose', 40);
            $table->string('purpose_other')->nullable();
            $table->string('pdf_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cns_requests');
    }
};
