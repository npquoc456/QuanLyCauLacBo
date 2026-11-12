<?php

namespace App\Http\Requests;

use App\Enums\DotTuyenTrangThai;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DotTuyenThanhVienRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $fields = ['NgayBatDau', 'NgayKetThuc', 'NgayPhongVan', 'NgayCongBoKetQua'];
        $merge = [];

        foreach ($fields as $field) {
            $date = $this->input("{$field}_date");
            $time = $this->input("{$field}_time");

            if ($date && ! $this->filled($field)) {
                $timeValue = $time ? trim($time) : '00:00';
                $merge[$field] = trim("{$date} {$timeValue}");
            }
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'TenDotTuyen' => 'required|string|max:255',
            'MoTa' => 'nullable|string|max:500',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'TrangThai' => ['nullable', Rule::enum(DotTuyenTrangThai::class)],
            'DoiTuong' => 'nullable|string|max:255',
            'DiaDiemPhongVan' => 'required|string|max:255',
            'NgayBatDau' => 'required|date',
            'NgayKetThuc' => 'required|date|after:NgayBatDau',
            'NgayPhongVan' => 'required|date|after_or_equal:NgayKetThuc',
            'NgayCongBoKetQua' => 'required|date|after_or_equal:NgayPhongVan',
            'action' => ['required', Rule::in(['publish', 'draft'])],
            'positions' => 'required|array|min:1',
            'positions.*.MaViTri' => 'nullable|integer|min:1',
            'positions.*.TenViTri' => 'required|string|max:255',
            'positions.*.Mota' => 'nullable|string|max:500',
            'positions.*.SoLuong' => 'required|integer|min:1|max:10000',
            'positions.*.MaBan' => 'nullable|integer|min:1',
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
            'TenDotTuyen.required' => 'Tên đợt tuyển không được để trống.',
            'TenDotTuyen.string' => 'Tên đợt tuyển phải là một chuỗi ký tự.',
            'TenDotTuyen.max' => 'Tên đợt tuyển không được vượt quá 255 ký tự.',

            'MoTa.string' => 'Mô tả phải là một chuỗi ký tự.',
            'MoTa.max' => 'Mô tả không được vượt quá 500 ký tự.',
            'poster.image' => 'Poster đợt tuyển phải là một hình ảnh.',
            'poster.mimes' => 'Poster chỉ được dùng định dạng JPG, PNG hoặc WEBP.',
            'poster.max' => 'Poster không được vượt quá 5 MB.',
            'DiaDiemPhongVan.required' => 'Vui lòng nhập địa điểm hoặc hình thức phỏng vấn.',
            'NgayBatDau.required' => 'Ngày bắt đầu không được để trống.',
            'NgayBatDau.date' => 'Ngày bắt đầu không đúng định dạng ngày giờ.',
            'NgayKetThuc.required' => 'Ngày kết thúc không được để trống.',
            'NgayKetThuc.date' => 'Ngày kết thúc không đúng định dạng ngày giờ.',
            'NgayKetThuc.after' => 'Hạn nhận hồ sơ phải sau thời điểm mở đơn.',
            'NgayPhongVan.required' => 'Vui lòng chọn thời gian phỏng vấn.',
            'NgayPhongVan.after_or_equal' => 'Thời gian phỏng vấn phải sau thời hạn nhận hồ sơ.',
            'NgayCongBoKetQua.required' => 'Vui lòng chọn thời gian công bố kết quả.',
            'NgayCongBoKetQua.after_or_equal' => 'Thời gian công bố kết quả phải sau thời gian phỏng vấn.',
            'positions.required' => 'Hãy thêm ít nhất một vị trí tuyển.',
            'positions.min' => 'Hãy thêm ít nhất một vị trí tuyển.',
            'positions.*.TenViTri.required' => 'Tên vị trí không được để trống.',
            'positions.*.SoLuong.required' => 'Vui lòng nhập chỉ tiêu cho từng vị trí.',
            'positions.*.SoLuong.min' => 'Chỉ tiêu mỗi vị trí phải ít nhất là 1.',
        ];
    }
}
