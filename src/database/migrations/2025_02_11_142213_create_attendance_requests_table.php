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
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->enum('request_type', ['修正', '申請'])->default('修正');
            $table->date('target_date');
            $table->time('requested_clock_in');
            $table->time('requested_clock_end');
            $table->string('requested_remarks', 255);
            $table->enum('status', ['承認待ち', '承認済み'])->default('承認待ち');
            $table->foreignId('admin_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_requests');
    }
};
