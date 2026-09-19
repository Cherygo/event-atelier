<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import EventLayout from '@/Layouts/EventLayout.vue';
import EventMemberRow from '@/Components/EventMemberRow.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    event: Object, owner: Object, members: Array, invitations: Array, roles: Array, can: Object,
});
const invite = useForm({ email: '', role: 'viewer' });
const resend = useForm({ email: '', role: '' });
const revoke = useForm({});
const transfer = useForm({ member_id: '', password: '' });
const sendInvitation = () => invite.post(route('events.invitations.store', props.event.id), { preserveScroll: true, onSuccess: () => invite.reset('email') });
const resendInvitation = (invitation) => {
    resend.email = invitation.email;
    resend.role = invitation.role;
    resend.post(route('events.invitations.store', props.event.id), { preserveScroll: true });
};
const revokeInvitation = (invitation) => revoke.delete(route('events.invitations.destroy', [props.event.id, invitation.id]), { preserveScroll: true });
const transferOwnership = () => transfer.patch(route('events.ownership.update', props.event.id), { preserveScroll: true, onFinish: () => transfer.reset('password') });
const labels = { admin: 'Planner / admin', editor: 'Editor', viewer: 'Viewer' };
</script>

<template>
    <EventLayout :event="event">
        <Head :title="'People · ' + event.name" />
        <template #description><p>Bring your planning circle together. Access applies only to this event.</p></template>
        <section v-if="can.manageMembers" class="ea-event-section" aria-labelledby="invite-heading">
            <h2 id="invite-heading">Invite someone</h2>
            <p>We’ll email them a link to join. They can create a free account if they don’t have one yet.</p>
            <form class="ea-invite-form" @submit.prevent="sendInvitation">
                <div class="ea-form-field">
                    <label for="invite-email">Email address</label>
                    <input id="invite-email" v-model="invite.email" type="email" autocomplete="email" required maxlength="255" placeholder="name@example.com" :aria-invalid="Boolean(invite.errors.email)" aria-describedby="invite-email-error" />
                    <InputError id="invite-email-error" :message="invite.errors.email" />
                </div>
                <div class="ea-form-field">
                    <label for="invite-role">Access level</label>
                    <select id="invite-role" v-model="invite.role" aria-describedby="access-help invite-role-error" :aria-invalid="Boolean(invite.errors.role)"><option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option></select>
                    <InputError id="invite-role-error" :message="invite.errors.role" />
                </div>
                <button class="ea-button" :disabled="invite.processing">{{ invite.processing ? 'Sending…' : 'Send invitation' }}</button>
            </form>
            <p id="access-help" class="ea-access-help">Viewers can read the workspace. Editors can edit planning content. Planners / admins can also manage event details and invite editors or viewers. Only the owner can appoint admins.</p>
        </section>
        <section class="ea-event-section" aria-labelledby="people-heading">
            <h2 id="people-heading">Your planning circle <span class="ea-section-count">{{ members.length + 1 }}</span></h2>
            <ul class="ea-people-list">
                <li class="ea-person-row"><div class="ea-person-identity"><strong>{{ owner.name }}</strong><span v-if="owner.email">{{ owner.email }}</span></div><span class="ea-access-label">Owner</span></li>
                <EventMemberRow v-for="member in members" :key="member.id + '-' + member.role" :member="member" :event-id="event.id" :roles="roles" />
            </ul>
        </section>
        <section v-if="can.manageMembers" class="ea-event-section" aria-labelledby="pending-heading">
            <h2 id="pending-heading">Pending invitations</h2>
            <InputError :message="resend.errors.email || resend.errors.role" />
            <p v-if="!invitations.length">No invitations waiting for a response.</p>
            <ul v-else class="ea-people-list">
                <li v-for="invitation in invitations" :key="invitation.id" class="ea-person-row">
                    <div class="ea-person-identity"><strong>{{ invitation.email }}</strong><span>{{ labels[invitation.role] }} · {{ invitation.expired ? 'Link inactive — resend to invite again' : 'Awaiting acceptance' }}</span></div>
                    <div v-if="invitation.canManage" class="ea-person-controls">
                        <button type="button" class="ea-button ea-button-outline" :disabled="resend.processing || revoke.processing" @click="resendInvitation(invitation)">Resend email</button>
                        <button type="button" class="ea-text-link" :disabled="resend.processing || revoke.processing" @click="revokeInvitation(invitation)">Revoke</button>
                    </div>
                </li>
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
