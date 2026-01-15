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
        Schema::table('questions', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['exam_id']);
            
            // Drop the exam_id column
            $table->dropColumn('exam_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Re-add exam_id column
            $table->foreignId('exam_id')->nullable()->after('id');
            
            // Re-add the foreign key constraint
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        });
    }
};
