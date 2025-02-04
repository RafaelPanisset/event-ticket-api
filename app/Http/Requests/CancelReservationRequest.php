<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_email' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_email.required' => 'Please provide your email address'
        ];
    }
}