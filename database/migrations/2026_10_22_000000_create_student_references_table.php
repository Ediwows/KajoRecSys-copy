<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_references', function (Blueprint $table) {
            $table->id();
            $table->string('id_number')->unique();
            $table->string('name');
            $table->string('gender');
            $table->string('year')->nullable();
            $table->string('course')->nullable();
            $table->json('source_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_references');
    }
};
