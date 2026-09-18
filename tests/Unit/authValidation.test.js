import assert from 'node:assert/strict';
import { test } from 'node:test';
import { reactive, nextTick } from 'vue';
import { useAuthValidation, validateAuthField } from '../../resources/js/authValidation.js';

test('email feedback handles missing, malformed and valid addresses', () => {
    for (const email of ['', 'person', 'person@', 'person @example.com']) {
        assert.ok(validateAuthField('email', { email }));
    }
    assert.equal(validateAuthField('email', { email: 'person+event@example.com' }), '');
});

test('registration checks password length and confirmation without restricting login passwords', () => {
    assert.ok(validateAuthField('password', { password: '' }));
    assert.ok(validateAuthField('password', { password: 'short' }, true));
    assert.equal(validateAuthField('password', { password: 'short' }), '');
    assert.equal(validateAuthField('password', { password: 'eight123' }, true), '');
    assert.ok(validateAuthField('password_confirmation', { password: 'eight123', password_confirmation: 'different' }, true));
    assert.equal(validateAuthField('password_confirmation', { password: 'eight123', password_confirmation: 'eight123' }, true), '');
});

test('feedback starts quiet, updates immediately and preserves unrelated server errors', async () => {
    const form = reactive({
        name: '', email: '', password: '', password_confirmation: '', errors: {},
        clearErrors(field) { delete this.errors[field]; },
    });
    const { errors, validate } = useAuthValidation(form, true);
    assert.equal(errors.value.email, '');
    form.email = 'invalid';
    await nextTick();
    assert.ok(errors.value.email);
    form.errors.email = 'This email is already registered.';
    form.password = 'eight123';
    form.password_confirmation = 'eight123';
    await nextTick();
    assert.equal(errors.value.email, 'This email is already registered.');
    assert.equal(errors.value.password_confirmation, '');
    form.password = 'changed123';
    await nextTick();
    assert.ok(errors.value.password_confirmation);
    form.email = 'person@example.com';
    await nextTick();
    assert.equal(errors.value.email, '');
    assert.equal(validate(), false);
    assert.ok(errors.value.name);
});

test('editing a login password clears the previous credential error', async () => {
    const form = reactive({
        email: 'person@example.com', password: 'wrong',
        errors: { email: 'These credentials do not match our records.' },
        clearErrors(field) { delete this.errors[field]; },
    });
    const { errors } = useAuthValidation(form);
    form.password = 'corrected';
    await nextTick();
    assert.equal(errors.value.email, '');
});
