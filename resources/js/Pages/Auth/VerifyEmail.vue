<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <header class="ea-auth-card-header">
            <h2>Check your inbox.</h2>
            <p>We sent a verification link to your email. Open it to make your planning room ready.</p>
        </header>

        <div
            class="ea-auth-status"
            v-if="verificationLinkSent"
            role="status"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form class="ea-auth-form" @submit.prevent="submit">
            <div class="ea-auth-actions ea-auth-actions-split">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Sending…' : 'Resend verification email' }}
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="ea-auth-link"
                    >Log Out</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
