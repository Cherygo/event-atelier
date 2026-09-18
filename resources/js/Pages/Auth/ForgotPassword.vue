<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onError: async () => {
            await nextTick();
            document.querySelector('.ea-auth-card [aria-invalid="true"]')?.focus();
        },
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

        <form class="ea-auth-form" @submit.prevent="submit">
            <div class="ea-auth-field">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    :aria-invalid="Boolean(form.errors.email)"
                    :aria-describedby="form.errors.email ? 'forgot-email-error' : undefined"
                />

                <InputError id="forgot-email-error" :message="form.errors.email" />
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
