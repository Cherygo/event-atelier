<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';

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

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: async () => {
            await nextTick();
            document.querySelector('.ea-auth-card [aria-invalid="true"]')?.focus();
        },
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
                    :aria-describedby="form.errors.email ? 'reset-email-error' : undefined"
                />

                <InputError id="reset-email-error" :message="form.errors.email" />
            </div>

            <div class="ea-auth-field">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    :aria-invalid="Boolean(form.errors.password)"
                    :aria-describedby="form.errors.password ? 'reset-password-error' : undefined"
                />

                <InputError id="reset-password-error" :message="form.errors.password" />
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
                    required
                    autocomplete="new-password"
                    :aria-invalid="Boolean(form.errors.password_confirmation)"
                    :aria-describedby="form.errors.password_confirmation ? 'reset-password-confirmation-error' : undefined"
                />

                <InputError
                    id="reset-password-confirmation-error"
                    :message="form.errors.password_confirmation"
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
    </GuestLayout>
</template>
