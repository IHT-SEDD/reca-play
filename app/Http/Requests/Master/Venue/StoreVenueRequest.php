<?php

namespace App\Http\Requests\Master\Venue;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreVenueRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if (!$this->has('code') || empty($this->code)) {
            $today = now();
            $year = $today->year;

            DB::transaction(function () use ($today, &$code) {
                $lastVenue = \App\Models\Master\Venue::whereYear('created_at', $today->year)
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                $lastNumber = 0;
                if ($lastVenue && preg_match('/VENUE(\d+)-\d{6}/', $lastVenue->code, $matches)) {
                    $lastNumber = (int) $matches[1];
                }

                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

                $this->merge([
                    'code' => 'VENUE' . $newNumber . '-' . $today->format('dmy')
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
            'code' => ['nullable', 'string', 'unique:venues,code', 'min:2'],
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string'],
            'venue_type_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.min' => 'Code minimum is 2 characters.',
            'code.unique' => 'Code already exists.',

            'name.required' => 'Name cannot be empty.',
            'name.max' => 'Name maximum is 255 characters.',
            'name.min' => 'Name minimum is 3 characters.',

            'address.required' => 'Address cannot be empty.',

            'venue_type_id.required' => 'Venue Type cannot be empty.',
        ];
    }
}
