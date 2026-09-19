<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AtelierBrand from '@/Components/AtelierBrand.vue';
import AtelierIcon from '@/Components/AtelierIcon.vue';
import '../../css/atelier.css';

const showingNavigation = ref(false);
const isMobile = ref(false);
const menuButton = ref(null);

const syncMobileState = () => {
    isMobile.value = window.matchMedia('(max-width: 800px)').matches;

    if (!isMobile.value) {
        showingNavigation.value = false;
    }
};

const closeNavigation = () => {
    showingNavigation.value = false;
    nextTick(() => menuButton.value?.focus());
};

const openNavigation = async () => {
    showingNavigation.value = true;
    await nextTick();
    document.querySelector('.ea-app-sidebar a')?.focus();
};

const handleKeydown = (event) => {
    if (event.key === 'Escape' && showingNavigation.value) {
        closeNavigation();
    }
};

onMounted(() => {
    syncMobileState();
    window.addEventListener('resize', syncMobileState);
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncMobileState);
    window.removeEventListener('keydown', handleKeydown);
});

const navigation = [
    { label: 'My events', icon: 'overview', route: 'events.index' },
    { label: 'Profile', icon: 'people', route: 'profile.edit' },
];
</script>

<template>
    <div class="ea-app">
        <a class="ea-skip" href="#main-content">Skip to content</a>

        <aside class="ea-app-sidebar" :class="{ 'is-open': showingNavigation }" :inert="isMobile && !showingNavigation">
            <div class="ea-app-brand-row">
                <AtelierBrand />
                <button class="ea-app-close" type="button" aria-label="Close navigation" @click="closeNavigation">
                    <AtelierIcon name="close" />
                </button>
            </div>

            <nav class="ea-app-nav" aria-label="Main navigation">
                <Link v-for="item in navigation" :key="item.route" :href="route(item.route)" :class="{ active: route().current(item.route === 'events.index' ? 'events.*' : item.route) }" @click="showingNavigation = false">
                    <AtelierIcon :name="item.icon" />
                    {{ item.label }}
                </Link>
            </nav>

            <div class="ea-app-sidebar-footer">
                <Link :href="route('logout')" method="post" as="button" class="ea-app-logout">
                    Log out
                    <AtelierIcon name="diagonal" />
                </Link>
                <div class="ea-app-account">
                    <div class="ea-app-avatar" aria-hidden="true">{{ $page.props.auth.user.name.slice(0, 1) }}</div>
                    <div>
                        <strong>{{ $page.props.auth.user.name }}</strong>
                        <span>{{ $page.props.auth.user.email }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <div class="ea-app-stage">
            <header class="ea-app-mobile-header">
                <AtelierBrand />
                <button ref="menuButton" class="ea-icon-button" type="button" aria-label="Open navigation" :aria-expanded="showingNavigation" @click="openNavigation">
                    <AtelierIcon name="menu" />
                </button>
            </header>
            <main id="main-content">
                <div v-if="$page.props.flash?.status" :key="$page.props.flash.status" class="ea-app-feedback" role="status">{{ $page.props.flash.status }}</div>
                <slot />
            </main>
        </div>
    </div>
</template>
