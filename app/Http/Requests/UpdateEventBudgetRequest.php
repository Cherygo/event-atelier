<?php

namespace App\Http\Requests;

use App\BudgetAmount;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateEventBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('view', $this->route('event'));

        return $this->user()->can('editPlanning', $this->route('event'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'currency' => ['required', Rule::in(BudgetAmount::CURRENCIES)],
            'target' => ['present', 'nullable', 'regex:'.BudgetAmount::PATTERN],
        ];
    }

    public function messages(): array
    {
        return ['target.regex' => 'Enter a target up to 999999999.99 with at most two decimal places.'];
    }
}
