<?php

namespace App\Http\Requests;

use App\Enums\DonUngTrangThai;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewDonUngTuyenRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'TrangThai' => ['required', Rule::in([
                DonUngTrangThai::DANG_XET_DUYET->value,
                DonUngTrangThai::DA_DUYET->value,
                DonUngTrangThai::TU_CHOI->value,
            ])],
            'GhiChu' => 'nullable|string|max:1024',
        ];
    }
}
