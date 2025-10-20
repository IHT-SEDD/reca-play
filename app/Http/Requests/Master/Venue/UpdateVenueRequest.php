<?php

namespace App\Http\Requests\Master\Venue;

use Illuminate\Support\Facades\Storage;

class UpdateVenueRequest extends StoreVenueRequest
{
    protected $venueId;

    protected function prepareForValidation()
    {
        $this->venueId = $this->input('id');

        if (!$this->venueId) {
            throw new \Exception('Venue ID not found.');
        }
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['code'] = ['nullable', 'string', 'min:2', 'unique:venues,code,' . $this->venueId];

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $venue = \App\Models\Master\Venue::findOrFail($this->venueId);

        if ($this->hasFile('logo')) {
            if ($venue->logo_path && Storage::disk('public')->exists(str_replace('storage/', '', $venue->logo_path))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $venue->logo_path));
            }

            $file = $this->file('logo');
            $path = $file->store('venue_logos', 'public');

            $data['logo_path'] = 'storage/' . $path;
            $data['logo_filename'] = $file->getClientOriginalName();
        } else {
            $data['logo_path'] = $venue->logo_path;
            $data['logo_filename'] = $venue->logo_filename;
        }

            unset($data['logo']);

        return $data;
    }
}
