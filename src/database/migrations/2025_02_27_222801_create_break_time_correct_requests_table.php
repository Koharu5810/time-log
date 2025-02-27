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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('break_time_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreign('attendance_correct_request_id')
                ->references('id')->on('attendance_correct_requests')
                ->cascadeOnDelete()
                ->name('btcr_attcr_fk'); // BreakTimeCorrectRequestとAttendanceCorrectRequestの短縮名
            $table->time('previous_break_time_start')->nullable();  // 変更前の開始時刻
            $table->time('previous_break_time_end')->nullable();    // 変更前の終了時刻
            $table->time('requested_break_time_start')->nullable(); // 申請された開始時刻
            $table->time('requested_break_time_end')->nullable();   // 申請された終了時刻
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
