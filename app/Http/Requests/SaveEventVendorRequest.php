<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SaveEventVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('view', $this->route('event'));

        return $this->user()->can('editPlanning', $this->route('event'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:60'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'website' => ['nullable', 'url:http,https', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
