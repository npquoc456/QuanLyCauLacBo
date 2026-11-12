<?php

namespace App\Http\Requests;

use App\Enums\ThanhVienTrangThai;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThanhVienCLBRequest extends FormRequest
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
            'MaCLB' => 'nullable|integer|min:1',
            'MaNguoiDung' => 'nullable|integer|min:1',
            'ChucVu' => 'required|string|max:50',
            'NgayThamGia' => 'required|date',
            'TrangThai' => ['nullable', Rule::enum(ThanhVienTrangThai::class)],
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
            'MaCLB.integer' => 'Mã câu lạc bộ (CLB) phải là một số nguyên.',
            'MaCLB.min' => 'Mã câu lạc bộ (CLB) không hợp lệ.',

            'MaNguoiDung.integer' => 'Mã người dùng phải là một số nguyên.',
            'MaNguoiDung.min' => 'Mã người dùng không hợp lệ.',

            'ChucVu.required' => 'Chức vụ không được để trống.',
            'ChucVu.string' => 'Chức vụ phải là một chuỗi ký tự.',
            'ChucVu.max' => 'Chức vụ không được vượt quá 50 ký tự.',

            'NgayThamGia.required' => 'Ngày tham gia không được để trống.',
            'NgayThamGia.date' => 'Ngày tham gia không đúng định dạng ngày giờ.',

            'TrangThai.Illuminate\Validation\Rules\Enum' => 'Trạng thái thành viên không hợp lệ.',
        ];
    }
}
