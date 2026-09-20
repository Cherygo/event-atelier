<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, nextTick, watch } from 'vue';
import EventTaskEditor from '@/Components/EventTaskEditor.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ eventId: Number, task: Object, canEdit: Boolean });
const emit = defineEmits(['removed']);
const editing = ref(false);
const confirming = ref(false);
const editButton = ref(null);
const removal = useForm({});
const status = useForm({ status: props.task.status });
const statusLabels = { todo: 'To do', in_progress: 'In progress', completed: 'Completed' };
watch(() => props.task.status, (value) => { status.status = value; });
const updateStatus = () => status.patch(route('events.tasks.status', [props.eventId, props.task.id]), {
    preserveScroll: true,
    onSuccess: () => emit('removed'),
    onError: () => { status.status = props.task.status; },
});
const closeEditor = async () => { editing.value = false; await nextTick(); editButton.value?.focus(); };
const remove = () => removal.delete(route('events.tasks.destroy', [props.eventId, props.task.id]), { preserveScroll: true, onSuccess: () => emit('removed') });
</script>

<template>
    <li class="ea-live-task" :class="{ 'is-completed': task.status === 'completed' }">
        <div class="ea-live-task-top">
            <div class="ea-live-task-copy">
                <h3>{{ task.title }}</h3>
                <div class="ea-live-task-meta">
                    <label v-if="canEdit" class="ea-task-status-control">
                        <span class="sr-only">Status for {{ task.title }}</span>
                        <select v-model="status.status" :disabled="status.processing || removal.processing" @change="updateStatus"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select>
                    </label>
                    <span v-else class="ea-access-label">{{ statusLabels[task.status] }}</span>
                    <span v-if="status.processing" role="status" class="ea-task-saving">Saving…</span>
                    <InputError :message="status.errors.status" />
                </div>
                <span v-if="task.category" class="ea-live-task-category">{{ task.category }}</span>
                <details v-if="task.notes && !editing" class="ea-task-notes"><summary>Notes</summary><p>{{ task.notes }}</p></details>
            </div>
            <div v-if="canEdit && !editing" class="ea-live-task-actions">
                <button ref="editButton" type="button" class="ea-text-link" :aria-label="'Edit ' + task.title" @click="editing = true; confirming = false">Edit</button>
                <button type="button" class="ea-text-link" :aria-label="'Delete ' + task.title" @click="confirming = true">Delete</button>
            </div>
        </div>
        <EventTaskEditor v-if="editing" :task="task" :event-id="eventId" @saved="closeEditor" @cancel="closeEditor" />
        <div v-if="confirming" class="ea-task-confirm" role="group" :aria-label="'Delete ' + task.title">
            <p>Delete this task? This cannot be undone.</p>
            <button type="button" class="ea-button ea-button-outline" :disabled="removal.processing" @click="remove">{{ removal.processing ? 'Deleting…' : 'Delete task' }}</button>
            <button type="button" class="ea-text-link" :disabled="removal.processing" @click="confirming = false">Cancel</button>
        </div>
    </li>
</template>
