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

test('recovery validates only its selected fields and keeps token errors when passwords change', () => {
    const form = reactive({
        email: 'person@example.com', password: '', password_confirmation: '', errors: {},
        clearErrors(field) { delete this.errors[field]; },
    });
    const emailOnly = useAuthValidation(form, false, ['email']);
    assert.equal(emailOnly.validate(), true);
    const reset = useAuthValidation(form, true, ['email', 'password', 'password_confirmation']);
    assert.equal(reset.validate(), false);
    form.password = 'new-password';
    form.password_confirmation = 'new-password';
    assert.equal(reset.validate(), true);
    form.errors.email = 'This password reset token is invalid.';
    form.password = 'another-password';
    assert.equal(reset.errors.value.email, 'This password reset token is invalid.');
    assert.ok(reset.errors.value.password_confirmation);
});

test('password changes require the current password and can reset feedback after success', () => {
    const form = reactive({
        current_password: '', password: '', password_confirmation: '', errors: {},
        clearErrors(field) { delete this.errors[field]; },
    });
    const { errors, validate, resetFeedback } = useAuthValidation(form, true, ['current_password', 'password', 'password_confirmation']);
    assert.equal(validate(), false);
    assert.equal(errors.value.current_password, 'Enter your current password.');
    form.current_password = 'old';
    form.password = 'new-password';
    form.password_confirmation = 'new-password';
    assert.equal(validate(), true);
    form.current_password = form.password = form.password_confirmation = '';
    resetFeedback();
    assert.deepEqual(errors.value, { current_password: '', password: '', password_confirmation: '' });
    assert.equal(validate(), false);
});
