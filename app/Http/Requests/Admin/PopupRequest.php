<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PopupRequest extends FormRequest
{
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
        $popupId = $this->route('popup')?->id ?? $this->route('popup');

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('popups', 'slug')->ignore($popupId),
            ],
            'content' => ['nullable', 'string'],
            'cta_text' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'layout' => ['nullable', Rule::in(['stacked', 'left-right', 'right-left', 'top-bottom'])],
            'is_active' => ['sometimes', 'boolean'],
            'display_start' => ['nullable', 'date'],
            'display_end' => ['nullable', 'date', 'after_or_equal:display_start'],
            'display_pages' => ['nullable', 'array'],
            'display_pages.*' => ['string', 'max:255'],
            'display_pages_custom' => ['nullable', 'string'],
            'display_devices' => ['nullable', 'array'],
            'display_devices.*' => ['in:desktop,mobile,tablet'],
            'frequency' => ['nullable', Rule::in(['once_per_session', 'once_per_day', 'once_per_visit', 'always'])],
            'delay_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'max_impressions' => ['nullable', 'integer', 'min:1'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'options' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        if ($this->has('is_active')) {
            $input['is_active'] = filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        foreach (['display_pages', 'display_devices'] as $field) {
            if (is_string($this->input($field))) {
                $input[$field] = array_filter(array_map('trim', explode(',', $this->input($field))));
            }
        }

        if (isset($input['frequency']) && is_string($input['frequency'])) {
            $input['frequency'] = Str::lower($input['frequency']);
        }

        $this->replace($input);
    }
}
