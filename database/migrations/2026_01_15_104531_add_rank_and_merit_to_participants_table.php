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
        Schema::table('participants', function (Blueprint $table) {
            $table->unsignedInteger('rank')->nullable()->after('score');
            $table->unsignedInteger('merit_position')->nullable()->after('rank');
            $table->index(['exam_id', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['exam_id', 'rank']);
            $table->dropColumn(['rank', 'merit_position']);
        });
    }
};
