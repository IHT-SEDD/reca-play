<?php

namespace App\Http\Requests\Master\NVR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreNvrRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if (!$this->has('code') || empty($this->code)) {
            $today = now();
            $year = $today->year;

            DB::transaction(function () use ($today, &$code) {
                $lastNvr = \App\Models\Master\Camera::whereYear('created_at', $today->year)
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                $lastNumber = 0;
                if ($lastNvr && preg_match('/NVR(\d+)-\d{6}/', $lastNvr->code, $matches)) {
                    $lastNumber = (int) $matches[1];
                }

                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

                $this->merge([
                    'code' => 'NVR' . $newNumber . '-' . $today->format('dmy')
                ]);
            });
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
            'venue_id' => ['required', 'string', 'exists:venues,id'],
            'port_id' => ['nullable', 'integer', 'exists:ports,id'],

            'code' => ['nullable', 'string', 'unique:nvrs,code', 'min:2'],
            'brand' => ['nullable', 'string', 'min:5'],
            'type' => ['nullable', 'string', 'min:5'],
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'initial' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'ip_address' => ['required', 'string', 'min:4'],
            'auth_type' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'venue_id.required' => 'Venue cannot be empty.',
            'venue_id.exists' => 'Venue not found.',

            'code.min' => 'Code minimum is 2 characters.',
            'code.unique' => 'Code already exists.',

            'brand.min' => 'Brand minimum is 5 characters.',

            'type.min' => 'Type minimum is 5 characters.',

            'name.required' => 'Name cannot be empty.',
            'name.max' => 'Name maximum is 255 characters.',
            'name.min' => 'Name minimum is 3 characters.',

            'ip_address.required' => 'IP Address cannot be empty.',
            'ip_address.min' => 'IP Address minimum is 4 characters.',

            'auth_type.required' => 'Auth Type cannot be empty.',

            'username.required' => 'Username cannot be empty.',

            'password.required' => 'Password cannot be empty.',

            'port_id.integer' => 'Port must be a number',
            'port_id.exists' => 'Port not found.',

            'is_active.required' => 'Is Active cannot be empty.',
        ];
    }
}
