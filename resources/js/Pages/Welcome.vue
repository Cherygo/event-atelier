<script setup>
import DesignPreview from "@/Components/DesignPreview.vue";
import HomePreview from "@/Components/HomePreview.vue";

const showWorkspace =
    typeof window !== "undefined" &&
    new URLSearchParams(window.location.search).get("view") === "workspace";
</script>

<template>
    <DesignPreview v-if="showWorkspace" />
    <HomePreview v-else />
</template>
