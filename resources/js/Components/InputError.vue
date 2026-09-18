<script setup>
defineProps({
    id: {
        type: String,
        default: undefined,
    },
    message: {
        type: String,
    },
    reserveSpace: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div v-show="message || reserveSpace" :id="id" :class="{ 'ea-field-feedback': reserveSpace }" aria-live="polite" aria-atomic="true">
        <p class="text-sm text-red-600">
            {{ message }}
        </p>
    </div>
</template>
