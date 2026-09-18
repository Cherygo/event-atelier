<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';
import { useAuthValidation } from '@/authValidation';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const { errors, touch, validate } = useAuthValidation(form, false);

const focusError = async () => {
    await nextTick();
    document.querySelector('.ea-auth-card [aria-invalid="true"]')?.focus();
};

const submit = () => {
    if (!validate()) {
        focusError();
        return;
    }
    form.post(route('login'), {
        onSuccess: () => form.reset('password'),
        onError: focusError,
    });
};
</script>

<template>
    <GuestLayout class="ea-auth-login">
        <Head title="Log in" />

        <header class="ea-auth-card-header">
            <h2>Welcome back.</h2>
            <p>Return to the plans and people waiting for you.</p>
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
                    :aria-describedby="errors.email ? 'login-email-error' : undefined"
                />

                <InputError reserve-space id="login-email-error" :message="errors.email" />
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
                    autocomplete="current-password"
                    :aria-invalid="Boolean(errors.password)"
                    :aria-describedby="errors.password ? 'login-password-error' : undefined"
                />

                <InputError reserve-space id="login-password-error" :message="errors.password" />
            </div>

            <div class="ea-auth-options">
                <label class="ea-auth-check">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span>Remember me</span>
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="ea-auth-link"
                >
                    Forgot your password?
                </Link>
            </div>

            <div class="ea-auth-actions">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Opening your atelier…' : 'Log in' }}</span>
                </PrimaryButton>
            </div>
        </form>

        <p class="ea-auth-alternate">
            New to Event Atelier?
            <Link :href="route('register')">Create your free account</Link>
        </p>
    </GuestLayout>
</template>
