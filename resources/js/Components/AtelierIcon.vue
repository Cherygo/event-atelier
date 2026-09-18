<script setup>
defineProps({ name: { type: String, default: 'arrow' } });
const paths = {
    arrow: 'M4 12h16m-6-6 6 6-6 6',
    diagonal: 'M5 19 19 5M5 5h14v14',
    check: 'm5 12 4 4L19 6',
    plus: 'M12 5v14M5 12h14',
    close: 'm6 6 12 12M6 18 18 6',
    menu: 'M4 7h16M4 12h16M4 17h16',
    overview: 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
    tasks: 'm3 6 2 2 3-4M11 6h10M3 13h4M11 13h10M3 20h4M11 20h10',
    vendors: 'm12 3 9 9-9 9-9-9z',
    budget: 'M12 3a9 9 0 1 0 9 9h-9zM16 3v5h5a9 9 0 0 0-5-5Z',
    share: 'M15 4h5v5M20 4 10 14M10 5H4v15h15v-6',
    calendar: 'M4 5h16v16H4zM8 3v4M16 3v4M4 10h16',
    pin: 'M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0ZM14 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0',
    people: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0M17 3a4 4 0 0 1 0 8M22 21v-2a4 4 0 0 0-3-3.87',
    chevron: 'm6 9 6 6 6-6',
    back: 'M20 12H4m6-6-6 6 6 6',
    mail: 'M3 5h18v14H3zM3 5l9 8 9-8',
    search: 'M17 17l4 4M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0',
    lock: 'M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4',
    leaf: 'M20 3C8 2 3 7 5 14s14 4 15-11ZM4 21 15 10',
};
</script>

<template>
    <svg
        class="ea-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path :d="paths[name] || paths.arrow" />
    </svg>
</template>
