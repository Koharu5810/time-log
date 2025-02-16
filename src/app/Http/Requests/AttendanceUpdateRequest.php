<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'requested_clock_in' => 'required|date_format:H:i',
            'requested_clock_end' => 'required|date_format:H:i|after:requested_clock_in',

            'requested_break_times' => 'required|array', // 休憩時間の配列
            'requested_break_times.*.start' => 'required|date_format:H:i',
            'requested_break_times.*.end' => 'required|date_format:H:i|after:requested_break_times.*.start',

            'requested_remarks' => 'required|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'requested_clock_end.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_break_times.*.end.after' => '休憩入時間もしくは休憩戻時間が不適切な値です',
            'requested_remarks.required' => '備考を記入してください',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = \Carbon\Carbon::createFromFormat('H:i', $this->requested_clock_in);
            $clockEnd = \Carbon\Carbon::createFromFormat('H:i', $this->requester_clock_end);

            // 勤務時間（分単位）
            $workMinutes = $clockIn->diffInMinutes($clockEnd);

            // 休憩時間の合計（分単位）
            $totalBreakMinutes = 0;
            if (!empty($this->requested_break_times)) {
                foreach ($this->requested_break_times as $break) {
                    $breakStart = \Carbon\Carbon::createFromFormat('H:i', $break['start']);
                    $breakEnd = \Carbon\Carbon::createFromFormat('H:i', $break['end']);
                    $totalBreakMinutes += $breakStart->diffInMinutes($breakEnd);
                }
            }

            // 総勤務時間より休憩時間が長い場合にエラー
            if ($totalBreakMinutes > $workMinutes) {
                $validator->errors()->add('requested_break_times', '休憩時間が勤務時間外です');
            }
        });
    }
}
