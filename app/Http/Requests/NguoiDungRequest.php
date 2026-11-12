<?php

namespace App\Http\Requests;

use App\Enums\NguoiDungTrangThai;
use App\Enums\NguoiDungVaiTro;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NguoiDungRequest extends FormRequest
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

        $id = $this->route('nguoi_dung');

        return [
            'TenTaiKhoan' => [
                'required', 'string', 'max:255',
                Rule::unique('nguoi_dungs', 'TenTaiKhoan')->ignore($id, 'MaNguoiDung'),
            ],

            'MatKhauHash' => 'required|string|max:255',

            'MSSV' => [
                'required', 'string', 'max:20',
                Rule::unique('nguoi_dungs', 'MSSV')->ignore($id, 'MaNguoiDung'),
            ],

            'HoTen' => 'required|string|max:255',

            'SDT' => 'nullable|string|max:30',

            'Email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('nguoi_dungs', 'Email')->ignore($id, 'MaNguoiDung'),
            ],

            'NgaySinh' => 'required|date',

            'URLAnhDaiDien' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    // Nếu người dùng upload file trực tiếp
                    if ($value instanceof UploadedFile) {
                        $validImage = Validator::make(
                            [$attribute => $value],
                            [$attribute => 'image|max:5120'] // max 5MB (5120 KB)
                        )->passes();

                        if (! $validImage) {
                            $fail('Ảnh đại diện phải là định dạng hình ảnh và không được vượt quá 5MB!');
                        }

                        return;
                    }

                    // Nếu giá trị là một chuỗi (URL hoặc đường dẫn file đã lưu)
                    if (! is_string($value) || strlen($value) > 2048) {
                        $fail('Đường dẫn ảnh đại diện không hợp lệ hoặc vượt quá 2048 ký tự!');
                    }
                },
            ],

            'Khoa' => 'nullable|string|max:255',
            'Nganh' => 'nullable|string|max:255',
            'KhoaHoc' => 'nullable|string|max:50',

            'VaiTro' => ['required', Rule::enum(NguoiDungVaiTro::class)],

            'TrangThai' => ['required', Rule::enum(NguoiDungTrangThai::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Trường :attribute không được bỏ trống.',
            'string' => 'Trường :attribute phải là chuỗi ký tự.',
            'max' => 'Trường :attribute không được vượt quá :max ký tự.',
            'unique' => ':attribute đã tồn tại trong hệ thống.',
            'date' => 'Trường :attribute không đúng định dạng ngày tháng.',
            'email' => 'Trường :attribute phải là địa chỉ email hợp lệ.',

            // Custom tên hiển thị cho các attribute để câu báo lỗi tự nhiên hơn
            'TenTaiKhoan.required' => 'Tên tài khoản không được để trống.',
            'TenTaiKhoan.unique' => 'Tên tài khoản này đã có người sử dụng.',
            'MSSV.required' => 'Mã số sinh viên không được để trống.',
            'MSSV.unique' => 'Mã số sinh viên này đã tồn tại.',
            'HoTen.required' => 'Họ tên không được để trống.',
            'Email.required' => 'Email không được để trống.',
            'Email.unique' => 'Email này đã được đăng ký.',
            'NgaySinh.required' => 'Ngày sinh không được để trống.',

            'VaiTro.required' => 'Vai trò không được để trống.',
            'VaiTro.Illuminate\Validation\Rules\Enum' => 'Vai trò được chọn không hợp lệ.',
            'TrangThai.required' => 'Trạng thái không được để trống.',
            'TrangThai.Illuminate\Validation\Rules\Enum' => 'Trạng thái được chọn không hợp lệ.',
        ];
    }
}
