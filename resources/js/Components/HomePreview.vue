<script setup>
import { computed, ref } from 'vue';
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
                <a href="#possibilities" @click="menuOpen = false"
                    >The possibilities</a
                >
                <a href="#planning-room" @click="menuOpen = false"
                    >Take a look inside</a
                >
                <a href="#how-it-works" @click="menuOpen = false"
                    >How it works</a
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
            <section class="ea-home-hero">
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
                                    :name="
                                        openFeature === feature.id
                                            ? 'close'
                                            : 'plus'
                                    "
                                />
                            </button>
                        </h3>
                        <div
                            v-show="openFeature === feature.id"
                            :id="'feature-' + feature.id"
                        >
                            <p>{{ feature.description }}</p>
                            <Link
                                :href="'/?view=workspace&section=' + feature.id"
                                class="ea-text-link"
                                >Explore {{ feature.label.toLowerCase() }}
                                <AtelierIcon name="arrow"
                            /></Link>
                        </div>
                    </section>
                </div>
            </section>

            <section class="ea-home-audiences">
                <div>
                    <h2>
                        Your day.<br />Your team.<br /><em
                            >Your kind of wonderful.</em
                        >
                    </h2>
                    <p>
                        Whether you’re planning your first gathering or your
                        next client event, give every occasion a space of its
                        own.
                    </p>
                </div>
                <div class="ea-audience-notes">
                    <article>
                        <h3>For couples & hosts</h3>
                        <p>
                            Plan together, compare your favourites, and let the
                            details take shape around you.
                        </p>
                    </article>
                    <article>
                        <h3>For company teams</h3>
                        <p>
                            Give each task an owner, keep spending visible, and
                            bring stakeholders into the plan.
                        </p>
                    </article>
                    <article>
                        <h3>For professional planners</h3>
                        <p>
                            A dedicated workspace for each client, with the
                            people and information that belong there.
                        </p>
                    </article>
                </div>
            </section>

            <section id="how-it-works" class="ea-home-steps ea-home-section">
                <h2>From a first idea<br /><em>to a day to remember.</em></h2>
                <ol>
                    <li>
                        <span>1</span>
                        <h3>Give it a home.</h3>
                        <p>
                            Create an account and name your gathering. Start
                            with the details you know.
                        </p>
                    </li>
                    <li>
                        <span>2</span>
                        <h3>Make it your own.</h3>
                        <p>
                            Gather tasks, compare vendors, and give your budget
                            a plan.
                        </p>
                    </li>
                    <li>
                        <span>3</span>
                        <h3>Bring your people in.</h3>
                        <p>
                            Invite collaborators and choose the details you want
                            to share.
                        </p>
                    </li>
                </ol>
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
