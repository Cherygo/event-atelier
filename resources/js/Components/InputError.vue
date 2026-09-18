<script setup>
defineProps({
    id: {
        type: String,
        default: undefined,
    },
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message" :id="id" role="alert">
        <p class="text-sm text-red-600">
            {{ message }}
        </p>
    </div>
</template>
