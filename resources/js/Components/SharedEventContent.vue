<script setup>
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
        <p class="ea-public-readonly">A read-only view of the details your host has chosen to share.</p>
    </article>
</template>
