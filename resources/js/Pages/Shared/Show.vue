<script setup>
import { Head } from '@inertiajs/vue3';
import AtelierBrand from '@/Components/AtelierBrand.vue';
import SharedEventContent from '@/Components/SharedEventContent.vue';
import '@/../css/atelier.css';

defineProps({ plan: { type: Object, default: null } });
</script>

<template>
    <div class="ea-site ea-public-page">
        <Head :title="plan ? plan.name : 'Shared page unavailable'">
            <meta name="robots" content="noindex, nofollow, noarchive" />
            <meta name="referrer" content="no-referrer" />
        </Head>
        <header class="ea-public-brand"><AtelierBrand /></header>
        <main>
            <SharedEventContent v-if="plan" :plan="plan" />
            <section v-else class="ea-public-unavailable">
                <h1>This page isn’t available.</h1>
                <p>The link may have been replaced or sharing may have stopped. Ask your host for a current link.</p>
                <a href="/" class="ea-text-link">Visit Event Atelier</a>
            </section>
        </main>
        <footer class="ea-public-footer">Planned with Event Atelier</footer>
    </div>
</template>
