<?php

namespace App\Http\Requests\UserManagement\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if (!$this->has('email_verified_at') || empty($this->email_verified_at)) {
            $this->merge([
                'email_verified_at' => now(),
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'username' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'email'],
            'email_verified_at' => ['required', 'date'],
            'password' => ['required', 'string', 'min:8'],
            'venue_id' => ['nullable', 'integer', 'exists:venues,id', 'required_if:role_id,10'],
        ];
    }

    public function messages(): array
    {
        return [
            // role_id
            'role_id.required' => 'Role is required.',
            'role_id.integer' => 'Role ID must be a valid integer.',
            'role_id.exists' => 'The selected role does not exist.',

            // name
            'name.required' => 'Name cannot be empty.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name maximum is 255 characters.',
            'name.min' => 'Name minimum is 3 characters.',

            // username
            'username.required' => 'Username cannot be empty.',
            'username.string' => 'Username must be a string.',
            'username.max' => 'Username maximum is 255 characters.',
            'username.min' => 'Username minimum is 3 characters.',

            // email
            'email.required' => 'Email cannot be empty.',
            'email.email' => 'Email format is not valid.',

            // email_verified_at
            'email_verified_at.required' => 'Email verification date is required.',
            'email_verified_at.date' => 'Email verification must be a valid date.',

            // password
            'password.required' => 'Password cannot be empty.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password minimum is 8 characters.',

            // venue_id
            'venue_id.required_if' => 'Venue is required if role is owner.',
            'venue_id.integer' => 'Venue must be a valid integer.',
            'venue_id.exists' => 'The selected venue does not exist.',
        ];
    }
}
