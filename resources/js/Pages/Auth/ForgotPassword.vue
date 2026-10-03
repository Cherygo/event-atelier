<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';
import { useAuthValidation } from '@/authValidation';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});
const { errors, touch, validate } = useAuthValidation(form, false, ['email']);
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
    form.post(route('password.email'), {
        onError: focusError,
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <header class="ea-auth-card-header">
            <h2>Find your way back.</h2>
            <p>Enter your email and we’ll send you a secure link to choose a new password.</p>
        </header>

        <div v-if="status" class="ea-auth-status" role="status">
            {{ status }}
        </div>

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
                    :aria-describedby="errors.email ? 'forgot-email-error' : undefined"
                />

                <InputError reserve-space id="forgot-email-error" :message="errors.email" />
            </div>

            <div class="ea-auth-actions">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Sending your link…' : 'Send reset link' }}
                </PrimaryButton>
            </div>
        </form>

        <p class="ea-auth-alternate">
            Remembered it? <Link :href="route('login')">Return to log in</Link>
        </p>
    </GuestLayout>
</template>
