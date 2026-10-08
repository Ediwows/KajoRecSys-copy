<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cns_requests', function (Blueprint $table) {
            $table->string('last_name', 80)->nullable()->after('full_name');
            $table->string('first_name', 80)->nullable()->after('last_name');
            $table->string('middle_initial', 10)->nullable()->after('first_name');
        });
    }

    public function down(): void
    {
        Schema::table('cns_requests', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'first_name', 'middle_initial']);
        });
    }
};
