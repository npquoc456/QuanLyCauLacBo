<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()->getAuthIdentifier();

        return [
            'TenTaiKhoan' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('nguoi_dungs', 'TenTaiKhoan')->ignore($userId, 'MaNguoiDung')],
            'MSSV' => ['required', 'string', 'max:20', Rule::unique('nguoi_dungs', 'MSSV')->ignore($userId, 'MaNguoiDung')],
            'HoTen' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'email', 'max:255', Rule::unique('nguoi_dungs', 'Email')->ignore($userId, 'MaNguoiDung')],
            'SDT' => ['nullable', 'string', 'max:30'],
            'NgaySinh' => ['required', 'date', 'before:today'],
            'Khoa' => ['nullable', 'string', 'max:255'],
            'Nganh' => ['nullable', 'string', 'max:255'],
            'KhoaHoc' => ['nullable', 'string', 'max:50'],
            'URLAnhDaiDien' => ['nullable', 'image', 'max:5120'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password:web'],
            'new_password' => ['nullable', 'required_with:current_password', 'string', 'min:8', 'max:72', 'confirmed', 'different:current_password'],
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
            'MSSV.unique' => 'Mã số sinh viên này đã tồn tại.',
            'Email.unique' => 'Email này đã được đăng ký.',
            'NgaySinh.before' => 'Ngày sinh phải trước ngày hôm nay.',
            'URLAnhDaiDien.image' => 'Ảnh đại diện phải là một tệp hình ảnh hợp lệ.',
            'URLAnhDaiDien.max' => 'Ảnh đại diện không được vượt quá 5 MB.',
            'current_password.required_with' => 'Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'new_password.required_with' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'new_password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ];
    }
}
