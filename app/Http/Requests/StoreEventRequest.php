<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;  // because im not using authetication
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after:today',
            'availability' => 'required|integer|min:0'
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

