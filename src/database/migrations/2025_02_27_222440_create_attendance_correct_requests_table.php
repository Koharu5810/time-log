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
        Schema::create('attendance_correct_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->time('previous_clock_in')->nullable();   // 変更前の出勤時刻
            $table->time('previous_clock_end')->nullable();  // 変更前の退勤時刻
            $table->time('requested_clock_in')->nullable();  // 申請された出勤時刻
            $table->time('requested_clock_end')->nullable(); // 申請された退勤時刻
            $table->enum('request_status', ['承認待ち', '承認済み'])->default('承認待ち');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->cascadeOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_correct_requests');
    }
};
