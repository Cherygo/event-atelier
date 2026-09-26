<script setup>
import { Head, Link } from '@inertiajs/vue3';
import EventLayout from '@/Layouts/EventLayout.vue';
import { formatBudgetAmount } from '@/budgetFormat';
const props = defineProps({ event: Object, metrics: Object, attention: Array, today: String, canEdit: Boolean, budget: Object });
const money = minor => formatBudgetAmount(minor, props.event.budget_currency);
const taskLink = (filters = {}) => route('events.tasks.index', { event: props.event.id, ...filters });
const dateLabel = (date) => new Intl.DateTimeFormat(undefined, { day: 'numeric', month: 'short' }).format(new Date(date + 'T12:00:00'));
</script>

<template>
    <EventLayout :event="event">
        <Head :title="'Overview · ' + event.name" />
        <template #description><p>Your plan, at a glance. A little clarity for what comes next.</p></template>
        <section v-if="!metrics.total" class="ea-event-section ea-overview-empty">
            <span class="ea-eyebrow">A place to begin</span>
            <h2>Every occasion starts with a first step.</h2>
            <p>{{ canEdit ? 'Add the things you need to do, give them a date, and share the work with your team.' : 'Your team hasn’t added any tasks yet. The shared plan will appear here.' }}</p>
            <Link :href="taskLink()" class="ea-button">{{ canEdit ? 'Plan your first task' : 'View tasks' }}</Link>
        </section>
        <div v-else class="ea-overview-grid">
            <section class="ea-overview-progress" aria-labelledby="progress-heading">
                <span class="ea-eyebrow">The bigger picture</span>
                <h2 id="progress-heading">Planning progress</h2>
                <div class="ea-overview-fraction"><strong>{{ metrics.completed }}</strong><span>of {{ metrics.total }} tasks completed</span></div>
                <progress :value="metrics.completed" :max="metrics.total" :aria-label="metrics.percent + '% of tasks completed'"></progress>
                <dl class="ea-overview-counts">
                    <div><dt><Link :href="taskLink({ status: 'todo' })">To do</Link></dt><dd>{{ metrics.todo }}</dd></div>
                    <div><dt><Link :href="taskLink({ status: 'in_progress' })">In progress</Link></dt><dd>{{ metrics.in_progress }}</dd></div>
                    <div><dt><Link :href="taskLink({ status: 'completed' })">Completed</Link></dt><dd>{{ metrics.completed }}</dd></div>
                </dl>
                <Link :href="taskLink({ status: 'all' })" class="ea-text-link">View all tasks →</Link>
            </section>
            <section class="ea-overview-attention" aria-labelledby="attention-heading">
                <span class="ea-eyebrow">Keep things moving</span>
                <h2 id="attention-heading">On the horizon</h2>
                <div class="ea-overview-deadlines">
                    <Link :href="taskLink({ due: 'overdue', sort: 'due' })" :class="{ 'has-overdue': metrics.overdue }"><strong>{{ metrics.overdue }}</strong> overdue</Link>
                    <Link :href="taskLink({ due: 'today' })"><strong>{{ metrics.today }}</strong> due today</Link>
                </div>
                <p class="ea-overview-caption">Open tasks due within seven days, including anything overdue.</p>
                <ul v-if="attention.length" class="ea-overview-agenda">
                    <li v-for="task in attention" :key="task.id">
                        <time :datetime="task.due_date" :class="{ 'is-overdue': task.due_date < today }">{{ dateLabel(task.due_date) }}<span v-if="task.due_date < today">Overdue</span><span v-else-if="task.due_date === today">Today</span></time>
                        <div><Link :href="taskLink({ search: task.title, status: 'active' })">{{ task.title }}</Link><span>{{ task.assignee?.name ?? 'Unassigned' }}</span></div>
                    </li>
                </ul>
                <p v-else class="ea-overview-calm">Nothing due in the next seven days. There’s room to look ahead.</p>
                <Link :href="taskLink({ sort: 'due' })" class="ea-text-link">Review deadlines →</Link>
            </section>
        </div>
        <section class="ea-event-section ea-overview-budget" aria-labelledby="overview-budget-heading">
            <div class="ea-task-toolbar"><h2 id="overview-budget-heading">Budget, in view</h2><Link :href="route('events.budget.index', event.id)" class="ea-text-link">{{ !budget && canEdit ? 'Set up budget' : 'View budget' }} →</Link></div>
            <template v-if="budget">
                <dl class="ea-budget-totals"><div><dt>Target</dt><dd>{{ money(event.budget_target_minor) }}</dd></div><div><dt>Forecast</dt><dd>{{ money(budget.forecast_minor) }}</dd></div><div><dt>Outstanding</dt><dd>{{ money(budget.outstanding_minor) }}</dd></div></dl>
                <p>{{ money(budget.paid_minor) }} recorded paid. Outstanding includes confirmed costs only.{{ budget.overdue_count ? ` ${budget.overdue_count} ${budget.overdue_count === 1 ? 'expense is' : 'expenses are'} overdue.` : '' }}</p>
                <p v-if="budget.remaining_minor !== null && budget.remaining_minor < 0" class="ea-budget-over">The forecast is {{ money(Math.abs(budget.remaining_minor)) }} over your target.</p>
                <p v-else-if="budget.unconfirmed_count">Forecast includes estimates for {{ budget.unconfirmed_count }} {{ budget.unconfirmed_count === 1 ? 'unconfirmed expense' : 'unconfirmed expenses' }}.</p>
            </template>
            <p v-else>{{ canEdit ? 'Choose a currency, set your target, and bring the costs into the plan.' : 'Your team hasn’t set up a budget yet.' }}</p>
        </section>
    </EventLayout>
</template>
