<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AtelierBrand from './AtelierBrand.vue';
import AtelierIcon from './AtelierIcon.vue';
import PlanningIllustration from './PlanningIllustration.vue';
import { demoEvents } from '../atelierDemo';

const menuOpen = ref(false);
const selectedOccasion = ref('wedding');
const event = computed(() =>
    demoEvents.find((item) => item.id === selectedOccasion.value),
);
const openFeature = ref('tasks');
const activeSection = ref('');
let sectionObserver;
const features = [
    {
        id: 'tasks',
        title: 'Every next step, in view.',
        label: 'Tasks & timelines',
        description:
            'Give each detail a deadline and a person. A shared checklist turns a long list of possibilities into a clear next step.',
    },
    {
        id: 'vendors',
        title: 'Find your kind of people.',
        label: 'Vendor comparisons',
        description:
            'Compare quotes and keep a considered shortlist. See the price, the possibilities, and the details behind each decision.',
    },
    {
        id: 'budget',
        title: 'Make room for what matters.',
        label: 'Budget & payments',
        description:
            'Keep planned spending, deposits, and upcoming payments together. Know what is committed and what you still have room for.',
    },
    {
        id: 'share',
        title: 'Keep everyone in the picture.',
        label: 'Shared planning',
        description:
            'Invite your planning circle or prepare a read-only page. Choose which details to share, with the budget hidden by default.',
    },
];

async function scrollToSection(clickEvent, sectionId) {
    clickEvent.preventDefault();
    activeSection.value = sectionId;
    menuOpen.value = false;
    await nextTick();

    const target = document.getElementById(sectionId);

    if (!target) {
        return;
    }

    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    target.scrollIntoView({
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
        block: 'start',
    });
    window.history.replaceState(null, '', `#${sectionId}`);
}

onMounted(() => {
    sectionObserver = new IntersectionObserver(
        (entries) => {
            const visibleSection = entries
                .filter((entry) => entry.isIntersecting)
                .sort(
                    (first, second) =>
                        second.intersectionRatio - first.intersectionRatio,
                )[0];

            if (visibleSection) {
                activeSection.value = visibleSection.target.id;
            }
        },
        { rootMargin: '-18% 0px -62% 0px', threshold: [0, 0.15, 0.35] },
    );

    ['home-intro', 'planning-room', 'possibilities', 'audiences'].forEach(
        (sectionId) => {
            const section = document.getElementById(sectionId);

            if (section) {
                sectionObserver.observe(section);
            }
        },
    );
});

onBeforeUnmount(() => sectionObserver?.disconnect());
</script>

