<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ eventId: Number, task: { type: Object, default: null } });
const emit = defineEmits(['saved', 'cancel']);
const prefix = 'task-' + (props.task?.id ?? 'new');
const element = ref(null);
const form = useForm({ title: props.task?.title ?? '', category: props.task?.category ?? '', notes: props.task?.notes ?? '' });
const save = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => emit('saved'),
        onError: async () => { await nextTick(); element.value?.querySelector('[aria-invalid="true"]')?.focus(); },
    };
    if (props.task) form.patch(route('events.tasks.update', [props.eventId, props.task.id]), options);
    else form.post(route('events.tasks.store', props.eventId), options);
};
</script>

<template>
    <form ref="element" class="ea-task-editor" @submit.prevent="save">
        <div class="ea-form-field ea-form-field-wide">
            <label :for="prefix + '-title'">Task name</label>
            <input :id="prefix + '-title'" v-model="form.title" required maxlength="180" placeholder="e.g. Arrange a venue visit" :aria-invalid="Boolean(form.errors.title)" :aria-describedby="prefix + '-title-error'" />
            <InputError :id="prefix + '-title-error'" :message="form.errors.title" />
        </div>
        <div class="ea-form-field ea-form-field-wide">
            <label :for="prefix + '-category'">Category <span>Optional</span></label>
            <input :id="prefix + '-category'" v-model="form.category" maxlength="60" placeholder="e.g. Venue, catering, programme" :aria-invalid="Boolean(form.errors.category)" :aria-describedby="prefix + '-category-error'" />
            <InputError :id="prefix + '-category-error'" :message="form.errors.category" />
        </div>
        <div class="ea-form-field ea-form-field-wide">
            <label :for="prefix + '-notes'">Notes <span>Optional</span></label>
            <textarea :id="prefix + '-notes'" v-model="form.notes" rows="3" maxlength="5000" :aria-invalid="Boolean(form.errors.notes)" :aria-describedby="prefix + '-notes-error'" />
            <InputError :id="prefix + '-notes-error'" :message="form.errors.notes" />
        </div>
        <div class="ea-task-editor-actions ea-form-field-wide">
            <button class="ea-button" :disabled="form.processing">{{ form.processing ? 'Saving…' : task ? 'Save task' : 'Add task' }}</button>
            <button type="button" class="ea-text-link" :disabled="form.processing" @click="emit('cancel')">Cancel</button>
        </div>
    </form>
</template>
