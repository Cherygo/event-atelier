<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import EventLayout from '@/Layouts/EventLayout.vue';
import EventMemberRow from '@/Components/EventMemberRow.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    event: Object, owner: Object, members: Array, roles: Array, can: Object,
});
const transfer = useForm({ member_id: '', password: '' });
const transferOwnership = () => transfer.patch(route('events.ownership.update', props.event.id), { preserveScroll: true, onFinish: () => transfer.reset('password') });
</script>

<template>
    <EventLayout :event="event">
        <Head :title="'People · ' + event.name" />
        <template #description><p>Bring your planning circle together. Access applies only to this event.</p></template>
        <section class="ea-event-section" aria-labelledby="people-heading">
            <h2 id="people-heading">Your planning circle <span class="ea-section-count">{{ members.length + 1 }}</span></h2>
            <ul class="ea-people-list">
                <li class="ea-person-row"><div class="ea-person-identity"><strong>{{ owner.name }}</strong><span v-if="owner.email">{{ owner.email }}</span></div><span class="ea-access-label">Owner</span></li>
                <EventMemberRow v-for="member in members" :key="member.id + '-' + member.role" :member="member" :event-id="event.id" :roles="roles" />
            </ul>
        </section>
        <details v-if="can.transferOwnership && members.length" class="ea-event-section ea-sensitive-action">
            <summary>Transfer event ownership</summary>
            <p>Choose an existing member to become the sole owner. You’ll become a planner / admin. Pending invitations will be revoked so the new owner can review access.</p>
            <form class="ea-confirm-form" @submit.prevent="transferOwnership">
                <div class="ea-form-field"><label for="new-owner">New owner</label><select id="new-owner" v-model="transfer.member_id" required :aria-invalid="Boolean(transfer.errors.member_id)" aria-describedby="new-owner-error"><option value="" disabled>Choose a member</option><option v-for="member in members" :key="member.id" :value="member.id">{{ member.name }}</option></select><InputError id="new-owner-error" :message="transfer.errors.member_id" /></div>
                <div class="ea-form-field"><label for="owner-password">Your password</label><input id="owner-password" v-model="transfer.password" type="password" autocomplete="current-password" required :aria-invalid="Boolean(transfer.errors.password)" aria-describedby="owner-password-error" /><InputError id="owner-password-error" :message="transfer.errors.password" /></div>
                <button class="ea-button ea-button-outline" :disabled="transfer.processing">{{ transfer.processing ? 'Transferring…' : 'Confirm ownership transfer' }}</button>
            </form>
        </details>
    </EventLayout>
</template>
