<script setup>
import { formatBudgetAmount } from '@/budgetFormat';
defineProps({ plan: { type: Object, required: true } });
const dateLabel = (date) => new Date(`${date}T12:00:00`).toLocaleDateString('en', { day: 'numeric', month: 'long', year: 'numeric' });
</script>

<template>
    <article class="ea-public-plan">
        <header class="ea-public-heading">
            <h1>{{ plan.name }}</h1>
            <p v-if="plan.date" class="ea-public-date">{{ dateLabel(plan.date) }}</p>
            <p v-if="plan.location">{{ plan.location }}</p>
        </header>
        <section v-if="plan.note" class="ea-public-section" aria-labelledby="welcome-heading">
            <h2 id="welcome-heading">A note from your host</h2>
            <p class="ea-public-note">{{ plan.note }}</p>
        </section>
        <section v-if="plan.progress" class="ea-public-section" aria-labelledby="progress-heading">
            <h2 id="progress-heading">Coming together</h2>
            <p v-if="plan.progress.total"><strong>{{ plan.progress.completed }} of {{ plan.progress.total }}</strong> planning tasks complete.</p>
            <p v-else>Planning is just getting started.</p>
        </section>
        <section v-if="plan.vendors" class="ea-public-section" aria-labelledby="vendors-heading">
            <h2 id="vendors-heading">The people behind the occasion</h2>
            <ul v-if="plan.vendors.items.length" class="ea-public-vendors"><li v-for="(vendor, index) in plan.vendors.items" :key="index"><strong>{{ vendor.name }}</strong><span>{{ vendor.category }}</span></li></ul>
            <p v-else>No vendors have been booked yet.</p>
            <p v-if="plan.vendors.total > plan.vendors.items.length" class="ea-sharing-help">Showing the first {{ plan.vendors.items.length }} of {{ plan.vendors.total }} booked vendors.</p>
        </section>
        <section v-if="plan.budget !== undefined" class="ea-public-section" aria-labelledby="budget-heading">
            <h2 id="budget-heading">Budget at a glance</h2>
            <template v-if="plan.budget">
                <dl class="ea-public-budget"><div><dt>Budget target</dt><dd>{{ formatBudgetAmount(plan.budget.target_minor, plan.budget.currency) }}</dd></div><div><dt>Forecast total</dt><dd>{{ formatBudgetAmount(plan.budget.forecast_minor, plan.budget.currency) }}</dd></div></dl>
                <p class="ea-sharing-help">The forecast uses actual costs where confirmed and estimates for everything else.</p>
            </template>
            <p v-else>The budget hasn’t been set up yet.</p>
        </section>
        <p class="ea-public-readonly">A read-only view of the details your host has chosen to share.</p>
    </article>
</template>
