<?php

namespace Database\Factories;

use App\Models\EventExpense;
use App\Models\EventExpensePayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventExpensePayment>
 */
class EventExpensePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_expense_id' => EventExpense::factory()->withActualCost(),
            'amount_minor' => 25000,
            'paid_on' => now()->toDateString(),
        ];
    }
}
