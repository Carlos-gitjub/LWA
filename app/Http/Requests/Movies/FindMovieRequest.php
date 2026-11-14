<?php

namespace App\Http\Requests\Movies;

use Illuminate\Foundation\Http\FormRequest;

final class FindMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255']];
    }
}
