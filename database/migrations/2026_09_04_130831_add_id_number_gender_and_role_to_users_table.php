<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_number')->unique()->after('name');

            $table->enum('gender', [
                'male',
                'female'
            ])->after('id_number');

            $table->enum('role', [
                'student',
                'teacher',
                'guidance_counselor'
            ])->default('student')->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'id_number',
                'gender',
                'role'
            ]);
        });
    }
};