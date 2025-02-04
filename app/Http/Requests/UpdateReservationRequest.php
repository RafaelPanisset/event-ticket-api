<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_email' => 'required|email',
            'tickets_count' => 'required|integer|min:1',
            'tickets_count' => 'required|integer|max:15'

        ];
    }

    public function messages(): array
    {
        return [
            'tickets_count.min' => 'You must reserve at least one ticket',
            'customer_email.required' => 'Please provide your email address'

        ];
    }
}