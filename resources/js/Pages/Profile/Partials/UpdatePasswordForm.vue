<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useAuthValidation } from '@/authValidation';
import { useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';

const form = useForm({ current_password: '', password: '', password_confirmation: '' });
const { errors, touch, validate, resetFeedback } = useAuthValidation(form, true, ['current_password', 'password', 'password_confirmation']);
const focusError = async () => {
    await nextTick();
    document.querySelector('#profile-password [aria-invalid="true"]')?.focus();
};
const updatePassword = () => {
    if (form.processing) return;
    if (!validate()) {
        focusError();
        return;
    }
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            resetFeedback();
        },
        onError: focusError,
    });
};
</script>

<template>
    <section class="ea-account-section" aria-labelledby="profile-password-heading">
        <header>
            <h2 id="profile-password-heading">Password</h2>
            <p>Choose a long, unique password that you don’t use elsewhere.</p>
        </header>
        <form id="profile-password" class="ea-account-form" novalidate @submit.prevent="updatePassword">
            <div class="ea-form-field">
                <InputLabel for="current_password" value="Current password" />
                <TextInput id="current_password" v-model="form.current_password" type="password" required autocomplete="current-password" :aria-invalid="Boolean(errors.current_password)" aria-describedby="current-password-error" @blur="touch('current_password')" />
                <InputError reserve-space id="current-password-error" :message="errors.current_password" />
            </div>
            <div class="ea-form-field">
                <InputLabel for="password" value="New password" />
                <TextInput id="password" v-model="form.password" type="password" required autocomplete="new-password" :aria-invalid="Boolean(errors.password)" aria-describedby="new-password-hint new-password-error" @blur="touch('password')" />
                <p id="new-password-hint" class="ea-account-hint">Use at least 8 characters.</p>
                <InputError reserve-space id="new-password-error" :message="errors.password" />
            </div>
            <div class="ea-form-field">
                <InputLabel for="password_confirmation" value="Confirm new password" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" :aria-invalid="Boolean(errors.password_confirmation)" aria-describedby="confirm-password-error" @blur="touch('password_confirmation')" />
                <InputError reserve-space id="confirm-password-error" :message="errors.password_confirmation" />
            </div>
            <div class="ea-account-actions">
                <button class="ea-button" :disabled="form.processing">{{ form.processing ? 'Updating password…' : 'Update password' }}</button>
                <p class="ea-account-result" role="status">{{ form.recentlySuccessful ? 'Your password has been updated.' : '' }}</p>
            </div>
        </form>
    </section>
</template>
