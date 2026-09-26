<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { amountInput, formatBudgetAmount } from '@/budgetFormat';

const props = defineProps({ event: Object, expense: Object, canEdit: Boolean, today: String });
const money = amount => formatBudgetAmount(amount, props.event.budget_currency);
const prefix = 'payment-' + props.expense.id;
const form = useForm({ amount: '', currency: props.event.budget_currency, paid_on: props.today, note: '' });
const removal = useForm({});
const removing = ref(null);
const element = ref(null);
const summary = ref(null);
const record = () => form.post(route('events.expenses.payments.store', [props.event.id, props.expense.id]), {
    preserveScroll: true,
    onSuccess: async () => { form.reset('amount', 'note'); await nextTick(); summary.value?.focus(); },
    onError: async () => { await nextTick(); element.value?.querySelector('[aria-invalid="true"]')?.focus(); },
});
const remove = payment => removal.delete(route('events.expenses.payments.destroy', [props.event.id, props.expense.id, payment.id]), {
    preserveScroll: true,
    onSuccess: async () => { removing.value = null; await nextTick(); summary.value?.focus(); },
});
</script>

<template>
    <details class="ea-expense-payments">
        <summary ref="summary">Payments <span>{{ expense.payments.length }} recorded</span></summary>
        <div class="ea-payment-detail">
            <dl class="ea-payment-balance"><div><dt>Paid</dt><dd>{{ money(expense.paid_minor) }}</dd></div><div><dt>Outstanding</dt><dd>{{ expense.outstanding_minor === null ? 'Actual cost needed' : money(expense.outstanding_minor) }}</dd></div></dl>
            <p class="ea-budget-caption">This is a record of payments made elsewhere. No money is sent here.</p>
            <TransitionGroup v-if="expense.payments.length" name="ea-payment" tag="ul" class="ea-payment-history" aria-label="Recorded payments">
                <li v-for="payment in expense.payments" :key="payment.id">
                    <div class="ea-payment-history-row"><time :datetime="payment.paid_on">{{ new Intl.DateTimeFormat(undefined, { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(payment.paid_on + 'T12:00:00')) }}</time><strong>{{ money(payment.amount_minor) }}</strong><button v-if="canEdit" class="ea-text-link ea-danger-link" :aria-label="'Remove payment of ' + money(payment.amount_minor) + ' on ' + payment.paid_on" @click="removing = payment.id">Remove record</button></div>
                    <p v-if="payment.note" class="ea-payment-note">{{ payment.note }}</p>
                    <div v-if="removing === payment.id" class="ea-task-confirm" role="group" aria-label="Remove payment record"><p>Remove this payment record? The outstanding balance will increase. No refund is issued.</p><button class="ea-button ea-button-outline" :disabled="removal.processing" @click="remove(payment)">{{ removal.processing ? 'Removing…' : 'Remove payment record' }}</button><button class="ea-text-link" :disabled="removal.processing" @click="removing = null">Keep record</button><InputError :message="removal.errors.payment" /></div>
                </li>
            </TransitionGroup>
            <p v-else class="ea-budget-caption">No payments recorded yet.</p>
            <form v-if="canEdit && expense.outstanding_minor > 0" ref="element" class="ea-task-editor ea-payment-editor" @submit.prevent="record">
                <h4 class="ea-form-field-wide">Record a payment</h4>
                <div class="ea-form-field"><label :for="prefix + '-amount'">Amount · {{ event.budget_currency }}</label><input :id="prefix + '-amount'" v-model="form.amount" type="number" inputmode="decimal" min="0.01" :max="amountInput(expense.outstanding_minor)" step="0.01" required :aria-invalid="Boolean(form.errors.amount)" :aria-describedby="prefix + '-amount-error'" /><InputError :id="prefix + '-amount-error'" :message="form.errors.amount" /></div>
                <div class="ea-form-field"><label :for="prefix + '-date'">Date paid</label><input :id="prefix + '-date'" v-model="form.paid_on" type="date" :max="today" required :aria-invalid="Boolean(form.errors.paid_on)" :aria-describedby="prefix + '-date-error'" /><InputError :id="prefix + '-date-error'" :message="form.errors.paid_on" /></div>
                <div class="ea-form-field ea-form-field-wide"><label :for="prefix + '-note'">Payment note <span>Optional</span></label><input :id="prefix + '-note'" v-model="form.note" maxlength="500" placeholder="e.g. Venue deposit, bank transfer" :aria-invalid="Boolean(form.errors.note)" :aria-describedby="prefix + '-note-error'" /><InputError :id="prefix + '-note-error'" :message="form.errors.note" /></div>
                <InputError class="ea-form-field-wide" :message="form.errors.currency" role="alert" />
                <div class="ea-task-editor-actions ea-form-field-wide"><button class="ea-button" :disabled="form.processing">{{ form.processing ? 'Recording…' : 'Record payment' }}</button></div>
            </form>
            <p v-else-if="canEdit && expense.actual_minor === null" class="ea-budget-caption">Edit this expense and set its actual cost before recording a payment.</p>
        </div>
    </details>
</template>