<template>
    <div class="ea-site">
        <Head title="Thoughtful event planning" />
        <a href="#main-content" class="ea-skip">Skip to content</a>
        <header class="ea-site-header">
            <AtelierBrand />
            <button
                class="ea-mobile-toggle ea-icon-button"
                :aria-expanded="menuOpen"
                aria-controls="site-navigation"
                :aria-label="menuOpen ? 'Close navigation' : 'Open navigation'"
                @click="menuOpen = !menuOpen"
            >
                <AtelierIcon :name="menuOpen ? 'close' : 'menu'" />
            </button>
            <nav
                id="site-navigation"
                :class="{ 'is-open': menuOpen }"
                aria-label="Main navigation"
            >
                <a
                    href="#planning-room"
                    :aria-current="
                        activeSection === 'planning-room'
                            ? 'location'
                            : undefined
                    "
                    @click="scrollToSection($event, 'planning-room')"
                    >Inside the atelier</a
                >
                <a
                    href="#possibilities"
                    :aria-current="
                        activeSection === 'possibilities'
                            ? 'location'
                            : undefined
                    "
                    @click="scrollToSection($event, 'possibilities')"
                    >What it brings together</a
                >
                <a
                    href="#audiences"
                    :aria-current="
                        activeSection === 'audiences' ? 'location' : undefined
                    "
                    @click="scrollToSection($event, 'audiences')"
                    >Who it’s for</a
                >
            </nav>
            <div class="ea-account-links">
                <Link :href="route('login')" class="ea-text-link">Log in</Link
                ><Link :href="route('register')" class="ea-button"
                    >Start planning <AtelierIcon name="diagonal"
                /></Link>
            </div>
        </header>

        <main id="main-content">
            <section id="home-intro" class="ea-home-hero">
                <div class="ea-hero-copy">
                    <h1>Good things<br />come <em>together.</em></h1>
                    <p class="ea-hero-intro">
                        The thoughtful way to plan your next gathering.
                    </p>
                    <p>
                        Bring your tasks, vendors, budget, and people into one
                        calm space. For a wedding, a company occasion, or
                        something entirely your own.
                    </p>
                    <div class="ea-hero-actions">
                        <Link :href="route('register')" class="ea-button"
                            >Start your event <AtelierIcon name="arrow" /></Link
                        ><Link href="/?view=workspace" class="ea-text-link"
                            >Explore a sample plan <AtelierIcon name="diagonal"
                        /></Link>
                    </div>
                    <span class="ea-free-note"
                        ><AtelierIcon name="check" /> Free for everyone. Room
                        for every detail.</span
                    >
                </div>
                <figure class="ea-hero-photo">
                    <img
                        src="/images/preview-table.jpg"
                        alt="A long celebration table set with flowers, glassware, and place settings"
                        width="1600"
                        height="1068"
                        fetchpriority="high"
                    />
                    <figcaption>
                        <span
                            >The details bring it together.<br /><em
                                >The people make it matter.</em
                            ></span
                        ><span class="ea-photo-signature" aria-hidden="true"
                            >a.</span
                        >
                    </figcaption>
                </figure>
                <div class="ea-hero-foot">
                    <span
                        >For the occasion.<br /><strong
                            >And everything before it.</strong
                        ></span
                    >
                    <p>
                        Weddings <span aria-hidden="true">/</span> Company
                        gatherings <span aria-hidden="true">/</span> Private
                        celebrations
                    </p>
                    <a
                        href="#planning-room"
                        class="ea-round-link"
                        aria-label="Explore the planning room"
                        @click="scrollToSection($event, 'planning-room')"
                        ><AtelierIcon name="arrow"
                    /></a>
                </div>
            </section>

            <section id="planning-room" class="ea-home-plan ea-home-section">
                <div class="ea-section-top">
                    <div>
                        <h2>
                            A place for the plans.<br /><em
                                >More space for the moment.</em
                            >
                        </h2>
                        <p>
                            A clear view of what’s next, what’s decided, and
                            who’s involved.
                        </p>
                    </div>
                    <div class="ea-segmented" aria-label="Sample occasion">
                        <button
                            :aria-pressed="selectedOccasion === 'wedding'"
                            @click="selectedOccasion = 'wedding'"
                        >
                            A wedding</button
                        ><button
                            :aria-pressed="selectedOccasion === 'corporate'"
                            @click="selectedOccasion = 'corporate'"
                        >
                            A company retreat
                        </button>
                    </div>
                </div>
                <Transition name="ea-occasion" mode="out-in"
                    ><PlanningIllustration :key="event.id" :event="event"
                /></Transition>
                <div class="ea-demo-caption">
                    <span
                        >Illustrative event · Try the interactive
                        workspace</span
                    ><Link
                        :href="'/?view=workspace&event=' + event.id"
                        class="ea-text-link"
                        >Step inside the atelier <AtelierIcon name="arrow"
                    /></Link>
                </div>
            </section>

            <section
                id="possibilities"
                class="ea-home-features ea-home-section"
            >
                <div class="ea-features-intro">
                    <h2>
                        The big picture.<br /><em>The beautiful details.</em>
                    </h2>
                    <p>
                        Less searching through spreadsheets and messages. More
                        time for the parts you’re looking forward to.
                    </p>
                    <figure>
                        <img
                            :src="event.image"
                            :alt="event.imageAlt"
                            width="1400"
                            height="933"
                            loading="lazy"
                        />
                        <figcaption>
                            {{
                                event.id === 'wedding'
                                    ? 'Make a little room for the unexpected.'
                                    : 'Make space for a different kind of working day.'
                            }}
                        </figcaption>
                    </figure>
                </div>
                <div class="ea-feature-list">
                    <section
                        v-for="feature in features"
                        :key="feature.id"
                        class="ea-feature"
                        :class="{ 'is-open': openFeature === feature.id }"
                    >
                        <h3>
                            <button
                                :aria-expanded="openFeature === feature.id"
                                :aria-controls="'feature-' + feature.id"
                                @click="
                                    openFeature =
                                        openFeature === feature.id
                                            ? null
                                            : feature.id
                                "
                            >
                                <span
                                    >{{ feature.title
                                    }}<small>{{ feature.label }}</small></span
                                ><AtelierIcon
                                    name="plus"
                                    class="ea-feature-toggle-icon"
                                />
                            </button>
                        </h3>
                        <div
                            :id="'feature-' + feature.id"
                            class="ea-feature-panel"
                            :aria-hidden="openFeature !== feature.id"
                            :inert="openFeature !== feature.id"
                        >
                            <div class="ea-feature-panel-clip">
                                <div class="ea-feature-panel-inner">
                                    <p>{{ feature.description }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <Link
                        href="/?view=workspace"
                        class="ea-text-link ea-feature-cta"
                        >Explore the full sample workspace
                        <AtelierIcon name="arrow"
                    /></Link>
                </div>
            </section>

            <section id="audiences" class="ea-home-audiences">
                <div>
                    <h2>Made for the way<br /><em>you plan.</em></h2>
                    <p>
                        One gathering, a company occasion, or a full client
                        calendar—each event gets its own calm workspace.
                    </p>
                </div>
                <div class="ea-audience-notes">
                    <article>
                        <h3>For couples & hosts</h3>
                        <p>
                            Plan together and compare the decisions that shape
                            your gathering.
                        </p>
                    </article>
                    <article>
                        <h3>For company teams</h3>
                        <p>
                            Give work an owner and keep spending visible to the
                            right people.
                        </p>
                    </article>
                    <article>
                        <h3>For professional planners</h3>
                        <p>
                            Keep each client, collaborator, and decision in its
                            own space.
                        </p>
                    </article>
                </div>
            </section>

            <section class="ea-final-invitation">
                <AtelierIcon name="leaf" />
                <h2>Something wonderful<br /><em>starts with a plan.</em></h2>
                <Link
                    :href="route('register')"
                    class="ea-button ea-button-light"
                    >Create your free account <AtelierIcon name="arrow"
                /></Link>
                <p>Free for everyone. Made for your kind of gathering.</p>
            </section>
        </main>
        <footer class="ea-site-footer">
            <AtelierBrand />
            <p>Every detail, together.</p>
            <span>© 2026 Event Atelier</span
            ><Link href="/?view=workspace" class="ea-text-link"
                >Explore the workspace <AtelierIcon name="diagonal"
            /></Link>
        </footer>
    </div>
</template>
