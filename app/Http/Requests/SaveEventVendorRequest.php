<?php

namespace App\Http\Requests;

use App\Models\EventVendor;
use App\VendorStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

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
            'status' => ['sometimes', 'required', Rule::enum(VendorStatus::class)],
            'quote_amount' => ['present_with:currency', 'nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'currency' => ['present_with:quote_amount', 'nullable', 'required_with:quote_amount', Rule::in(EventVendor::CURRENCIES)],
            'quote_details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
