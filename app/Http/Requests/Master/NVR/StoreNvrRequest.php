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
            'code' => ['nullable', 'string', 'unique:nvrs,code', 'min:2'],
            'brand' => ['nullable', 'string', 'min:5'],
            'type' => ['nullable', 'string', 'min:5'],
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'initial' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'ip_address' => ['nullable', 'string', 'min:4'],
            'camera_id' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.min' => 'Code minimum is 2 characters.',
            'code.unique' => 'Code already exists.',

            'brand.min' => 'Brand minimum is 5 characters.',

            'type.min' => 'Type minimum is 5 characters.',

            'name.required' => 'Name cannot be empty.',
            'name.max' => 'Name maximum is 255 characters.',
            'name.min' => 'Name minimum is 3 characters.',

            'ip_address.min' => 'IP Address minimum is 4 characters.',

            'camera_id.required' => 'Camera cannot be empty.',
            'is_active.required' => 'Is Active cannot be empty.',
        ];
    }
}
