<script setup>
import { ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import EventLayout from '@/Layouts/EventLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ event: Object, canManage: Boolean, settings: Object, shareUrl: String });
const form = useForm({ ...props.settings });
const publication = useForm({});
const confirmingStop = ref(false);
const copyStatus = ref('');
const linkInput = ref(null);
watch(() => props.shareUrl, () => { copyStatus.value = ''; });
const save = () => form.patch(route('events.sharing.update', props.event.id), {
    preserveScroll: true,
    onSuccess: () => form.defaults(),
});
const publish = () => publication.post(route('events.sharing.store', props.event.id), { preserveScroll: true });
const stop = () => publication.delete(route('events.sharing.destroy', props.event.id), {
    preserveScroll: true,
    onSuccess: () => { confirmingStop.value = false; },
});
const copy = async () => {
    try {
        await navigator.clipboard.writeText(props.shareUrl);
        copyStatus.value = 'Link copied.';
    } catch {
        linkInput.value?.focus();
        linkInput.value?.select();
        copyStatus.value = 'Select and copy the link from the field above.';
    }
};
</script>

<template>
    <EventLayout :event="event">
        <Head :title="'Shared page · ' + event.name" />
        <template #description><p>A considered view of your plans, for the people you choose.</p></template>
        <section v-if="!canManage" class="ea-event-section">
            <h2>Sharing is managed by the owner</h2>
            <p>Your workspace access hasn’t changed. Only the event owner can choose what is published and manage the public link.</p>
        </section>
        <template v-else>
            <section class="ea-event-section ea-sharing-status" aria-labelledby="sharing-heading">
                <div class="ea-sharing-title"><h2 id="sharing-heading">Shared page</h2><span class="ea-tag" :class="{ 'ea-tag-green': shareUrl }">{{ shareUrl ? 'Published' : 'Private' }}</span></div>
                <p>{{ shareUrl ? 'Anyone with the link can read this page, without an account. Share it thoughtfully.' : 'Nothing is public yet. Save your choices, then publish when you’re ready.' }}</p>
                <template v-if="shareUrl">
                    <div class="ea-form-field"><label for="share-url">Public link</label><input id="share-url" ref="linkInput" :value="shareUrl" readonly @focus="$event.target.select()" /></div>
                    <div class="ea-sharing-actions">
                        <button type="button" class="ea-button" @click="copy">Copy link</button>
                        <a :href="shareUrl" target="_blank" rel="noopener noreferrer" class="ea-button ea-button-outline">Open page <span class="sr-only">in a new tab</span></a>
                        <button type="button" class="ea-text-link" :disabled="publication.processing" @click="confirmingStop = !confirmingStop" :aria-expanded="confirmingStop">Stop sharing</button>
                    </div>
                    <p class="ea-sharing-feedback" role="status">{{ copyStatus }}</p>
                    <div v-if="confirmingStop" class="ea-sharing-confirm">
                        <p>The current link will stop working. Visitors may still have copies they saved earlier.</p>
                        <div class="ea-sharing-actions"><button type="button" class="ea-button" :disabled="publication.processing" @click="stop">{{ publication.processing ? 'Stopping…' : 'Confirm stop sharing' }}</button><button type="button" class="ea-text-link" :disabled="publication.processing" @click="confirmingStop = false">Keep sharing</button></div>
                    </div>
                </template>
                <button v-else type="button" class="ea-button" :disabled="publication.processing || form.processing || form.isDirty" @click="publish">{{ publication.processing ? 'Publishing…' : 'Publish shared page' }}</button>
                <p v-if="form.isDirty" class="ea-sharing-help">You have unsaved choices. Save them before publishing.</p>
            </section>
            <section class="ea-event-section" aria-labelledby="sharing-choices-heading">
                <h2 id="sharing-choices-heading">Choose what to share</h2>
                <p>The event name is always included. Everything else starts private. Saved choices apply to the live page immediately.</p>
                <form class="ea-sharing-form" @submit.prevent="save">
                    <fieldset class="ea-sharing-options">
                        <legend class="sr-only">Event details to include</legend>
                        <label class="ea-sharing-option"><input v-model="form.show_date" type="checkbox" /><span><strong>Event date</strong><small>The date from your event details.</small></span></label>
                        <InputError :message="form.errors.show_date" />
                        <label class="ea-sharing-option"><input v-model="form.show_location" type="checkbox" /><span><strong>Venue &amp; location</strong><small>The location exactly as saved in your event details.</small></span></label>
                        <InputError :message="form.errors.show_location" />
                        <label class="ea-sharing-option"><input v-model="form.show_progress" type="checkbox" /><span><strong>Planning progress</strong><small>Completed and total task counts only. Task names, notes, dates, and assignees stay private.</small></span></label>
                        <InputError :message="form.errors.show_progress" />
                        <label class="ea-sharing-option"><input v-model="form.show_vendors" type="checkbox" /><span><strong>Booked vendors</strong><small>Names and categories of booked vendors only, including future bookings. Contacts, quotes, and notes stay private.</small></span></label>
                        <InputError :message="form.errors.show_vendors" />
                        <label class="ea-sharing-option"><input v-model="form.show_budget" type="checkbox" /><span><strong>Budget summary</strong><small>Opt in to share your currency, target, and forecast total. Expense details and payment records stay private.</small></span></label>
                        <InputError :message="form.errors.show_budget" />
                    </fieldset>
                    <div class="ea-form-field"><label for="public-note">A note for your guests <span>optional</span></label><textarea id="public-note" v-model="form.public_note" rows="4" maxlength="2000" :aria-invalid="Boolean(form.errors.public_note)" aria-describedby="public-note-help public-note-error" /><p id="public-note-help" class="ea-sharing-help">Only this note is shared. Internal workspace notes stay private.</p><InputError id="public-note-error" :message="form.errors.public_note" /></div>
                    <div class="ea-sharing-actions"><button class="ea-button" :disabled="form.processing || publication.processing">{{ form.processing ? 'Saving…' : 'Save sharing choices' }}</button><span v-if="form.isDirty" class="ea-sharing-help" role="status">Unsaved changes</span></div>
                </form>
            </section>
            <p class="ea-sharing-help ea-sharing-boundary">A shared link does not grant workspace access. Invite collaborators through People &amp; access instead.</p>
        </template>
    </EventLayout>
</template>
