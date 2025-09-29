<?php

namespace App\Http\Requests\Master\QrCode;

use App\Models\Master\Field;
use App\Models\Master\Venue;
use App\Services\QrCode\QrCodeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreQrCodeRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $today = now();
        $prefix = 'QR';

        // Generate unique code for this QR
        DB::transaction(function () use ($today, &$prefix) {
            if ($this->filled('field_id') && !$this->filled('venue_id')) {
                $prefix = 'QRF';
            } elseif ($this->filled('venue_id') && !$this->filled('field_id')) {
                $prefix = 'QRV';
            }

            $lastQrCode = \App\Models\Master\QrCode::whereYear('created_at', $today->year)
                ->where('code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $lastNumber = 0;
            if ($lastQrCode && preg_match('/' . $prefix . '(\d+)-\d{6}/', $lastQrCode->code, $matches)) {
                $lastNumber = (int) $matches[1];
            }

            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            $this->merge([
                'code' => $prefix . $newNumber . '-' . $today->format('dmy'),
            ]);
        });

        // Generate unique token for QR Code (keeps data safe)
        $token = \Illuminate\Support\Str::uuid()->toString();
        $this->merge(['qr_token' => $token]);

        // Only encode token in the QR (safe for users)
        $qrData = [
            'token' => $token,
        ];

        // Generate QR Code image
        $service = app(\App\Services\QrCode\QrCodeService::class);
        $path = $service->generate($qrData, $this->code);
        $filename = basename($path);

        // Merge generated QR info to request for saving to DB
        $this->merge([
            'qr_path' => $path,
            'qr_file' => $filename,
            'qr_token' => $token,
        ]);
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
            'code' => ['nullable', 'string', 'unique:qr_codes,code', 'min:2'],

            'name' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string'],

            'field_id' => ['nullable', 'string', 'exists:fields,id'],
            'venue_id' => ['nullable', 'string', 'exists:venues,id'],

            'type' => ['nullable', 'string', 'min:3'],
            'is_active' => ['required', 'boolean'],

            'qr_path' => ['nullable', 'string', 'min:5'],
            'qr_file' => ['nullable', 'string', 'min:5'],
            'qr_token' => ['nullable', 'string', 'min:5'],
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

            'field_id.exists' => 'Field not found.',
            'venue_id.exists' => 'Venue not found.',

            'type.min' => 'Type minimum is 3 characters.',

            'qr_path.min' => 'QR Code Path minimum is 5 characters.',
            'qr_file.min' => 'QR Code File minimum is 5 characters.',
            'qr_token.min' => 'QR Code Token minimum is 5 characters.',

            'is_active.required' => 'Is Active cannot be empty.',
        ];
    }
}
