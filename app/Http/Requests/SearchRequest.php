<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
