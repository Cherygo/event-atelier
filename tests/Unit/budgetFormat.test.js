import assert from 'node:assert/strict';
import test from 'node:test';
import { amountInput, formatBudgetAmount } from '../../resources/js/budgetFormat.js';

test('budget input preserves cents and distinguishes zero from unknown', () => {
    assert.equal(amountInput(null), '');
    assert.equal(amountInput(0), '0.00');
    assert.equal(amountInput(101), '1.01');
    assert.equal(amountInput(99999999999), '999999999.99');
});

test('budget display states currency and preserves unknown amounts', () => {
    assert.equal(formatBudgetAmount(null, 'EUR'), 'Not set');
    assert.equal(formatBudgetAmount(0, null), 'Not set');
    assert.match(formatBudgetAmount(0, 'EUR'), /EUR.*0\.00/);
    assert.match(formatBudgetAmount(123456, 'GBP'), /GBP.*1,234\.56/);
});
