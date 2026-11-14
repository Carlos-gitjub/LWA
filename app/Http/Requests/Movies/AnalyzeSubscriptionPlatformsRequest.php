<?php

namespace App\Http\Requests\Movies;

use Illuminate\Foundation\Http\FormRequest;

final class AnalyzeSubscriptionPlatformsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'movies' => ['required', 'array', 'min:1', 'max:30'],
            'movies.*.id' => ['required', 'integer', 'min:1', 'distinct'],
            'movies.*.title' => ['required', 'string', 'max:255'],
            'movies.*.year' => ['present', 'nullable', 'string', 'regex:/^(?:[0-9]{4})?$/'],
            'region' => ['sometimes', 'required', 'string', 'in:ES,US,GB'],
        ];
    }
}
