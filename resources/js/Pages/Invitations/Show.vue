<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';

defineProps({ invitation: Object, acceptUrl: String, matchesEmail: Boolean });
const page = usePage();
const form = useForm({});
</script>

<template>
    <GuestLayout>
        <Head title="Event invitation" />
        <div v-if="invitation" class="ea-invitation">
            <header class="ea-auth-card-header"><h2>You’re invited.</h2><p>Join <strong>{{ invitation.event_name }}</strong> as a {{ invitation.role.toLowerCase() }}.</p></header>
            <p>This invitation is for <strong>{{ invitation.email }}</strong>.</p>
            <template v-if="!page.props.auth.user">
                <p>Sign in with that email, or create a free account. You’ll return here to accept the invitation.</p>
                <div class="ea-invitation-actions"><Link :href="route('register')" class="ea-button">Create an account</Link><Link :href="route('login')" class="ea-text-link">Log in</Link></div>
            </template>
            <form v-else-if="matchesEmail" @submit.prevent="form.post(acceptUrl)">
                <InputError :message="form.errors.invitation" />
                <button class="ea-button" :disabled="form.processing">{{ form.processing ? 'Joining…' : 'Accept invitation' }}</button>
            </form>
            <template v-else>
                <p>You’re signed in as {{ page.props.auth.user.email }}. Sign out, then reopen this email link and sign in with the invited address.</p>
                <Link :href="route('logout')" method="post" as="button" class="ea-button">Log out</Link>
            </template>
        </div>
        <div v-else class="ea-invitation">
            <header class="ea-auth-card-header"><h2>This link is no longer active.</h2><p>It may have expired, been revoked, or already been accepted. Ask the event owner for a new invitation if you still need access.</p></header>
            <Link :href="page.props.auth.user ? route('events.index') : '/'" class="ea-button">{{ page.props.auth.user ? 'My events' : 'Back to home' }}</Link>
        </div>
    </GuestLayout>
</template>
