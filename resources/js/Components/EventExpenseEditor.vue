<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { amountInput } from '@/budgetFormat';

const props = defineProps({ event: Object, expense: { type: Object, default: null }, categories: Array });
const emit = defineEmits(['saved', 'cancel']);
const element = ref(null);
const prefix = 'expense-' + (props.expense?.id ?? 'new');
const form = useForm({
    title: props.expense?.title ?? '',
    category: props.expense?.category ?? '',
    estimate: amountInput(props.expense?.estimated_minor),
    currency: props.event.budget_currency,
    notes: props.expense?.notes ?? '',
});
const save = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => emit('saved'),
        onError: async () => { await nextTick(); element.value?.querySelector('[aria-invalid="true"]')?.focus(); },
    };
    if (props.expense) form.patch(route('events.expenses.update', [props.event.id, props.expense.id]), options);
    else form.post(route('events.expenses.store', props.event.id), options);
};
</script>

<template>
    <form ref="element" class="ea-task-editor ea-expense-editor" @submit.prevent="save">
        <h3 class="ea-form-field-wide">{{ expense ? 'Edit expense' : 'Add an expense' }}</h3>
        <div class="ea-form-field">
            <label :for="prefix + '-title'">Expense name</label>
            <input :id="prefix + '-title'" v-model="form.title" required maxlength="180" placeholder="e.g. Venue hire" :aria-invalid="Boolean(form.errors.title)" :aria-describedby="prefix + '-title-error'" />
            <InputError :id="prefix + '-title-error'" :message="form.errors.title" />
        </div>
        <div class="ea-form-field">
            <label :for="prefix + '-category'">Category</label>
            <input :id="prefix + '-category'" v-model="form.category" required maxlength="60" :list="prefix + '-categories'" placeholder="e.g. Venue, catering, production" :aria-invalid="Boolean(form.errors.category)" :aria-describedby="prefix + '-category-error'" />
            <datalist :id="prefix + '-categories'"><option v-for="category in categories" :key="category" :value="category" /></datalist>
            <InputError :id="prefix + '-category-error'" :message="form.errors.category" />
        </div>
        <div class="ea-form-field">
            <label :for="prefix + '-estimate'">Estimated cost · {{ event.budget_currency }}</label>
            <input :id="prefix + '-estimate'" v-model="form.estimate" type="number" inputmode="decimal" min="0" max="999999999.99" step="0.01" required :aria-invalid="Boolean(form.errors.estimate)" :aria-describedby="prefix + '-estimate-error'" />
            <InputError :id="prefix + '-estimate-error'" :message="form.errors.estimate" />
        </div>
        <div class="ea-form-field ea-form-field-wide">
            <label :for="prefix + '-notes'">Notes <span>Optional</span></label>
            <textarea :id="prefix + '-notes'" v-model="form.notes" rows="2" maxlength="5000" placeholder="What does this cover? Include taxes and agreed terms." :aria-invalid="Boolean(form.errors.notes)" :aria-describedby="prefix + '-notes-error'" />
            <InputError :id="prefix + '-notes-error'" :message="form.errors.notes" />
        </div>
        <InputError class="ea-form-field-wide" :message="form.errors.currency" role="alert" />
        <div class="ea-task-editor-actions ea-form-field-wide">
            <button class="ea-button" :disabled="form.processing">{{ form.processing ? 'Saving…' : expense ? 'Save expense' : 'Add expense' }}</button>
            <button type="button" class="ea-text-link" :disabled="form.processing" @click="emit('cancel')">Cancel</button>
        </div>
    </form>
</template>
