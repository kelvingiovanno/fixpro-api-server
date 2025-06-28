<?php

namespace App\Services;

use App\Exceptions\JoinFormValidationException;

use Illuminate\Support\Facades\Validator;

class JoinFormService
{
    public function __construct(
        protected AreaService $areaService,
    ) {}

    public function form()
    {   
        $form_fields = collect($this->areaService->get_join_form())->map(function ($field) {
                return ['field_label' => ucfirst(str_replace('_', ' ', $field))];
        })->values();

        return $form_fields;
    }

    public function formated(): array
    {
        return array_map(function ($label) {
            return ucfirst(str_replace('_', ' ', $label));
        }, $this->areaService->get_join_form());
    }

    public function validate($form) : void
    {
        $form_data = collect($form)->pluck('field_value', 'field_label')->toArray();

        $rules = [];
        foreach ($this->formated() as $label) {
            $rules[$label] = 'required|string';
        }

        $validator = Validator::make($form_data, $rules);

        if($validator->fails())
        {
            throw new JoinFormValidationException('Form validation failed. Please make sure all required fields are filled in correctly.');
        }
    }

    public function normalize($form): array
    {
        $form_data = collect($form)->pluck('field_value', 'field_label')->toArray();

        $normalized = [];
        foreach ($form_data as $label => $value) {
            $normalized_key = strtolower(trim($label));
            $normalized_key = str_replace(' ', '_', $normalized_key);
            $normalized_key = preg_replace('/[^a-z0-9_]/', '', $normalized_key);

            $normalized[$normalized_key] = $value;
        }

        return $normalized;
    }
}
