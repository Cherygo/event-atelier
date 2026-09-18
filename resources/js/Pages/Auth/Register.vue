<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';
import { useAuthValidation } from '@/authValidation';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const { errors, touch, validate } = useAuthValidation(form, true);

const focusError = async () => {
    await nextTick();
    document.querySelector('.ea-auth-card [aria-invalid="true"]')?.focus();
};

const submit = () => {
    if (!validate()) {
        focusError();
        return;
    }
    form.post(route('register'), {
        onSuccess: () => form.reset('password', 'password_confirmation'),
        onError: focusError,
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

        <form class="ea-auth-form" novalidate @submit.prevent="submit">
            <div class="ea-auth-field">
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="block w-full"
                    v-model="form.name"
                    @blur="touch('name')"
                    required
                    autofocus
                    autocomplete="name"
                    :aria-invalid="Boolean(errors.name)"
                    :aria-describedby="errors.name ? 'register-name-error' : undefined"
                />

                <InputError reserve-space id="register-name-error" :message="errors.name" />
            </div>

            <div class="ea-auth-field">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full"
                    v-model="form.email"
                    @blur="touch('email')"
                    required
                    autocomplete="username"
                    :aria-invalid="Boolean(errors.email)"
                    :aria-describedby="errors.email ? 'register-email-error' : undefined"
                />

                <InputError reserve-space id="register-email-error" :message="errors.email" />
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
                    :aria-describedby="errors.password ? 'register-password-error' : undefined"
                />

                <InputError reserve-space id="register-password-error" :message="errors.password" />
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
                    :aria-describedby="errors.password_confirmation ? 'register-password-confirmation-error' : undefined"
                />

                <InputError reserve-space
                    id="register-password-confirmation-error"
                    :message="errors.password_confirmation"
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
