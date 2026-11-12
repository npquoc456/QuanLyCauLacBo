<?php

namespace App\Http\Requests;

use App\Enums\DonUngTrangThai;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonUngTuyenRequest extends FormRequest
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
            'MaViTri' => 'required|integer|min:1',
            'MaNguoiUngTuyen' => 'required|integer|min:1',
            'NoiDung' => 'nullable|string|max:500',
            'URLHoSo' => 'nullable|string|max:2048',
            'TrangThai' => ['nullable', Rule::enum(DonUngTrangThai::class)],
            'MaNguoiXetDuyet' => 'nullable|integer|min:1',
            'GhiChu' => 'nullable|string|max:1024',
            'NgayNopDon' => 'required|date',
            'NgayCapNhat' => 'required|date',
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
            'MaViTri.required' => 'Mã vị trí không được để trống.',
            'MaViTri.integer' => 'Mã vị trí phải là một số nguyên.',
            'MaViTri.min' => 'Mã vị trí không hợp lệ.',

            'MaNguoiUngTuyen.required' => 'Mã người ứng tuyển không được để trống.',
            'MaNguoiUngTuyen.integer' => 'Mã người ứng tuyển phải là một số nguyên.',
            'MaNguoiUngTuyen.min' => 'Mã người ứng tuyển không hợp lệ.',

            'NoiDung.string' => 'Nội dung phải là một chuỗi ký tự.',
            'NoiDung.max' => 'Nội dung không được vượt quá 500 ký tự.',

            'URLHoSo.string' => 'Đường dẫn hồ sơ phải là một chuỗi ký tự.',
            'URLHoSo.max' => 'Đường dẫn hồ sơ không được vượt quá 2048 ký tự.',

            'TrangThai.Illuminate\Validation\Rules\Enum' => 'Trạng thái đơn ứng tuyển không hợp lệ.',

            'MaNguoiXetDuyet.integer' => 'Mã người xét duyệt phải là một số nguyên.',
            'MaNguoiXetDuyet.min' => 'Mã người xét duyệt không hợp lệ.',

            'GhiChu.string' => 'Ghi chú phải là một chuỗi ký tự.',
            'GhiChu.max' => 'Ghi chú không được vượt quá 1024 ký tự.',

            'NgayNopDon.required' => 'Ngày nộp đơn không được để trống.',
            'NgayNopDon.date' => 'Ngày nộp đơn không đúng định dạng ngày giờ.',

            'NgayCapNhat.required' => 'Ngày cập nhật không được để trống.',
            'NgayCapNhat.date' => 'Ngày cập nhật không đúng định dạng ngày giờ.',
        ];
    }
}
