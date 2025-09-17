<?php

namespace App\Http\Requests\Creator;

use App\Models\Master\Camera;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRecordRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $userId = Auth::id();
        $scannedQrData = session('scanned_qr');
        $fieldId = $scannedQrData['field_id'] ?? null;
        $cameraId = null;
        if ($fieldId) {
            $cameraId = Camera::where('field_id', $fieldId)->value('id');
        }

        $this->merge([
            'user_id' => $userId,
            'field_id' => $fieldId,
            'camera_id' => $cameraId,
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
            'video_name.required' => 'Video name cannot be empty.',
            'video_name.min' => 'Video name minimum is 5 characters',

            'duration.required' => 'Duration cannot be empty',
            'duration.integer' => 'Duration must be a number',

            'user_id.exist' => 'User ID does not exist',
            'field_id.exist' => 'Field ID does not exist',
            'camera_id.exist' => 'Camera ID does not exist',
        ];
    }
}
