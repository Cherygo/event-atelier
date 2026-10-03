<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ mustVerifyEmail: Boolean, status: String });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Your account" />
        <div class="ea-events-page ea-account-page">
            <header class="ea-events-header">
                <div>
                    <h1>Your account</h1>
                    <p>Your personal details and sign-in settings, across every event.</p>
                </div>
            </header>
            <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
            <UpdatePasswordForm />
            <DeleteUserForm />
        </div>
    </AuthenticatedLayout>
</template>
