<?php

namespace App\Http\Requests\Creator;

use App\Models\Master\Camera;
use App\Models\Session\QrSession;
use App\Models\Session\SessionCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreRecordRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');

        $scannedQr = QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->latest()
            ->first();
        $fieldId = $scannedQr?->qrCode?->field_id ?? null;

        $cameraId = null;
        if ($fieldId) {
            $cameraId = Camera::where('field_id', $fieldId)->value('id');
        }

        $sessionCodeId = SessionCode::where('generated_code', $this->session_code)
            ->first();

        Log::info('StoreRecordRequest prepareForValidation', [
            'session_code' => $this->session_code,
            'user_id' => $userId,
            'field_id' => $fieldId,
            'session_code_id' => $sessionCodeId,
        ]);

        $this->merge([
            'user_id' => $userId,
            'field_id' => $fieldId,
            'camera_id' => $cameraId,
            'session_code_id' => $sessionCodeId,
            'session_token' => $sessionToken,
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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'field_id' => ['nullable', 'integer', 'exists:fields,id'],
            'camera_id' => ['nullable', 'integer', 'exists:cameras,id'],
            'session_code_id' => ['nullable', 'integer', 'exists:session_codes,id'],

            'session_token' => ['nullable', 'string'],
            'session_code' => ['required', 'string'],
            'video_name' => ['required', 'string', 'max:255', 'min:5'],
            'duration' => ['required', 'integer'],

            'start_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'end_time' => ['nullable', 'date_format:Y-m-d H:i:s'],

            'video_path' => ['nullable', 'string'],
            'video_filename' => ['nullable', 'string'],
            'video_size' => ['nullable', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'session_code.required' => 'Session code cannot be empty.',

            'video_name.required' => 'Video name cannot be empty.',
            'video_name.min' => 'Video name minimum is 5 characters',

            'session_code.required' => 'Session code cannot be empty',

            'duration.required' => 'Duration cannot be empty',
            'duration.integer' => 'Duration must be a number',

            'user_id.exist' => 'User ID does not exist',
            'field_id.exist' => 'Field ID does not exist',
            'camera_id.exist' => 'Camera ID does not exist',
            'session_code_id.exist' => 'Session Code not valid',
        ];
    }
}
