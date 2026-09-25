import test from 'node:test';
import assert from 'node:assert/strict';
import { formatQuote } from '../../resources/js/vendorFormat.js';

test('unknown quotes stay distinct from an explicitly free quote', () => {
    assert.equal(formatQuote({ quote_amount: null, currency: null }), 'Not quoted');
    assert.match(formatQuote({ quote_amount: '0.00', currency: 'EUR' }), /EUR/);
    assert.notEqual(formatQuote({ quote_amount: '0.00', currency: 'EUR' }), 'Not quoted');
});

test('quotes retain their currency code and cents', () => {
    const quote = formatQuote({ quote_amount: '1234.56', currency: 'GBP' });
    assert.match(quote, /GBP/);
    assert.match(quote, /56/);
});
