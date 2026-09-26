<?php

namespace App;

class BudgetAmount
{
    public const CURRENCIES = ['EUR', 'USD', 'GBP', 'CAD', 'AUD', 'CHF', 'NZD'];

    public const PATTERN = '/\A[0-9]{1,9}(?:\.[0-9]{1,2})?\z/';

    /** Convert a validated decimal amount to cents without float rounding. */
    public static function toMinor(string $amount): int
    {
        if (! preg_match(self::PATTERN, $amount)) {
            throw new \InvalidArgumentException('Enter an amount up to 999999999.99 with at most two decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }
}
