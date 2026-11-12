<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'TenTaiKhoan' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('nguoi_dungs', 'TenTaiKhoan')],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'HoTen' => ['required', 'string', 'max:255'],
            'MSSV' => ['required', 'string', 'max:20', Rule::unique('nguoi_dungs', 'MSSV')],
            'Email' => ['required', 'email', 'max:255', Rule::unique('nguoi_dungs', 'Email')],
            'SDT' => ['nullable', 'string', 'max:30'],
            'NgaySinh' => ['required', 'date', 'before:today'],
            'Khoa' => ['nullable', 'string', 'max:255'],
            'Nganh' => ['nullable', 'string', 'max:255'],
            'KhoaHoc' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TenTaiKhoan.unique' => 'Tên tài khoản này đã có người sử dụng.',
            'TenTaiKhoan.alpha_dash' => 'Tên tài khoản chỉ được chứa chữ cái, chữ số, dấu gạch ngang và gạch dưới.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận chưa khớp.',
            'MSSV.unique' => 'Mã số sinh viên này đã tồn tại.',
            'Email.unique' => 'Email này đã được đăng ký.',
            'NgaySinh.before' => 'Ngày sinh phải trước ngày hôm nay.',
        ];
    }
}
