<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
const props = defineProps({ eventId: Number, vendor: { type: Object, default: null }, categories: Array });
const emit = defineEmits(['saved', 'cancel']);
const element = ref(null);
const prefix = 'vendor-' + (props.vendor?.id ?? 'new');
const fields = [
    { key: 'name', label: 'Vendor name', max: 180, required: true, placeholder: 'e.g. Garden House' },
    { key: 'category', label: 'Category', max: 60, required: true, placeholder: 'e.g. Venue, catering, production' },
    { key: 'contact_name', label: 'Contact person', max: 120 },
    { key: 'email', label: 'Email', type: 'email', max: 255 },
    { key: 'phone', label: 'Phone', type: 'tel', max: 60 },
    { key: 'website', label: 'Website', type: 'url', max: 2048, placeholder: 'https://' },
];
const form = useForm(Object.fromEntries([...fields.map(({ key }) => [key, props.vendor?.[key] ?? '']), ['notes', props.vendor?.notes ?? '']]));
const save = () => {
    const options = { preserveScroll: true, onSuccess: () => emit('saved'), onError: async () => { await nextTick(); element.value?.querySelector('[aria-invalid="true"]')?.focus(); } };
    if (props.vendor) form.patch(route('events.vendors.update', [props.eventId, props.vendor.id]), options);
    else form.post(route('events.vendors.store', props.eventId), options);
};
</script>

<template>
    <form ref="element" class="ea-task-editor ea-vendor-editor" @submit.prevent="save">
        <h3 class="ea-form-field-wide">{{ vendor ? 'Edit vendor' : 'Add a vendor' }}</h3>
        <div v-for="field in fields" :key="field.key" class="ea-form-field">
            <label :for="prefix + '-' + field.key">{{ field.label }} <span v-if="!field.required">Optional</span></label>
            <input :id="prefix + '-' + field.key" v-model="form[field.key]" :type="field.type ?? 'text'" :maxlength="field.max" :required="field.required" :placeholder="field.placeholder" :list="field.key === 'category' ? prefix + '-categories' : undefined" :aria-invalid="Boolean(form.errors[field.key])" :aria-describedby="prefix + '-' + field.key + '-error'" />
            <InputError :id="prefix + '-' + field.key + '-error'" :message="form.errors[field.key]" />
        </div>
        <datalist :id="prefix + '-categories'"><option v-for="category in categories" :key="category" :value="category" /></datalist>
        <div class="ea-form-field ea-form-field-wide">
            <label :for="prefix + '-notes'">Notes <span>Optional</span></label>
            <textarea :id="prefix + '-notes'" v-model="form.notes" rows="3" maxlength="5000" placeholder="What matters when choosing this vendor?" :aria-invalid="Boolean(form.errors.notes)" :aria-describedby="prefix + '-notes-error'" />
            <InputError :id="prefix + '-notes-error'" :message="form.errors.notes" />
        </div>
        <div class="ea-task-editor-actions ea-form-field-wide">
            <button class="ea-button" :disabled="form.processing">{{ form.processing ? 'Saving…' : vendor ? 'Save vendor' : 'Add vendor' }}</button>
            <button type="button" class="ea-text-link" :disabled="form.processing" @click="emit('cancel')">Cancel</button>
        </div>
    </form>
</template>
