<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { nextTick } from 'vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
        onError: async () => {
            await nextTick();
            document.querySelector('.ea-auth-card [aria-invalid="true"]')?.focus();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <header class="ea-auth-card-header">
            <h2>One quiet check.</h2>
            <p>This area holds private event details. Confirm your password to continue.</p>
        </header>

        <form class="ea-auth-form" @submit.prevent="submit">
            <div class="ea-auth-field">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    :aria-invalid="Boolean(form.errors.password)"
                    :aria-describedby="form.errors.password ? 'confirm-password-error' : undefined"
                />
                <InputError id="confirm-password-error" :message="form.errors.password" />
            </div>

            <div class="ea-auth-actions">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Checking…' : 'Confirm password' }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
