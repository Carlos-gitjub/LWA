<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'region' => 'required|string|size:2'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
