<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AtelierIcon from '@/Components/AtelierIcon.vue';

const types = [
    ['wedding', 'Wedding', 'A ceremony, celebration, and all the details between.'],
    ['corporate', 'Corporate event', 'A gathering for colleagues, clients, or community.'],
    ['private', 'Private gathering', 'A dinner, milestone, or occasion at home.'],
    ['conference', 'Conference', 'A programme built around people and ideas.'],
    ['custom', 'Something else', 'A plan that does not need a predefined shape.'],
];

const form = useForm({ name: '', type: '', event_date: '', guest_count: '', location: '' });
const formElement = ref(null);
const submit = () => form.post(route('events.store'), {
    onError: async () => {
        await nextTick();
        formElement.value?.querySelector('[aria-invalid="true"]')?.focus();
    },
});
</script>

<template>
    <Head title="Plan an event" />

    <AuthenticatedLayout>
        <div class="ea-create-page">
            <Link :href="route('events.index')" class="ea-back-link"><AtelierIcon name="back" /> My events</Link>
            <div class="ea-create-grid">
                <header class="ea-create-intro">
                    <h1>Begin with the occasion.</h1>
                    <p>Only the essentials for now. You can build out the planning details from inside this workspace as the product grows.</p>
                    <div class="ea-create-arch" aria-hidden="true"><span>ea</span></div>
                </header>

                <form ref="formElement" class="ea-event-form" @submit.prevent="submit">
                    <div class="ea-form-field ea-form-field-wide">
                        <label for="name">What are you planning?</label>
                        <input id="name" v-model="form.name" type="text" maxlength="120" autocomplete="off" placeholder="e.g. Maya &amp; Adrian’s weekend" autofocus required :aria-invalid="Boolean(form.errors.name)" :aria-describedby="form.errors.name ? 'name-error' : undefined" />
                        <p v-if="form.errors.name" id="name-error" class="ea-form-error">{{ form.errors.name }}</p>
                    </div>
                    <fieldset class="ea-form-field ea-form-field-wide">
                        <legend>What kind of occasion is it?</legend>
                        <div class="ea-type-choice-grid">
                            <label v-for="[value, label, description] in types" :key="value" class="ea-type-choice" :class="{ selected: form.type === value }">
                                <input v-model="form.type" type="radio" name="type" :value="value" required :aria-invalid="Boolean(form.errors.type)" :aria-describedby="form.errors.type ? 'type-error' : undefined" />
                                <span>{{ label }}</span><small>{{ description }}</small>
                            </label>
                        </div>
                        <p v-if="form.errors.type" id="type-error" class="ea-form-error">{{ form.errors.type }}</p>
                    </fieldset>
                    <div class="ea-form-field"><label for="event_date">When is it? <span>Optional</span></label><input id="event_date" v-model="form.event_date" type="date" :aria-invalid="Boolean(form.errors.event_date)" :aria-describedby="form.errors.event_date ? 'event-date-error' : undefined" /><p v-if="form.errors.event_date" id="event-date-error" class="ea-form-error">{{ form.errors.event_date }}</p></div>
                    <div class="ea-form-field"><label for="guest_count">How many people? <span>Optional</span></label><input id="guest_count" v-model="form.guest_count" type="number" min="1" max="100000" inputmode="numeric" placeholder="e.g. 80" :aria-invalid="Boolean(form.errors.guest_count)" :aria-describedby="form.errors.guest_count ? 'guest-count-error' : undefined" /><p v-if="form.errors.guest_count" id="guest-count-error" class="ea-form-error">{{ form.errors.guest_count }}</p></div>
                    <div class="ea-form-field ea-form-field-wide"><label for="location">Where will it happen? <span>Optional</span></label><input id="location" v-model="form.location" type="text" maxlength="120" autocomplete="off" placeholder="City, venue, or ‘still deciding’" :aria-invalid="Boolean(form.errors.location)" :aria-describedby="form.errors.location ? 'location-error' : undefined" /><p v-if="form.errors.location" id="location-error" class="ea-form-error">{{ form.errors.location }}</p></div>
                    <div class="ea-form-actions ea-form-field-wide"><p>Your workspace is private to you until you invite someone.</p><button type="submit" class="ea-button" :disabled="form.processing"><span>{{ form.processing ? 'Creating your workspace…' : 'Create workspace' }}</span><AtelierIcon name="arrow" /></button></div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
