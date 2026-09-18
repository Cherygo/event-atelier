<script setup>
import DesignPreview from '@/Components/DesignPreview.vue';
import HomePreview from '@/Components/HomePreview.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import '../../css/atelier.css';

const page = usePage();
const showWorkspace = computed(
    () =>
        new URLSearchParams(page.url.split('?')[1] || '').get('view') ===
        'workspace',
);
</script>

<template>
    <DesignPreview v-if="showWorkspace" />
    <HomePreview v-else />
</template>
