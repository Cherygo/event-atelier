<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';
import EventLayout from '@/Layouts/EventLayout.vue';
import EventExpenseEditor from '@/Components/EventExpenseEditor.vue';
import EventExpensePayments from '@/Components/EventExpensePayments.vue';
import InputError from '@/Components/InputError.vue';
import { amountInput, formatBudgetAmount } from '@/budgetFormat';

const props = defineProps({ event: Object, expenses: Object, budget: Object, categories: Array, filters: Object, currencies: Array, currencyLocked: Boolean, canEdit: Boolean, today: String });
const money = minor => formatBudgetAmount(minor, props.event.budget_currency);
const settings = useForm({ currency: props.event.budget_currency ?? '', target: amountInput(props.event.budget_target_minor) });
const settingsElement = ref(null);
const saveSettings = () => settings.patch(route('events.budget.update', props.event.id), {
    preserveScroll: true,
    onSuccess: () => { settingsElement.value.open = false; },
    onError: async () => { await nextTick(); settingsElement.value?.querySelector('[aria-invalid="true"]')?.focus(); },
});
watch(() => props.event, event => { settings.currency = event.budget_currency ?? ''; settings.target = amountInput(event.budget_target_minor); });
const search = useForm({ search: props.filters.search, category: props.filters.category });
watch(() => props.filters, filters => { search.search = filters.search; search.category = filters.category; });
const applyFilters = () => search.get(route('events.budget.index', props.event.id), { preserveState: true, preserveScroll: true, replace: true });
const adding = ref(false);
const editing = ref(null);
const deleting = ref(null);
const addButton = ref(null);
const heading = ref(null);
const removal = useForm({});
const openEditor = async (expense = null) => {
    adding.value = !expense;
    editing.value = expense?.id ?? null;
    deleting.value = null;
    await nextTick();
    document.getElementById('expense-' + (expense?.id ?? 'new') + '-title')?.focus();
};
const closeEditor = async () => {
    const previous = editing.value;
    adding.value = false;
    editing.value = null;
    await nextTick();
    (previous ? document.getElementById('edit-expense-' + previous) : addButton.value)?.focus();
};
const remove = expense => removal.delete(route('events.expenses.destroy', [props.event.id, expense.id]), {
    preserveScroll: true,
    onSuccess: async () => { deleting.value = null; await nextTick(); heading.value?.focus(); },
});
</script>

