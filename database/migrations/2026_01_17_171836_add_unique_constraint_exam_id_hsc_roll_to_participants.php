<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, remove duplicate HSC rolls per exam (keep the first one, delete the rest)
        // This handles existing duplicates before adding the constraint
        $duplicates = \DB::table('participants')
            ->select('exam_id', 'hsc_roll', \DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('hsc_roll')
            ->groupBy('exam_id', 'hsc_roll')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Delete all participants with this exam_id and hsc_roll except the first one
            \DB::table('participants')
                ->where('exam_id', $duplicate->exam_id)
                ->where('hsc_roll', $duplicate->hsc_roll)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('participants', function (Blueprint $table) {
            // Add unique constraint on exam_id and hsc_roll
            // This prevents the same HSC roll from taking the same exam twice
            // NULL values are allowed (multiple participants can have NULL hsc_roll)
            $table->unique(['exam_id', 'hsc_roll'], 'participants_exam_id_hsc_roll_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique('participants_exam_id_hsc_roll_unique');
        });
    }
};
