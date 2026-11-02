<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->uuid('bundle_id')->nullable()->after('related_assignment_id')->index();
            $table->text('notes')->nullable()->after('slip');
        });

        DB::table('slip_assignments')
            ->where('slip', 'student-referral-slip')
            ->orderBy('id')
            ->eachById(function (object $referral): void {
                $bundleId = (string) Str::uuid();

                DB::table('slip_assignments')
                    ->where('id', $referral->id)
                    ->update(['bundle_id' => $bundleId]);

                DB::table('slip_assignments')
                    ->where('related_assignment_id', $referral->id)
                    ->update(['bundle_id' => $bundleId]);
            });
    }

    public function down(): void
    {
        Schema::table('slip_assignments', function (Blueprint $table) {
            $table->dropIndex(['bundle_id']);
            $table->dropColumn(['bundle_id', 'notes']);
        });
    }
};
