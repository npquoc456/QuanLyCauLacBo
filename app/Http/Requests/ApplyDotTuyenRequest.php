<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyDotTuyenRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'MaViTri' => 'required|integer|min:1',
            'NoiDung' => 'nullable|string|max:500',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'MaViTri.required' => 'Vui lòng chọn vị trí muốn ứng tuyển.',
            'NoiDung.max' => 'Lời giới thiệu không được vượt quá 500 ký tự.',
            'resume.mimes' => 'Hồ sơ chỉ nhận định dạng PDF, DOC hoặc DOCX.',
            'resume.max' => 'Hồ sơ không được vượt quá 10 MB.',
        ];
    }
}
