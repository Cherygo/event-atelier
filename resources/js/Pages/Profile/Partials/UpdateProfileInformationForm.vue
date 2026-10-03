<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useAuthValidation } from '@/authValidation';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { nextTick } from 'vue';

defineProps({ mustVerifyEmail: Boolean, status: String });

const user = usePage().props.auth.user;
const form = useForm({ name: user.name, email: user.email });
const { errors, touch, validate } = useAuthValidation(form, false, ['name', 'email']);
const focusError = async () => {
    await nextTick();
    document.querySelector('#profile-details [aria-invalid="true"]')?.focus();
};
const updateProfile = () => {
    if (form.processing) return;
    if (!validate()) {
        focusError();
        return;
    }
    form.patch(route('profile.update'), { preserveScroll: true, onError: focusError });
};
</script>

<template>
    <section class="ea-account-section" aria-labelledby="profile-details-heading">
        <header>
            <h2 id="profile-details-heading">Personal details</h2>
            <p>Keep your name and sign-in email up to date.</p>
        </header>
        <form id="profile-details" class="ea-account-form" novalidate @submit.prevent="updateProfile">
            <div class="ea-form-field">
                <InputLabel for="name" value="Name" />
                <TextInput id="name" v-model="form.name" required maxlength="255" autocomplete="name" :aria-invalid="Boolean(errors.name)" aria-describedby="profile-name-error" @blur="touch('name')" />
                <InputError reserve-space id="profile-name-error" :message="errors.name" />
            </div>
            <div class="ea-form-field">
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" required maxlength="255" autocomplete="username" :aria-invalid="Boolean(errors.email)" aria-describedby="profile-email-error" @blur="touch('email')" />
                <InputError reserve-space id="profile-email-error" :message="errors.email" />
            </div>
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="ea-account-hint">
                <p>Your email address is unverified. <Link :href="route('verification.send')" method="post" as="button" class="ea-text-link">Resend verification email</Link></p>
                <p v-if="status === 'verification-link-sent'" role="status">A new verification link has been sent to your email address.</p>
            </div>
            <div class="ea-account-actions">
                <button class="ea-button" :disabled="form.processing">{{ form.processing ? 'Saving details…' : 'Save details' }}</button>
                <p class="ea-account-result" role="status">{{ form.recentlySuccessful ? 'Your details have been saved.' : '' }}</p>
            </div>
        </form>
    </section>
</template>
