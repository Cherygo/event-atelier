<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';
import { useAuthValidation } from '@/authValidation';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const { errors, touch, validate } = useAuthValidation(form, true, ['email', 'password', 'password_confirmation']);
const focusError = async () => {
    await nextTick();
    document.querySelector('.ea-auth-card [aria-invalid="true"]')?.focus();
};

const submit = () => {
    if (form.processing) return;
    if (!validate()) {
        focusError();
        return;
    }
    form.post(route('password.store'), {
        onSuccess: () => form.reset('password', 'password_confirmation'),
        onError: focusError,
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <header class="ea-auth-card-header">
            <h2>Choose a new key.</h2>
            <p>Set a new password, then return to your planning room.</p>
        </header>

        <form class="ea-auth-form" novalidate @submit.prevent="submit">
            <div class="ea-auth-field">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full"
                    v-model="form.email"
                    @blur="touch('email')"
                    required
                    autofocus
                    autocomplete="username"
                    :aria-invalid="Boolean(errors.email)"
                    :aria-describedby="errors.email ? 'reset-email-error' : undefined"
                />

                <InputError reserve-space id="reset-email-error" :message="errors.email" />
            </div>

            <div class="ea-auth-field">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full"
                    v-model="form.password"
                    @blur="touch('password')"
                    required
                    autocomplete="new-password"
                    :aria-invalid="Boolean(errors.password)"
                    aria-describedby="reset-password-hint reset-password-error"
                />

                <p id="reset-password-hint" class="ea-auth-hint">Use at least 8 characters.</p>
                <InputError reserve-space id="reset-password-error" :message="errors.password" />
            </div>

            <div class="ea-auth-field">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="block w-full"
                    v-model="form.password_confirmation"
                    @blur="touch('password_confirmation')"
                    required
                    autocomplete="new-password"
                    :aria-invalid="Boolean(errors.password_confirmation)"
                    :aria-describedby="errors.password_confirmation ? 'reset-password-confirmation-error' : undefined"
                />

                <InputError
                    reserve-space
                    id="reset-password-confirmation-error"
                    :message="errors.password_confirmation"
                />
            </div>

            <div class="ea-auth-actions">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving password…' : 'Reset password' }}
                </PrimaryButton>
            </div>
        </form>
        <p class="ea-auth-alternate">
            Link expired or already used? <Link :href="route('password.request')">Request a new reset link</Link>
        </p>
    </GuestLayout>
</template>
