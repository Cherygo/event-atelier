<?php

namespace Tests\Unit;

use App\BudgetAmount;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BudgetAmountTest extends TestCase
{
    public static function validAmounts(): array
    {
        return [['0', 0], ['0.01', 1], ['12.3', 1230], ['1234.56', 123456], ['999999999.99', 99999999999]];
    }

    #[DataProvider('validAmounts')]
    public function test_decimal_amounts_convert_to_exact_minor_units(string $input, int $expected): void
    {
        $this->assertSame($expected, BudgetAmount::toMinor($input));
    }

    public static function invalidAmounts(): array
    {
        return [['-1'], ['1.234'], ['1e3'], ['1000000000'], ['1,000'], ['NaN'], [''], ["12\n"], ['+12']];
    }

    #[DataProvider('invalidAmounts')]
    public function test_unsupported_amounts_are_not_silently_rounded(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        BudgetAmount::toMinor($input);
    }
}
