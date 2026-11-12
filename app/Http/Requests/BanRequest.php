<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BanRequest extends FormRequest
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
     * @return array <string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'TenBan' => 'required|string|max:100',
            'MoTa' => 'nullable|string|max:500',
            'MaCLB' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array <string, string>
     */
    public function messages(): array
    {
        return [
            'TenBan.required' => 'Tên ban không được để trống.',
            'TenBan.string' => 'Tên ban phải là một chuỗi ký tự.',
            'TenBan.max' => 'Tên ban không được vượt quá 100 ký tự.',
            'MoTa.string' => 'Mô tả phải là một chuỗi ký tự.',
            'MoTa.max' => 'Mô tả không được vượt quá 500 ký tự.',
            'MaCLB.integer' => 'Mã câu lạc bộ (CLB) phải là một số nguyên.',
            'MaCLB.min' => 'Mã câu lạc bộ (CLB) không hợp lệ.',
        ];
    }
}
