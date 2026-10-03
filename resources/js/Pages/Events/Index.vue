<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AtelierIcon from '@/Components/AtelierIcon.vue';
import { formatEventDate } from '@/dateFormat';

const props = defineProps({ events: { type: Array, required: true } });

const displayType = (type) => ({ wedding: 'Wedding', corporate: 'Corporate event', private: 'Private gathering', conference: 'Conference', custom: 'Custom occasion' }[type] ?? type);
const eventCountLabel = computed(() => props.events.length === 1 ? '1 event in progress' : `${props.events.length} events in progress`);
</script>

<template>
    <Head title="My events" />

    <AuthenticatedLayout>
        <div class="ea-events-page">
            <header class="ea-events-header">
                <div>
                    <h1>My events</h1>
                    <p>{{ eventCountLabel }}. Start with the occasion that needs a clear plan.</p>
                </div>
                <Link :href="route('events.create')" class="ea-button" prefetch><span>Plan an event</span><AtelierIcon name="plus" /></Link>
            </header>

            <section v-if="events.length" class="ea-event-list" aria-label="Your events">
                <article v-for="event in events" :key="event.id" class="ea-event-record">
                    <div class="ea-event-record-type">{{ displayType(event.type) }}</div>
                    <div class="ea-event-record-main">
                        <h2><Link :href="route('events.overview', event.id)" class="ea-event-name-link">{{ event.name }}</Link></h2>
                        <p><time v-if="event.event_date" :datetime="event.event_date.slice(0, 10)">{{ formatEventDate(event.event_date) }}</time><span v-if="event.event_date && event.location"> · </span><span v-if="event.location">{{ event.location }}</span><span v-if="!event.event_date && !event.location">Details can be added as your plan takes shape.</span></p>
                    </div>
                    <div class="ea-event-record-meta"><span v-if="event.guest_count">{{ event.guest_count }} guests</span><span v-else>Workspace setup</span></div>
                </article>
            </section>

            <section v-else class="ea-empty-events">
                <div class="ea-empty-events-arch" aria-hidden="true"><span>+</span></div>
                <div>
                    <h2>Make room for the occasion.</h2>
                    <p>Your workspace begins with a single event. Give it a name, then bring in the details when you are ready.</p>
                    <Link :href="route('events.create')" class="ea-text-link" prefetch>Begin your first plan <AtelierIcon name="arrow" /></Link>
                </div>
            </section>

            <aside class="ea-events-note"><AtelierIcon name="leaf" /><p>Each event has its own planning circle. Open an event to manage its details and access.</p></aside>
        </div>
    </AuthenticatedLayout>
</template>
