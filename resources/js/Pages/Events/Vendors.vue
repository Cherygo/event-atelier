<script setup>
import { Head, Link, useForm, useRemember, router } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';
import { formatQuote } from '@/vendorFormat';
import EventLayout from '@/Layouts/EventLayout.vue';
import EventVendorEditor from '@/Components/EventVendorEditor.vue';
const props = defineProps({ event: Object, vendors: Object, categories: Array, filters: Object, canEdit: Boolean, statuses: Object, currencies: Array });
const selected = useRemember([], 'vendor-comparison-' + props.event.id);
const selectionMessage = ref('');
const comparing = ref(false);
const toggleSelection = vendor => {
    selectionMessage.value = '';
    if (selected.value.some(item => item.id === vendor.id)) selected.value = selected.value.filter(item => item.id !== vendor.id);
    else if (selected.value.length < 4) selected.value.push({ id: vendor.id, name: vendor.name });
    else selectionMessage.value = 'Compare up to four vendors. Remove one before adding another.';
};
const compare = () => {
    comparing.value = true;
    router.get(route('events.vendors.compare', props.event.id), { vendors: selected.value.map(v => v.id) }, { onFinish: () => { comparing.value = false; } });
};
const adding = ref(false);
const editing = ref(null);
const deleting = ref(null);
const addButton = ref(null);
const heading = ref(null);
const removal = useForm({});
const search = useForm({ search: props.filters.search, category: props.filters.category, status: props.filters.status });
watch(() => props.filters, filters => { search.search = filters.search; search.category = filters.category; search.status = filters.status; });
const applyFilters = () => search.get(route('events.vendors.index', props.event.id), { preserveState: true, preserveScroll: true, replace: true });
const openEditor = async (vendor = null) => {
    adding.value = !vendor; editing.value = vendor?.id ?? null; deleting.value = null;
    await nextTick(); document.getElementById('vendor-' + (vendor?.id ?? 'new') + '-name')?.focus();
};
const closeEditor = async () => {
    const previous = editing.value;
    adding.value = false; editing.value = null;
    await nextTick();
    (previous ? document.getElementById('edit-vendor-' + previous) : addButton.value)?.focus();
};
const remove = vendor => removal.delete(route('events.vendors.destroy', [props.event.id, vendor.id]), {
    preserveScroll: true, onSuccess: async () => { deleting.value = null; selected.value = selected.value.filter(item => item.id !== vendor.id); await nextTick(); heading.value?.focus(); },
});
</script>

