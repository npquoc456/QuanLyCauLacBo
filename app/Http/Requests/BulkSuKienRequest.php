<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkSuKienRequest extends FormRequest
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
        return [
            'event_ids' => ['required', 'array', 'min:1'],
            'event_ids.*' => ['required', 'integer', 'distinct', 'exists:su_kiens,MaSuKien'],
            'action' => ['required', 'in:mark,unmark,delete'],
        ];
    }
}
