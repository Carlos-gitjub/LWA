<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

final class SearchBooksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['q' => 'nullable|string', 'authors' => 'sometimes|array', 'authors.*' => 'string',
            'books' => 'sometimes|array', 'books.*' => 'integer|min:1'];
    }
}
