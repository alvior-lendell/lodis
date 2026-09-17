<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Non-Binary'],
            'birthdate' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }
}