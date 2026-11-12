<?php

namespace App\Http\Requests;

use App\Enums\SuKienLoai;
use App\Enums\SuKienPhanLoaiNoiBo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

class SuKienRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $dateFields = [
            'ThoiGianMoDangKy',
            'ThoiGianDongDangKy',
            'ThoiGianBatDau',
            'ThoiGianKetThuc',
        ];
        $normalized = [];

        foreach ($dateFields as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $time = $this->input($field.'_time');

                if (! is_string($time) || ! preg_match('/^\d{2}:\d{2}$/', $time)) {
                    continue;
                }

                $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', "{$value} {$time}");
            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{2} \d{2}:\d{2}$/', $value)) {
                $date = \DateTimeImmutable::createFromFormat('!d/m/y H:i', $value);
            } else {
                continue;
            }

            $errors = \DateTimeImmutable::getLastErrors();

            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                $normalized[$field] = $date->format('Y-m-d H:i:s');
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }

        if ($this->input('LoaiSuKien') === SuKienLoai::CONG_KHAI->value) {
            $this->merge(['PhanLoaiNoiBo' => null]);
        }
    }

    public function withValidator(ValidationValidator $validator): void
    {
        $validator->after(function (ValidationValidator $validator): void {
            foreach (['ThoiGianMoDangKy', 'ThoiGianDongDangKy', 'ThoiGianBatDau', 'ThoiGianKetThuc'] as $field) {
                $date = $this->input($field);
                $time = $this->input($field.'_time');

                if (! is_string($date) || $date === '') {
                    if (is_string($time) && $time !== '') {
                        $validator->errors()->add($field, 'Vui lòng chọn ngày nếu đã nhập giờ.');
                    }

                    continue;
                }

                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    continue;
                }

                if (! is_string($time) || ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
                    $validator->errors()->add($field.'_time', 'Vui lòng nhập giờ hợp lệ theo định dạng 24 tiếng.');
                }
            }
        });
    }

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
            'TenSuKien' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'URLAnhNen' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value instanceof UploadedFile) {
                        $validImage = Validator::make(
                            [$attribute => $value],
                            [$attribute => 'image|max:5120'],
                        )->passes();

                        if (! $validImage) {
                            $fail('Ảnh nền phải là tệp hình ảnh hợp lệ và không vượt quá 5 MB.');
                        }

                        return;
                    }

                    if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false || strlen($value) > 2048) {
                        $fail('Ảnh nền phải là tệp hình ảnh hoặc một URL hợp lệ.');
                    }
                },
            ],
            'DiaDiem' => 'nullable|string|max:255',
            'SoLuongToiDa' => 'required|integer|min:1',
            'ThoiGianMoDangKy' => 'required|date',
            'ThoiGianDongDangKy' => 'required|date|after:ThoiGianMoDangKy',
            'ThoiGianBatDau' => 'required|date|after_or_equal:ThoiGianDongDangKy',
            'ThoiGianKetThuc' => 'required|date|after_or_equal:ThoiGianBatDau',
            'LoaiSuKien' => ['sometimes', 'required', Rule::enum(SuKienLoai::class)],
            'PhanLoaiNoiBo' => ['sometimes', 'nullable', Rule::enum(SuKienPhanLoaiNoiBo::class)],
            'MaCLB' => 'nullable|integer|min:1',
            'MaDanhMucSuKien' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'TenSuKien.required' => 'Tên sự kiện không được để trống.',
            'ThoiGianDongDangKy.after' => 'Thời gian đóng đăng ký phải sau thời gian mở đăng ký.',
            'ThoiGianBatDau.after_or_equal' => 'Sự kiện phải bắt đầu sau hoặc bằng thời gian đóng đăng ký.',
            'ThoiGianKetThuc.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'SoLuongToiDa.min' => 'Số lượng tối đa phải ít nhất là 1.',
        ];
    }
}
