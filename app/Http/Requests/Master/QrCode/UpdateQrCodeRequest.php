<?php

namespace App\Http\Requests\Master\QrCode;

use App\Models\Master\Field;
use App\Models\Master\Venue;
use App\Services\QrCode\QrCodeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateQrCodeRequest extends StoreQrCodeRequest
{
        protected function prepareForValidation()
    {
        // Empty: no need to generate a new QR Code when updating
    }


    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

        public function messages(): array
    {
        return [
            'name.required' => 'Name cannot be empty.',
            'name.max' => 'Name maximum is 255 characters.',
            'name.min' => 'Name minimum is 3 characters.',

            'is_active.required' => 'Is Active cannot be empty.',
        ];
    }
}
