<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AtelierIcon from '@/Components/AtelierIcon.vue';

defineProps({ event: { type: Object, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <div class="ea-events-page ea-event-detail">
            <Link :href="route('events.index')" class="ea-back-link"><AtelierIcon name="back" /> My events</Link>
            <header class="ea-event-title"><h1>{{ event.name }}</h1><slot name="description" /></header>
            <nav class="ea-event-tabs" aria-label="Event navigation">
                <Link :href="route('events.overview', event.id)" :aria-current="route().current('events.overview') ? 'page' : undefined">Overview</Link>
                <Link :href="route('events.tasks.index', event.id)" :aria-current="route().current('events.tasks.*') ? 'page' : undefined">Tasks</Link>
                <Link :href="route('events.show', event.id)" :aria-current="route().current('events.show') ? 'page' : undefined">Event details</Link>
                <Link :href="route('events.people', event.id)" :aria-current="route().current('events.people') ? 'page' : undefined">People &amp; access</Link>
            </nav>
            <slot />
        </div>
    </AuthenticatedLayout>
</template>
