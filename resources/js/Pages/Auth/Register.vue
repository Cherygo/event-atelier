<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: async () => {
            await nextTick();
            document.querySelector('.ea-auth-card [aria-invalid="true"]')?.focus();
        },
    });
};
</script>

<template>
    <GuestLayout class="ea-auth-register">
        <Head title="Register" />

        <header class="ea-auth-card-header">
            <h2>Start your planning room.</h2>
            <p>Create an account now; shape the occasion one detail at a time.</p>
        </header>

        <form class="ea-auth-form" @submit.prevent="submit">
            <div class="ea-auth-field">
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    :aria-invalid="Boolean(form.errors.name)"
                    :aria-describedby="form.errors.name ? 'register-name-error' : undefined"
                />

                <InputError id="register-name-error" :message="form.errors.name" />
            </div>

            <div class="ea-auth-field">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    :aria-invalid="Boolean(form.errors.email)"
                    :aria-describedby="form.errors.email ? 'register-email-error' : undefined"
                />

                <InputError id="register-email-error" :message="form.errors.email" />
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
                    :aria-describedby="form.errors.password ? 'register-password-error' : undefined"
                />

                <InputError id="register-password-error" :message="form.errors.password" />
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
                    :aria-describedby="form.errors.password_confirmation ? 'register-password-confirmation-error' : undefined"
                />

                <InputError
                    id="register-password-confirmation-error"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="ea-auth-actions">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Creating your room…' : 'Create account' }}</span>
                </PrimaryButton>
            </div>
        </form>

        <p class="ea-auth-alternate">
            Already have an account?
            <Link :href="route('login')">Log in</Link>
        </p>
    </GuestLayout>
</template>
