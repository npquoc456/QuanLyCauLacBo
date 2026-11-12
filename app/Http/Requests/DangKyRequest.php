<?php

namespace App\Http\Requests;

use App\Enums\TrangThaiDangKy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DangKyRequest extends FormRequest
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
            'MaNguoiDung' => 'required|integer|min:1',
            'MaSuKien' => 'required|integer|min:1',
            'TrangThai' => ['nullable', Rule::enum(TrangThaiDangKy::class)],
            'ThoiGianCheckIn' => 'required|date',
            'NgayDangKy' => 'required|date',
            'MaQRToken' => 'nullable|string|max:255',
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
            'MaNguoiDung.required' => 'Mã người dùng không được để trống.',
            'MaNguoiDung.integer' => 'Mã người dùng phải là một số nguyên.',
            'MaNguoiDung.min' => 'Mã người dùng không hợp lệ.',

            'MaSuKien.required' => 'Mã sự kiện không được để trống.',
            'MaSuKien.integer' => 'Mã sự kiện phải là một số nguyên.',
            'MaSuKien.min' => 'Mã sự kiện không hợp lệ.',

            'TrangThai.Illuminate\Validation\Rules\Enum' => 'Trạng thái đăng ký không hợp lệ.',

            'ThoiGianCheckIn.required' => 'Thời gian check-in không được để trống.',
            'ThoiGianCheckIn.date' => 'Thời gian check-in không đúng định dạng ngày giờ.',

            'NgayDangKy.required' => 'Ngày đăng ký không được để trống.',
            'NgayDangKy.date' => 'Ngày đăng ký không đúng định dạng ngày giờ.',

            'MaQRToken.string' => 'Mã QR Token phải là một chuỗi ký tự.',
            'MaQRToken.max' => 'Mã QR Token không được vượt quá 255 ký tự.',
        ];
    }
}
