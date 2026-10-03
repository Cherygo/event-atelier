import assert from 'node:assert/strict';
import test from 'node:test';
import { formatEventDate } from '../../resources/js/dateFormat.js';

test('event dates display as dd/mm/yyyy without timezone conversion', () => {
    assert.equal(formatEventDate('2027-12-13T00:00:00.000000Z'), '13/12/2027');
    assert.equal(formatEventDate('2027-01-02'), '02/01/2027');
    assert.equal(formatEventDate('2027-01-02T00:00:00+14:00'), '02/01/2027');
    assert.equal(formatEventDate('2028-02-29'), '29/02/2028');
});

test('events without a date leave the display empty for the page fallback', () => {
    assert.equal(formatEventDate(null), '');
    assert.equal(formatEventDate(undefined), '');
    assert.equal(formatEventDate(''), '');
});
