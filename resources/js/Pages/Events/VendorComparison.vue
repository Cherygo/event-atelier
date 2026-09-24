<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import EventLayout from '@/Layouts/EventLayout.vue';
import { formatQuote } from '@/vendorFormat';
const props = defineProps({ event: Object, vendors: Array, statuses: Object, canEdit: Boolean });
const mixedCurrencies = computed(() => new Set(props.vendors.filter(v => v.quote_amount !== null).map(v => v.currency)).size > 1);
const mixedCategories = computed(() => new Set(props.vendors.map(v => v.category)).size > 1);
const rows = [
    { key: 'quote_details', label: 'What’s included', empty: 'No quote details yet' },
    { key: 'contact_name', label: 'Contact person', empty: 'Not added' },
    { key: 'email', label: 'Email', empty: 'Not added' },
    { key: 'phone', label: 'Phone', empty: 'Not added' },
    { key: 'notes', label: 'Planning notes', empty: 'No notes yet' },
];
</script>

<template>
    <EventLayout :event="event">
        <Head :title="'Compare vendors · ' + event.name" />
        <template #description><p>See the differences. Make a considered choice.</p></template>
        <section class="ea-event-section ea-vendor-comparison" aria-labelledby="comparison-heading">
            <div class="ea-task-toolbar"><div><h2 id="comparison-heading">Compare vendors</h2><p class="ea-vendor-intro">A shared view of {{ vendors.length }} options. Check what each quote includes before deciding.</p></div><Link :href="route('events.vendors.index', event.id)" class="ea-text-link">Back to vendors</Link></div>
            <p v-if="mixedCurrencies" class="ea-comparison-notice">These quotes use different currencies. Amounts are shown as entered, without conversion or price ranking.</p>
            <p v-if="mixedCategories" class="ea-vendor-muted">You’re comparing different categories. Their services may not be directly equivalent.</p>
            <p id="comparison-scroll-help" class="ea-comparison-hint">Scroll sideways to see every option. Missing quotes are shown as “Not quoted”, not zero.</p>
            <div class="ea-comparison-scroll" role="region" aria-label="Vendor comparison table" aria-describedby="comparison-scroll-help" tabindex="0">
                <table class="ea-comparison-table">
                    <caption class="sr-only">Compare vendor prices, status, contact details, and planning notes</caption>
                    <thead><tr><th scope="col">Your options</th><th v-for="vendor in vendors" :key="vendor.id" scope="col"><span>{{ vendor.category }}</span><strong>{{ vendor.name }}</strong></th></tr></thead>
                    <tbody>
                        <tr class="ea-comparison-price"><th scope="row">Quoted total</th><td v-for="vendor in vendors" :key="vendor.id">{{ formatQuote(vendor) }}</td></tr>
                        <tr><th scope="row">Status</th><td v-for="vendor in vendors" :key="vendor.id"><span class="ea-vendor-status" :class="'is-' + vendor.status">{{ statuses[vendor.status] }}</span></td></tr>
                        <tr v-for="row in rows" :key="row.key"><th scope="row">{{ row.label }}</th><td v-for="vendor in vendors" :key="vendor.id" class="ea-comparison-text" :class="{ 'ea-vendor-muted': !vendor[row.key] }"><a v-if="row.key === 'email' && vendor.email" :href="'mailto:' + vendor.email">{{ vendor.email }}</a><template v-else>{{ vendor[row.key] || row.empty }}</template></td></tr>
                        <tr><th scope="row">Website</th><td v-for="vendor in vendors" :key="vendor.id"><a v-if="vendor.website" :href="vendor.website" target="_blank" rel="noopener noreferrer">Visit website<span class="sr-only"> for {{ vendor.name }} (opens a new tab)</span></a><span v-else class="ea-vendor-muted">Not added</span></td></tr>
                        <tr v-if="canEdit"><th scope="row">Next step</th><td v-for="vendor in vendors" :key="vendor.id"><Link :href="route('events.vendors.index', { event: event.id, search: vendor.name })" class="ea-text-link">Manage vendor<span class="sr-only"> {{ vendor.name }}</span></Link></td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </EventLayout>
</template>
