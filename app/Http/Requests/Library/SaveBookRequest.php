<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

final class SaveBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255', 'author' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf|max:30000', 'cover_base64' => 'nullable|string',
        ];
    }
}
