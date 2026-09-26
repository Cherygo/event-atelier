<?php

namespace App\Http\Requests;

use App\BudgetAmount;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreEventExpensePaymentRequest extends FormRequest
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
            'amount' => ['required', 'regex:'.BudgetAmount::PATTERN, 'numeric', 'gt:0'],
            'currency' => ['required', Rule::in(BudgetAmount::CURRENCIES)],
            'paid_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.regex' => 'Enter an amount up to 999999999.99 with at most two decimal places.',
            'amount.gt' => 'A recorded payment must be greater than zero.',
            'paid_on.before_or_equal' => 'Record only payments already made. Choose today or an earlier date.',
        ];
    }
}
