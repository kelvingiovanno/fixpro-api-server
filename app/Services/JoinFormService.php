<?php

namespace App\Services;

use App\Exceptions\JoinFormValidationException;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Validator;

class JoinFormService
{

    public function set($data) : array
    {
        $formated_form = array_map(function ($item) {
            $item = trim($item);
            $item = strtolower($item);
            return preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $item));
        }, $data);

        SystemSetting::put('area_join_form', json_encode($formated_form));
    
        return $formated_form;
    }

    public function form()
    {
        $form_fields = collect($this->raw())->map(function ($field) {
                return ['field_label' => ucfirst(str_replace('_', ' ', $field))];
        })->values();

        return $form_fields;
    }

    public function raw(): array
    {
        return array_merge(
            ['name'],
            json_decode(SystemSetting::get('area_join_form'), true) ?? []
        );
    }

    public function formated(): array
    {
        return array_map(function ($label) {
            return ucwords(str_replace('_', ' ', $label));
        }, $this->raw());
    }

    public function validate($form) : void
    {
        $form_data = collect($form)->pluck('field_value', 'field_label')->toArray();

        $rules = [];
        foreach ($this->formated() as $label) {
            $rules[$label] = ['required|string'];
        }

        $validator = Validator::make($form_data, $rules);

        if($validator->fails())
        {
            throw new JoinFormValidationException('Form validation failed. Please make sure all required fields are filled in correctly.');
        }
    }

    public function normalize($form) : array
    {
        $form_data = collect($form)->pluck('field_value', 'field_label')->toArray();

        return $form_data;
    }
}
