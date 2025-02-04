<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'date' => 'sometimes|required|date|after:today',
            'availability' => 'sometimes|required|integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'date.after' => 'Event date must be in the future',
            'availability.min' => 'Total tickets cannot be negative'
        ];
    }
}