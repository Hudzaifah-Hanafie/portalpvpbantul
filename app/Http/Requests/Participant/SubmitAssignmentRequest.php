<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization logic handled in controller
    }

    public function rules(): array
    {
        return [
            'content_text' => 'nullable|string',
            'link_url' => 'nullable|url|max:255',
            'file_upload' => 'nullable|file|max:10240|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-rar-compressed,image/jpeg,image/png',
        ];
    }

    protected function prepareForValidation()
    {
        // Trim potential whitespace issues
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->content_text) && empty($this->link_url) && !$this->hasFile('file_upload')) {
                $validator->errors()->add('content_text', 'Isi teks, tautan, atau unggah file.');
            }
        });
    }
}
