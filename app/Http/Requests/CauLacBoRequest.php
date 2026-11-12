<?php

namespace App\Http\Requests;

use App\Enums\CauLacBoTrangThai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CauLacBoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $nameRules = $this->isMethod('POST')
            ? ['required', 'string', 'max:255']
            : ['sometimes', 'required', 'string', 'max:255'];
        $imageRule = function (string $attribute, mixed $value, \Closure $fail): void {
            $this->validateImageOrUrl($attribute, $value, $fail);
        };

        return [
            'TenCLB' => $nameRules,
            'MoTa' => ['sometimes', 'nullable', 'string'],
            'Email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'SDT' => ['sometimes', 'nullable', 'string', 'max:30'],
            'TrangThai' => ['sometimes', 'nullable', Rule::enum(CauLacBoTrangThai::class)],
            'URLLogo' => ['sometimes', 'nullable', $imageRule],
            'URLAnhNen' => ['sometimes', 'nullable', $imageRule],
            'MaDanhMucCLB' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    private function validateImageOrUrl(string $attribute, mixed $value, \Closure $fail): void
    {
        if ($value instanceof UploadedFile) {
            $validImage = Validator::make(
                [$attribute => $value],
                [$attribute => 'image|max:5120'],
            )->passes();

            if (! $validImage) {
                $fail('Ảnh câu lạc bộ phải là tệp hình ảnh hợp lệ và không vượt quá 5 MB.');
            }

            return;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false || strlen($value) > 2048) {
            $fail('Ảnh câu lạc bộ phải là tệp hình ảnh hoặc một URL hợp lệ.');
        }
    }
}
