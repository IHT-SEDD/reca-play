<?php

namespace App\Http\Requests\Master\Camera;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateCameraRequest extends StoreCameraRequest
{
    protected function prepareForValidation()
    {
       // Empty: no need to generate a new Code when updating
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand' => ['nullable', 'string', 'min:5'],
            'type' => ['nullable', 'string', 'min:2'],
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'initial' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'ip_address' => ['nullable', 'string', 'min:4'],
            'channel' => ['nullable', 'integer'],
            'field_id' => ['required', 'string', 'exists:fields,id'],
            'nvr_id' => ['required', 'string', 'exists:nvrs,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [

            'brand.min' => 'Brand minimum is 5 characters.',

            'type.min' => 'Type minimum is 2 characters.',

            'name.required' => 'Name cannot be empty.',
            'name.max' => 'Name maximum is 255 characters.',
            'name.min' => 'Name minimum is 3 characters.',

            'ip_address.min' => 'IP Address minimum is 4 characters.',

            'channel.integer' => 'Channel must be a number',

            'field_id.required' => 'Field cannot be empty.',
            'field_id.exists' => 'Field not found.',

            'nvr_id.required' => 'NVR cannot be empty.',
            'nvr_id.exists' => 'NVR not found.',

            'is_active.required' => 'Is Active cannot be empty.',
        ];
    }
}
