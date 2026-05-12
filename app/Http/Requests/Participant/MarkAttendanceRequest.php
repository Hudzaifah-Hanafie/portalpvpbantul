<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CourseAttendance;

class MarkAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization logic handled in controller
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|in:' . implode(',', array_keys(CourseAttendance::statuses())),
            'reason' => 'nullable|string',
            'proof_url' => 'nullable|string|max:255',
            'signature_data' => 'nullable|string',
            'attendance_code' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_code.required' => 'Kode presensi wajib diisi.',
            'status.in' => 'Status presensi tidak valid.',
        ];
    }
}
