<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';

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

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: async () => {
            await nextTick();
            document.querySelector('.ea-auth-card [aria-invalid="true"]')?.focus();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <header class="ea-auth-card-header">
            <h2>Welcome back.</h2>
            <p>Return to the plans and people waiting for you.</p>
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
                    :aria-describedby="form.errors.email ? 'login-email-error' : undefined"
                />

                <InputError id="login-email-error" :message="form.errors.email" />
            </div>

            <div class="ea-auth-field">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    :aria-invalid="Boolean(form.errors.password)"
                    :aria-describedby="form.errors.password ? 'login-password-error' : undefined"
                />

                <InputError id="login-password-error" :message="form.errors.password" />
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
