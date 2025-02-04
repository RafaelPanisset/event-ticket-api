<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Since we don't have authentication
    }

    public function rules(): array
    {
        return [
            'customer_email' => 'required|email',
            'customer_name' => 'required|string|max:255',
            'tickets_count' => 'required|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'tickets_count.min' => 'You must reserve at least one ticket',
            'customer_email.required' => 'Please provide your email address',
            'customer_name.required' => 'Please provide your name'
        ];
    }
}