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
        Schema::create('break_time_correct_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('break_time_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('att_correct_id')->constrained('attendance_correct_requests')->cascadeOnDelete();  // 外部IDカラム名が長くてSQL制限に引っかかるため短縮形を採用
            $table->time('previous_break_time_start')->nullable();
            $table->time('previous_break_time_end')->nullable();
            $table->time('requested_break_time_start')->nullable();
            $table->time('requested_break_time_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_time_correct_requests');
    }
};
