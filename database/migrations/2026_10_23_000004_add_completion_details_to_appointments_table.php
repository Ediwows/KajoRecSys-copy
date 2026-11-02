<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->text('action_taken')->nullable()->after('guidance_message');
            $table->timestamp('completed_at')->nullable()->after('action_taken');
            $table->json('attachments')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropColumn(['action_taken', 'completed_at', 'attachments']);
        });
    }
};