<template>
    <EventLayout :event="event">
        <Head :title="'Vendors · ' + event.name" />
        <template #description><p>Bring the right people into your plan.</p></template>
        <section class="ea-event-section ea-vendors" aria-labelledby="vendors-heading">
            <div class="ea-task-toolbar">
                <div><h2 id="vendors-heading" ref="heading" tabindex="-1">Vendors <span class="ea-section-count">{{ vendors.total }}</span></h2><p class="ea-vendor-intro">{{ canEdit ? 'Keep contacts and decisions together, from first conversation to the final choice.' : 'You have read-only access. Browse the vendors your team is considering.' }}</p></div>
                <button v-if="canEdit && !adding" ref="addButton" class="ea-button" type="button" @click="openEditor()">Add vendor</button>
            </div>
            <EventVendorEditor v-if="adding" :event-id="event.id" :categories="categories" :statuses="statuses" :currencies="currencies" @saved="closeEditor" @cancel="closeEditor" />
            <form class="ea-task-search" role="search" @submit.prevent="applyFilters">
                <label class="sr-only" for="vendor-search">Search vendors</label>
                <input id="vendor-search" v-model="search.search" type="search" maxlength="180" placeholder="Search vendors" />
                <label class="ea-task-filter-field">Category<select v-model="search.category"><option value="">All categories</option><option v-for="category in categories" :key="category" :value="category">{{ category }}</option></select></label>
                <label class="ea-task-filter-field">Status<select v-model="search.status"><option value="all">All statuses</option><option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option></select></label>
                <button class="ea-button ea-button-outline" :disabled="search.processing">Search</button>
                <Link v-if="filters.search || filters.category || filters.status !== 'all'" :href="route('events.vendors.index', event.id)" class="ea-text-link">Clear filters</Link>
            </form>
            <p v-if="vendors.total > 1" class="ea-vendor-muted">Select two to four vendors to compare their quotes and details.</p>
            <div v-if="selected.length" class="ea-vendor-compare-bar">
                <div><strong aria-live="polite">{{ selected.length }} of 4 selected</strong><p>Select two to four vendors to compare.</p></div>
                <button class="ea-button" :disabled="selected.length < 2 || comparing" @click="compare">{{ comparing ? 'Opening…' : 'Compare vendors' }}</button>
                <button v-if="selected.length" class="ea-text-link" @click="selected = []; selectionMessage = ''">Clear selection</button>
                <details class="ea-vendor-selection-details"><summary>Selected vendors</summary><ul class="ea-vendor-selected"><li v-for="item in selected" :key="item.id"><button :aria-label="'Remove ' + item.name + ' from comparison'" @click="toggleSelection(item)">{{ item.name }} <span aria-hidden="true">×</span></button></li></ul></details>
                <p v-if="selectionMessage" role="status">{{ selectionMessage }}</p>
            </div>
            <ul v-if="vendors.data.length" class="ea-vendor-list">
                <li v-for="vendor in vendors.data" :key="vendor.id" class="ea-vendor-record">
                    <div class="ea-vendor-record-heading">
                        <div><span class="ea-live-task-category">{{ vendor.category }}</span><h3>{{ vendor.name }}</h3><label class="ea-vendor-select"><input type="checkbox" :checked="selected.some(item => item.id === vendor.id)" :disabled="selected.length >= 4 && !selected.some(item => item.id === vendor.id)" @change="toggleSelection(vendor)" /> Compare<span class="sr-only"> {{ vendor.name }}</span></label></div>
                        <div v-if="canEdit && editing !== vendor.id" class="ea-live-task-actions">
                            <button :id="'edit-vendor-' + vendor.id" class="ea-text-link" :aria-label="'Edit ' + vendor.name" @click="openEditor(vendor)">Edit</button>
                            <button class="ea-text-link" :aria-label="'Delete ' + vendor.name" @click="deleting = vendor.id">Delete</button>
                        </div>
                    </div>
                    <EventVendorEditor v-if="editing === vendor.id" :vendor="vendor" :event-id="event.id" :categories="categories" :statuses="statuses" :currencies="currencies" @saved="closeEditor" @cancel="closeEditor" />
                    <template v-else>
                        <div class="ea-vendor-decision"><span class="ea-vendor-status" :class="'is-' + vendor.status">{{ statuses[vendor.status] }}</span><strong>{{ formatQuote(vendor) }}</strong></div>
                        <details v-if="vendor.quote_details" class="ea-task-notes ea-vendor-quote-details"><summary>Quote details</summary><p>{{ vendor.quote_details }}</p></details>
                        <dl v-if="vendor.contact_name || vendor.email || vendor.phone || vendor.website" class="ea-vendor-contact">
                            <div v-if="vendor.contact_name"><dt>Contact</dt><dd>{{ vendor.contact_name }}</dd></div>
                            <div v-if="vendor.email"><dt>Email</dt><dd><a :href="'mailto:' + vendor.email">{{ vendor.email }}</a></dd></div>
                            <div v-if="vendor.phone"><dt>Phone</dt><dd>{{ vendor.phone }}</dd></div>
                            <div v-if="vendor.website"><dt>Website</dt><dd><a :href="vendor.website" target="_blank" rel="noopener noreferrer">Visit website<span class="sr-only"> for {{ vendor.name }} (opens a new tab)</span></a></dd></div>
                        </dl>
                        <p v-else class="ea-vendor-muted">No contact details added.</p>
                        <details v-if="vendor.notes" class="ea-task-notes"><summary>Planning notes</summary><p>{{ vendor.notes }}</p></details>
                    </template>
                    <div v-if="deleting === vendor.id" class="ea-task-confirm" role="group" :aria-label="'Delete ' + vendor.name">
                        <p>Delete {{ vendor.name }} from this event? This cannot be undone.</p>
                        <button class="ea-button ea-button-outline" :disabled="removal.processing" @click="remove(vendor)">{{ removal.processing ? 'Deleting…' : 'Delete vendor' }}</button>
                        <button class="ea-text-link" :disabled="removal.processing" @click="deleting = null">Cancel</button>
                    </div>
                </li>
            </ul>
            <div v-else class="ea-task-empty">
                <h3>{{ filters.search || filters.category || filters.status !== 'all' ? 'No vendors match this view.' : 'A good event starts with the right people.' }}</h3>
                <p>{{ filters.search || filters.category || filters.status !== 'all' ? 'Try another search or clear the filters.' : canEdit ? 'Add a venue, caterer, or another partner you’re considering. Only your event team can see these details.' : 'Your team hasn’t added vendors yet. They’ll appear here when the planning begins.' }}</p>
            </div>
            <nav v-if="vendors.last_page > 1" class="ea-task-pagination" aria-label="Vendor pages">
                <Link v-if="vendors.prev_page_url" :href="vendors.prev_page_url" preserve-scroll class="ea-text-link">Previous</Link>
                <span>Page {{ vendors.current_page }} of {{ vendors.last_page }}</span>
                <Link v-if="vendors.next_page_url" :href="vendors.next_page_url" preserve-scroll class="ea-text-link">Next</Link>
            </nav>
            <div v-if="selected.length >= 2" class="ea-vendor-compare-footer">
                <button class="ea-button" :disabled="comparing" @click="compare">{{ comparing ? 'Opening…' : 'Compare vendors' }}</button>
                <span>{{ selected.length }} vendors selected</span>
            </div>
        </section>
    </EventLayout>
</template>
