<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ member: { type: Object, required: true }, eventId: Number, roles: Array });
const form = useForm({ role: props.member.role });
const removal = useForm({});
const confirmingRemoval = ref(false);
const update = () => form.patch(route('events.members.update', [props.eventId, props.member.id]), { preserveScroll: true });
const remove = () => removal.delete(route('events.members.destroy', [props.eventId, props.member.id]), { preserveScroll: true });
const labels = { admin: 'Planner / admin', editor: 'Editor', viewer: 'Viewer' };
</script>

<template>
    <li class="ea-person-row">
        <div class="ea-person-identity"><strong>{{ member.name }}</strong><span v-if="member.email">{{ member.email }}</span></div>
        <div v-if="member.canManage" class="ea-person-controls">
            <form class="ea-role-form" @submit.prevent="update">
                <label class="sr-only" :for="'role-' + member.id">Role for {{ member.name }}</label>
                <select :id="'role-' + member.id" v-model="form.role" :aria-describedby="'role-error-' + member.id" :aria-invalid="Boolean(form.errors.role)"><option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option></select>
                <button class="ea-button ea-button-outline" :disabled="form.processing || removal.processing || form.role === member.role">Save role</button>
                <InputError :id="'role-error-' + member.id" :message="form.errors.role" />
            </form>
            <button v-if="!confirmingRemoval" type="button" class="ea-text-link" @click="confirmingRemoval = true">Remove access</button>
            <div v-else class="ea-inline-confirm">
                <span>Remove {{ member.name }} from this event?</span>
                <button type="button" class="ea-text-link" :disabled="removal.processing || form.processing" @click="remove">Yes, remove</button>
                <button type="button" class="ea-text-link" @click="confirmingRemoval = false">Cancel</button>
            </div>
        </div>
        <span v-else class="ea-access-label">{{ labels[member.role] }}</span>
    </li>
</template>
