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

            'requested_break_times' => 'nullable|array', // 休憩時間の配列
            'requested_break_times.*.start' => 'nullable|date_format:H:i|required_with:requested_break_times.*.end',
            'requested_break_times.*.end' => 'nullable|date_format:H:i|after:requested_break_times.*.start|required_with:requested_break_times.*.start',

            'requested_remarks' => 'required|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'requested_clock_in.required' => '出勤時間を入力してください',
            'requested_clock_end.required' => '退勤時間を入力してください',
            'requested_clock_end.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'requested_break_times.*.start.required_with' => '休憩開始時間と休憩終了時間の両方を入力してください',
            'requested_break_times.*.end.required_with' => '休憩開始時間と休憩終了時間の両方を入力してください',
            'requested_break_times.*.end.after' => '休憩開始時間もしくは休憩終了時間が不適切な値です',
            // 休憩開始時間及び休憩終了時間が，出勤時間及び退勤時間を超えている際に「休憩時間が勤務外です」のバリデーションを実装

            'requested_remarks.required' => '備考を記入してください',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 出勤時間・退勤時間のバリデーション（両方入力されていることを前提）
            if (!empty($this->requested_clock_in) && !empty($this->requested_clock_end)) {
                try {
                    $clockIn = \Carbon\Carbon::parse($this->requested_clock_in);
                    $clockEnd = \Carbon\Carbon::parse($this->requested_clock_end);

                    // 勤務時間（分単位）
                    $workMinutes = $clockIn->diffInMinutes($clockEnd);
                } catch (\Exception $e) {
                    return; // フォーマットエラーは rules() のバリデーションに任せる
                }

                // 休憩時間のチェック
                $totalBreakMinutes = 0;
                if (!empty($this->requested_break_times)) {
                    foreach ($this->requested_break_times as $index => $break) {
                        $breakStart = $break['start'] ?? null;
                        $breakEnd = $break['end'] ?? null;

                        // 両方の値が入力されている場合のみ処理
                        if (!empty($breakStart) && !empty($breakEnd)) {
                            try {
                                $breakStartTime = \Carbon\Carbon::parse($breakStart);
                                $breakEndTime = \Carbon\Carbon::parse($breakEnd);

                                // **休憩時間が出勤前 or 退勤後ならエラー**
                                if ($breakStartTime->lt($clockIn) || $breakEndTime->gt($clockEnd)) {
                                    $validator->errors()->add("requested_break_times.$index.start", '休憩時間が勤務時間外です');
                                }

                                // 休憩時間を加算
                                $totalBreakMinutes += $breakStartTime->diffInMinutes($breakEndTime);
                            } catch (\Exception $e) {
                                return; // フォーマットエラーは rules() のバリデーションに任せる
                            }
                        }
                    }
                }

                // **総勤務時間より休憩時間が長い場合にエラー**
                if ($totalBreakMinutes > $workMinutes) {
                    $validator->errors()->add('requested_break_times', '休憩時間が勤務時間外です');
                }
            }
        });
    }
}