<template>
    <EventLayout :event="event">
        <Head :title="'Budget · ' + event.name" />
        <template #description><p>A clear view of what your occasion will cost.</p></template>
        <section class="ea-event-section ea-budget-ledger" aria-labelledby="budget-heading">
            <div class="ea-task-toolbar">
                <div><h2 id="budget-heading" ref="heading" tabindex="-1">Budget</h2><p>{{ canEdit ? 'Give each expense a place in the plan.' : 'You have read-only access to this event’s budget.' }}</p></div>
                <button v-if="canEdit && event.budget_currency && !adding" ref="addButton" class="ea-button" @click="openEditor()">Add expense</button>
            </div>
            <dl v-if="event.budget_currency" class="ea-budget-totals">
                <div><dt>Budget target</dt><dd>{{ money(event.budget_target_minor) }}</dd></div>
                <div><dt>Forecast total</dt><dd>{{ money(budget.totals.forecast_minor) }}</dd></div>
                <div :class="{ 'ea-budget-over': budget.totals.remaining_minor !== null && budget.totals.remaining_minor < 0 }"><dt>{{ budget.totals.remaining_minor !== null && budget.totals.remaining_minor < 0 ? 'Over target' : 'Left to allocate' }}</dt><dd>{{ money(budget.totals.remaining_minor === null ? null : Math.abs(budget.totals.remaining_minor)) }}</dd></div>
            </dl>
            <div v-if="event.budget_currency" class="ea-budget-context">
                <p>Forecast uses actual costs where confirmed and estimates for everything else.{{ budget.totals.unconfirmed_count ? ` ${budget.totals.unconfirmed_count} ${budget.totals.unconfirmed_count === 1 ? 'expense still needs its' : 'expenses still need their'} actual cost.` : '' }}</p>
                <p><strong>{{ money(budget.totals.paid_minor) }}</strong> recorded paid · <strong>{{ money(budget.totals.outstanding_minor) }}</strong> outstanding on confirmed costs<span v-if="budget.totals.overdue_count" class="ea-budget-over"> · {{ budget.totals.overdue_count }} overdue</span></p>
                <details v-if="budget.categories.length" class="ea-budget-breakdown">
                    <summary>Category breakdown and totals</summary>
                    <p id="budget-breakdown-help">Actual and outstanding totals include confirmed costs only. On narrow screens, scroll across the table.</p>
                    <div class="ea-budget-table-scroll" tabindex="0" role="region" aria-label="Budget by category" aria-describedby="budget-breakdown-help">
                        <table class="ea-budget-table">
                            <caption class="sr-only">Budget categories in {{ event.budget_currency }}</caption>
                            <thead><tr><th scope="col">Category</th><th scope="col">Estimated</th><th scope="col">Actual</th><th scope="col">Paid</th><th scope="col">Outstanding</th></tr></thead>
                            <tbody><tr v-for="category in budget.categories" :key="category.category"><th scope="row">{{ category.category }}<small v-if="category.unconfirmed_count">{{ category.unconfirmed_count }} unconfirmed</small></th><td>{{ money(category.estimated_minor) }}</td><td>{{ money(category.actual_minor) }}</td><td>{{ money(category.paid_minor) }}</td><td>{{ money(category.outstanding_minor) }}</td></tr></tbody>
                            <tfoot><tr><th scope="row">All expenses</th><td>{{ money(budget.totals.estimated_minor) }}</td><td>{{ money(budget.totals.actual_minor) }}</td><td>{{ money(budget.totals.paid_minor) }}</td><td>{{ money(budget.totals.outstanding_minor) }}</td></tr></tfoot>
                        </table>
                    </div>
                </details>
            </div>
            <details v-if="canEdit" ref="settingsElement" class="ea-budget-settings" :open="!event.budget_currency">
                <summary>{{ event.budget_currency ? 'Budget settings' : 'Set up your budget' }}</summary>
                <form class="ea-task-editor" @submit.prevent="saveSettings">
                    <div class="ea-form-field">
                        <label for="budget-currency">Currency</label>
                        <select id="budget-currency" v-model="settings.currency" required :disabled="currencyLocked" :aria-invalid="Boolean(settings.errors.currency)" aria-describedby="budget-currency-help budget-currency-error"><option value="">Choose currency</option><option v-for="currency in currencies" :key="currency" :value="currency">{{ currency }}</option></select>
                        <p id="budget-currency-help" class="ea-vendor-field-help">{{ currencyLocked ? 'Currency is locked while expenses exist. Amounts are never converted.' : 'All expenses use this currency. Vendor quotes stay separate.' }}</p>
                        <InputError id="budget-currency-error" :message="settings.errors.currency" />
                    </div>
                    <div class="ea-form-field">
                        <label for="budget-target">Budget target <span>Optional</span></label>
                        <input id="budget-target" v-model="settings.target" type="number" inputmode="decimal" min="0" max="999999999.99" step="0.01" placeholder="Not set" :aria-invalid="Boolean(settings.errors.target)" aria-describedby="budget-target-error" />
                        <InputError id="budget-target-error" :message="settings.errors.target" />
                    </div>
                    <div class="ea-task-editor-actions ea-form-field-wide"><button class="ea-button" :disabled="settings.processing">{{ settings.processing ? 'Saving…' : 'Save budget settings' }}</button></div>
                </form>
            </details>
            <template v-if="event.budget_currency">
                <EventExpenseEditor v-if="adding" :event="event" :categories="categories" @saved="closeEditor" @cancel="closeEditor" />
                <form class="ea-task-search" role="search" @submit.prevent="applyFilters">
                    <div class="ea-form-field"><label for="expense-search">Find an expense</label><input id="expense-search" v-model="search.search" type="search" maxlength="180" placeholder="Search by name" /></div>
                    <div class="ea-form-field"><label for="expense-category">Category</label><select id="expense-category" v-model="search.category"><option value="">All categories</option><option v-for="category in categories" :key="category" :value="category">{{ category }}</option></select></div>
                    <button class="ea-button ea-button-outline" :disabled="search.processing">Search</button>
                    <Link v-if="filters.search || filters.category" :href="route('events.budget.index', event.id)" class="ea-text-link">Clear filters</Link>
                </form>
                <p class="ea-budget-caption">{{ expenses.total }} {{ expenses.total === 1 ? 'expense' : 'expenses' }}{{ filters.search || filters.category ? ' matching these filters' : '' }} · All amounts in {{ event.budget_currency }}. Totals include the whole budget.</p>
                <ul v-if="expenses.data.length" class="ea-expense-list">
                    <li v-for="expense in expenses.data" :key="expense.id" class="ea-expense-record">
                        <EventExpenseEditor v-if="editing === expense.id" :event="event" :expense="expense" :categories="categories" @saved="closeEditor" @cancel="closeEditor" />
                        <template v-else>
                            <div class="ea-expense-heading">
                                <div class="ea-expense-name"><h3>{{ expense.title }}</h3><span>{{ expense.category }}</span></div>
                                <dl class="ea-expense-amounts"><div><dt>Estimated</dt><dd>{{ money(expense.estimated_minor) }}</dd></div><div><dt>Actual</dt><dd>{{ expense.actual_minor === null ? 'Not confirmed' : money(expense.actual_minor) }}</dd></div></dl>
                                <div v-if="canEdit" class="ea-live-task-actions"><button :id="'edit-expense-' + expense.id" class="ea-text-link" :aria-label="'Edit ' + expense.title" @click="openEditor(expense)">Edit</button><button class="ea-text-link ea-danger-link" :aria-label="'Delete ' + expense.title" @click="deleting = expense.id; removal.clearErrors()">Delete</button></div>
                            </div>
                            <div class="ea-expense-status"><span class="ea-tag" :class="{ 'ea-tag-rose': expense.overdue, 'ea-tag-green': expense.payment_status === 'Paid' || expense.payment_status === 'No cost' }">{{ expense.overdue ? 'Overdue · ' : '' }}{{ expense.payment_status }}</span><span v-if="expense.due_date">Due <time :datetime="expense.due_date">{{ new Intl.DateTimeFormat(undefined, { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(expense.due_date + 'T12:00:00')) }}</time></span></div>
                            <EventExpensePayments :event="event" :expense="expense" :can-edit="canEdit" :today="today" />
                            <details v-if="expense.notes" class="ea-expense-notes"><summary>Notes</summary><p>{{ expense.notes }}</p></details>
                            <div v-if="deleting === expense.id" class="ea-task-confirm" role="group" :aria-label="'Delete ' + expense.title">
                                <p>Delete “{{ expense.title }}”? This cannot be undone.</p>
                                <InputError :message="removal.errors.expense" role="alert" />
                                <button class="ea-button ea-button-outline" :disabled="removal.processing" @click="remove(expense)">{{ removal.processing ? 'Deleting…' : 'Delete expense' }}</button>
                                <button class="ea-text-link" :disabled="removal.processing" @click="deleting = null">Keep expense</button>
                            </div>
                        </template>
                    </li>
                </ul>
                <div v-else class="ea-task-empty"><h3>{{ filters.search || filters.category ? 'No matching expenses' : 'Room for every detail' }}</h3><p>{{ filters.search || filters.category ? 'Try another name or clear your filters.' : canEdit ? 'Start with your venue, catering, or the first cost you know about.' : 'Your team hasn’t added any expenses yet.' }}</p></div>
                <nav v-if="expenses.last_page > 1" class="ea-task-pagination" aria-label="Expense pages"><Link v-if="expenses.prev_page_url" :href="expenses.prev_page_url" preserve-scroll class="ea-text-link">Previous</Link><span>Page {{ expenses.current_page }} of {{ expenses.last_page }}</span><Link v-if="expenses.next_page_url" :href="expenses.next_page_url" preserve-scroll class="ea-text-link">Next</Link></nav>
            </template>
            <p v-else-if="!canEdit" class="ea-task-empty">Your team hasn’t set up a budget yet.</p>
        </section>
    </EventLayout>
</template>
