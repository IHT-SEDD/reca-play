<?php

namespace App\Http\Requests\Master\Field;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateFieldRequest extends StoreFieldRequest
{
   protected $fieldId;

    protected function prepareForValidation()
    {
        $this->fieldId = $this->input('id');

        if (!$this->fieldId) {
            throw new \Exception('field ID not found.');
        }
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['code'] = ['nullable', 'string', 'min:2', 'unique:fields,code,' . $this->fieldId];

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $field = \App\Models\Master\Field::findOrFail($this->fieldId);

        if ($this->hasFile('pict')) {
            if ($field->pict_path && Storage::disk('public')->exists(str_replace('storage/', '', $field->pict_path))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $field->pict_path));
            }

            $file = $this->file('pict');
            $path = $file->store('field_pictures', 'public');

            $data['pict_path'] = 'storage/' . $path;
            $data['pict_filename'] = $file->getClientOriginalName();
        } else {
            $data['pict_path'] = $field->pict_path;
            $data['pict_filename'] = $field->pict_filename;
        }

            unset($data['pict']);

        return $data;
    }
}
