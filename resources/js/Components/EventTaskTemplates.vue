<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ event: Object, templates: Array, existingKeys: Array, today: String });
const emit = defineEmits(['saved', 'cancel']);
const errors = ref(null);
const form = useForm({ template: props.templates.find(template => template.event_types.includes(props.event.type))?.key ?? props.templates[0].key, items: [], include_due_dates: false });
const selectedTemplate = computed(() => props.templates.find(template => template.key === form.template));
const available = computed(() => selectedTemplate.value.items.filter(item => !props.existingKeys.includes(item.key)));
watch(() => form.template, () => { form.items = []; form.clearErrors(); });
watch(() => props.existingKeys, keys => { form.items = form.items.filter(key => !keys.includes(key)); });
const formatDate = date => new Date(`${date}T12:00:00`).toLocaleDateString('en', { day: 'numeric', month: 'short', year: 'numeric' });
const importTasks = () => form.transform(data => ({ ...data, event_date: props.event.event_date, preview_today: props.today })).post(route('events.task-templates.store', props.event.id), {
    preserveScroll: true,
    onSuccess: () => emit('saved'),
    onError: async () => { await nextTick(); errors.value?.focus(); },
});
</script>

<template>
    <form class="ea-template-panel" aria-labelledby="template-heading" @submit.prevent="importTasks">
        <div class="ea-task-toolbar"><h3 id="template-heading">A starting point, made yours</h3><button type="button" class="ea-text-link" :disabled="form.processing" @click="emit('cancel')">Close templates</button></div>
        <p>Choose the tasks that fit. They’ll be added as unassigned, editable tasks—not a replacement for your plan.</p>
        <div class="ea-form-field ea-template-picker"><label for="task-template">Starter checklist</label><select id="task-template" v-model="form.template" :disabled="form.processing" :aria-invalid="Boolean(form.errors.template)" aria-describedby="template-description template-error"><option v-for="template in templates" :key="template.key" :value="template.key">{{ template.name }}</option></select><p id="template-description">{{ selectedTemplate.description }} You can use any checklist, regardless of your event type.</p><InputError id="template-error" :message="form.errors.template" /></div>
        <div class="ea-template-dates">
            <label><input v-model="form.include_due_dates" type="checkbox" :disabled="!event.event_date || form.processing" aria-describedby="template-dates-help" /><strong>Use suggested due dates</strong></label>
            <p id="template-dates-help">{{ event.event_date ? `Based on your event date: ${formatDate(event.event_date)}. Suggestions that have already passed stay unscheduled.` : 'Set an event date in Event details to enable suggestions. You can still add tasks without dates.' }} Imported dates are editable and won’t move automatically if the event date changes.</p>
        </div>
        <div class="ea-template-selection"><span role="status">{{ form.items.length }} selected · {{ available.length }} available</span><div><button type="button" class="ea-text-link" :disabled="form.processing || !available.length" @click="form.items = available.map(item => item.key)">Select available</button><button type="button" class="ea-text-link" :disabled="form.processing || !form.items.length" @click="form.items = []">Clear selection</button></div></div>
        <fieldset class="ea-template-list" :disabled="form.processing" aria-describedby="template-items-errors">
            <legend class="sr-only">Tasks to add from {{ selectedTemplate.name }}</legend>
            <div v-for="item in selectedTemplate.items" :key="item.key" class="ea-template-row">
                <label><input v-model="form.items" type="checkbox" :value="item.key" :disabled="existingKeys.includes(item.key)" /><span><strong>{{ item.title }}</strong><small>{{ item.category }}<span v-if="existingKeys.includes(item.key)"> · Already added</span></small></span></label>
                <p v-if="form.include_due_dates && !existingKeys.includes(item.key)" class="ea-template-due">{{ item.due_date ? `Suggested: ${formatDate(item.due_date)} · ${item.days_before} days before` : 'Suggested timing has passed — no due date will be set' }}</p>
                <details><summary>Task notes<span class="sr-only"> for {{ item.title }}</span></summary><p>{{ item.notes }}</p></details>
            </div>
        </fieldset>
        <div id="template-items-errors" ref="errors" tabindex="-1" role="alert"><InputError v-for="(message, key) in form.errors" :key="key" :message="message" /></div>
        <Link v-if="form.errors.include_due_dates" :href="route('events.tasks.index', event.id)" class="ea-text-link">Reload suggestions</Link>
        <p class="ea-template-help">Already-added tasks are skipped, even if renamed or completed. Deleting an imported task makes it available again. Similar tasks you created manually are kept.</p>
        <div class="ea-task-editor-actions"><button class="ea-button" :disabled="form.processing || !form.items.length">{{ form.processing ? 'Adding tasks…' : `Add ${form.items.length} selected ${form.items.length === 1 ? 'task' : 'tasks'}` }}</button><button type="button" class="ea-text-link" :disabled="form.processing" @click="emit('cancel')">Keep my plan as it is</button></div>
    </form>
</template>
