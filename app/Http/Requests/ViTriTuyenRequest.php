<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ViTriTuyenRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'TenViTri' => 'required|string|max:255',
            'Mota' => 'required|string|max:500',
            'SoLuong' => 'required|integer|min:0',
            'MaDotTuyen' => 'nullable|integer|min:1',
            'MaBan' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TenViTri.required' => 'Tên vị trí không được để trống.',
            'TenViTri.string' => 'Tên vị trí phải là một chuỗi ký tự.',
            'TenViTri.max' => 'Tên vị trí không được vượt quá 255 ký tự.',

            'Mota.required' => 'Mô tả không được để trống.',
            'Mota.string' => 'Mô tả phải là một chuỗi ký tự.',
            'Mota.max' => 'Mô tả không được vượt quá 500 ký tự.',

            'SoLuong.required' => 'Số lượng không được để trống.',
            'SoLuong.integer' => 'Số lượng phải là một số nguyên.',
            'SoLuong.min' => 'Số lượng không được nhỏ hơn 0.',

            'MaDotTuyen.integer' => 'Mã đợt tuyển phải là một số nguyên.',
            'MaDotTuyen.min' => 'Mã đợt tuyển không hợp lệ.',

            'MaBan.integer' => 'Mã ban phải là một số nguyên.',
            'MaBan.min' => 'Mã ban không hợp lệ.',
        ];
    }
}
