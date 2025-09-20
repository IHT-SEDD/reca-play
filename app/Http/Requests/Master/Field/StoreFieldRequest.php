<?php

namespace App\Http\Requests\Master\Field;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreFieldRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if (!$this->has('code') || empty($this->code)) {
            $today = now();
            $year = $today->year;

            DB::transaction(function () use ($today, &$code) {
                $lastField = \App\Models\Master\Field::whereYear('created_at', $today->year)
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                $lastNumber = 0;
                if ($lastField && preg_match('/FIELD(\d+)-\d{6}/', $lastField->code, $matches)) {
                    $lastNumber = (int) $matches[1];
                }

                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

                $this->merge([
                    'code' => 'FIELD' . $newNumber . '-' . $today->format('dmy')
                ]);
            });
        }
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if ($this->hasFile('pict')) {
            $file = $this->file('pict');
            $path = $file->store('field_pictures', 'public');

            $data['pict_path'] = 'storage/' . $path;
            $data['pict_filename'] = $file->getClientOriginalName();
        }

        return $data;
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
            'code' => ['nullable', 'string', 'unique:fields,code', 'min:2'],
            'name' => ['required', 'string', 'max:255'],
            'initial' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pict' => ['nullable', 'image', 'mimes:jpg,jpeg,png'],
            'pict_path' => ['nullable', 'string'],
            'pict_filename' => ['nullable', 'string'],
            'category_id' => ['required', 'string'],
            'venue_id' => ['required', 'string', 'exists:venues,id'],
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

            'pict.image' => 'The pict must be an image file.',
            'pict.mimes' => 'The pict must be a file of type: :values.',

            'category_id.required' => 'Category cannot be empty.',
            'venue_id.required' => 'Venue cannot be empty.',
        ];
    }
}
