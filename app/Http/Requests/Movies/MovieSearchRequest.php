<?php

namespace App\Http\Requests\Movies;

use Illuminate\Foundation\Http\FormRequest;

class MovieSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'region' => 'required|string|in:ES,US,GB',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
