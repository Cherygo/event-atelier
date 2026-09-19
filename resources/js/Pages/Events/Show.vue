<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import EventLayout from '@/Layouts/EventLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ event: { type: Object, required: true }, role: String, can: { type: Object, required: true } });
const form = useForm({
    name: props.event.name,
    type: props.event.type,
    event_date: props.event.event_date?.slice(0, 10) ?? '',
    guest_count: props.event.guest_count ?? '',
    location: props.event.location ?? '',
});
const deletion = useForm({ password: '' });
const types = { wedding: 'Wedding', corporate: 'Corporate event', private: 'Private gathering', conference: 'Conference', custom: 'Custom occasion' };
const save = () => form.patch(route('events.update', props.event.id), { preserveScroll: true });
const deleteEvent = () => deletion.delete(route('events.destroy', props.event.id), { onFinish: () => deletion.reset('password') });
</script>

<template>
    <EventLayout :event="event">
        <Head :title="event.name" />
        <template #description><p>Your access: {{ role }}. Keep the essentials of your occasion together.</p></template>
        <section class="ea-event-section" aria-labelledby="event-details-heading">
            <h2 id="event-details-heading">The occasion</h2>
            <form v-if="can.update" class="ea-details-form" @submit.prevent="save">
                <div class="ea-form-field ea-form-field-wide">
                    <label for="event-name">Event name</label>
                    <input id="event-name" v-model="form.name" required maxlength="120" :aria-invalid="Boolean(form.errors.name)" aria-describedby="event-name-error" />
                    <InputError id="event-name-error" :message="form.errors.name" />
                </div>
                <div class="ea-form-field">
                    <label for="event-type">Occasion type</label>
                    <select id="event-type" v-model="form.type" :aria-invalid="Boolean(form.errors.type)" aria-describedby="event-type-error"><option v-for="(label, value) in types" :key="value" :value="value">{{ label }}</option></select>
                    <InputError id="event-type-error" :message="form.errors.type" />
                </div>
                <div class="ea-form-field">
                    <label for="event-date">Date <span>Optional</span></label>
                    <input id="event-date" v-model="form.event_date" type="date" :aria-invalid="Boolean(form.errors.event_date)" aria-describedby="event-date-error" />
                    <InputError id="event-date-error" :message="form.errors.event_date" />
                </div>
                <div class="ea-form-field">
                    <label for="event-location">Location <span>Optional</span></label>
                    <input id="event-location" v-model="form.location" maxlength="120" :aria-invalid="Boolean(form.errors.location)" aria-describedby="event-location-error" />
                    <InputError id="event-location-error" :message="form.errors.location" />
                </div>
                <div class="ea-form-field">
                    <label for="event-guests">Guest count <span>Optional</span></label>
                    <input id="event-guests" v-model="form.guest_count" type="number" min="1" max="100000" :aria-invalid="Boolean(form.errors.guest_count)" aria-describedby="event-guests-error" />
                    <InputError id="event-guests-error" :message="form.errors.guest_count" />
                </div>
                <div class="ea-form-field-wide"><button class="ea-button" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save details' }}</button></div>
            </form>
            <dl v-else class="ea-event-facts">
                <div><dt>Occasion</dt><dd>{{ types[event.type] }}</dd></div>
                <div><dt>Date</dt><dd>{{ event.event_date?.slice(0, 10) || 'Not set yet' }}</dd></div>
                <div><dt>Location</dt><dd>{{ event.location || 'Not set yet' }}</dd></div>
                <div><dt>Guest count</dt><dd>{{ event.guest_count || 'Not set yet' }}</dd></div>
            </dl>
        </section>
        <details v-if="can.delete" class="ea-event-section ea-sensitive-action">
            <summary>Delete this event</summary>
            <p>This permanently deletes the event, its memberships, and its invitations. Enter your account password to confirm.</p>
            <form class="ea-confirm-form" @submit.prevent="deleteEvent">
                <div class="ea-form-field"><label for="delete-password">Your password</label><input id="delete-password" v-model="deletion.password" type="password" autocomplete="current-password" required :aria-invalid="Boolean(deletion.errors.password)" aria-describedby="delete-password-error" /><InputError id="delete-password-error" :message="deletion.errors.password" /></div>
                <button class="ea-button ea-button-outline" :disabled="deletion.processing">{{ deletion.processing ? 'Deleting…' : 'Permanently delete event' }}</button>
            </form>
        </details>
    </EventLayout>
</template>
