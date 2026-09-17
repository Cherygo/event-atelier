<script setup>
import { computed, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import PlanningIllustration from "./PlanningIllustration.vue";
import "../../css/home-preview.css";

const concepts = [
    {
        id: "invitation",
        name: "The Invitation",
        note: "An open, editorial welcome with an architectural portrait.",
        title: "Good things",
        line: "come together.",
        label: "A LITTLE MORE JOY. A LITTLE LESS JUGGLING.",
    },
    {
        id: "orangery",
        name: "The Orangery",
        note: "An immersive forest-green opening. Quietly luxurious.",
        title: "Make space for",
        line: "something wonderful.",
        label: "THOUGHTFULLY PLANNED. BEAUTIFULLY REMEMBERED.",
    },
    {
        id: "journal",
        name: "The Journal",
        note: "A magazine cover, a visual story, and room to breathe.",
        title: "The art of",
        line: "coming together.",
        label: "THE GATHERING ISSUE · VOL. 01",
    },
    {
        id: "planning-room",
        name: "The Planning Room",
        note: "The product takes centre stage, with an editorial sensibility.",
        title: "Beautiful events.",
        line: "A clearer head.",
        label: "YOUR PLANS HAVE FOUND THEIR PLACE.",
    },
    {
        id: "gathering",
        name: "The Gathering",
        note: "A confident split composition, warm photography, and bold type.",
        title: "For the moments",
        line: "that bring us closer.",
        label: "FROM THE FIRST IDEA TO THE LAST DANCE.",
    },
];
const hash = typeof window === "undefined" ? "" : window.location.hash.slice(1);
const active = ref(
    concepts.some((item) => item.id === hash) ? hash : "invitation",
);
const concept = computed(() =>
    concepts.find((item) => item.id === active.value),
);
const menuOpen = ref(false);
const occasion = ref("wedding");
const occasionText = computed(
    () =>
        ({
            wedding: {
                title: "Your day. Your kind of wonderful.",
                copy: "From the venue that feels just right to the people you want beside you. Make space for the joy of planning your wedding.",
                image: "/images/preview-garden.jpg",
                alt: "A wedding couple holding a bouquet in warm evening light",
            },
            corporate: {
                title: "Bring the team a little closer.",
                copy: "Offsites, launches, and company celebrations. Give every stakeholder a clear plan, every decision a home, and the occasion your full attention.",
                image: "/images/preview-estate.jpg",
                alt: "An elegant event hall set for a large gathering",
            },
            planner: {
                title: "Your vision. Beautifully organised.",
                copy: "Give each client their own planning space. Keep the details, decisions, and collaborators together as you move from one event to the next.",
                image: "/images/preview-table.jpg",
                alt: "A celebration table with colourful flowers and carefully arranged place settings",
            },
        })[occasion.value],
);
const features = [
    {
        number: "01",
        title: "Every little to-do.",
        copy: "A clear next step, a shared checklist, and the lovely feeling of ticking things off.",
        mark: "✓",
        detail: "TASKS & TIMELINES",
    },
    {
        number: "02",
        title: "The right people.",
        copy: "Bring quotes, conversations, and your vendor shortlist into one considered view.",
        mark: "◇",
        detail: "VENDOR COMPARISON",
    },
    {
        number: "03",
        title: "Room for what matters.",
        copy: "Keep the big picture in sight, from your first estimate to your final payment.",
        mark: "◷",
        detail: "BUDGET & PAYMENTS",
    },
    {
        number: "04",
        title: "Everyone, in the loop.",
        copy: "Share a planning page with your people. You choose which details they can see.",
        mark: "↗",
        detail: "SHARED PLANNING",
    },
];
function choose(id) {
    active.value = id;
    menuOpen.value = false;
    window.history.replaceState(null, "", "#" + id);
}
function goTo(id) {
    menuOpen.value = false;
    document
        .getElementById(id)
        ?.scrollIntoView({
            behavior: window.matchMedia("(prefers-reduced-motion: reduce)")
                .matches
                ? "auto"
                : "smooth",
        });
}
</script>

<template>
    <div class="home-collection">
        <Head title="Event Atelier — Homepage collection"
            ><meta
                name="description"
                content="A considered space for planning weddings, company events, and every gathering that matters. Explore five homepage directions."
        /></Head>
        <div class="home-review-bar">
            <span class="review-label"><i></i> HOMEPAGE COLLECTION</span>
            <nav aria-label="Homepage designs">
                <button
                    v-for="(item, index) in concepts"
                    :key="item.id"
                    :aria-pressed="active === item.id"
                    @click="choose(item.id)"
                >
                    <span>0{{ index + 1 }}</span
                    >{{ item.name.replace("The ", "") }}
                </button>
            </nav>
            <a href="/?view=workspace#editorial">Workspace designs ↗</a>
        </div>
        <div class="home-review-caption">
            <p>
                <strong>{{ concept.name }}</strong
                ><span>{{ concept.note }}</span>
            </p>
            <span class="palette-caption"
                ><i></i><i></i><i></i> EDITORIAL × ESTATE</span
            >
        </div>
        <div class="home-site" :class="'home-' + active">
            <header class="home-nav">
                <a
                    href="/"
                    class="home-wordmark"
                    aria-label="Event Atelier home"
                    ><span class="home-monogram">a<span>✳</span></span
                    ><span>event<br />atelier</span></a
                >
                <button
                    class="home-menu-toggle"
                    :aria-expanded="menuOpen"
                    aria-controls="home-navigation"
                    @click="menuOpen = !menuOpen"
                >
                    {{ menuOpen ? "Close −" : "Menu +" }}
                </button>
                <nav
                    id="home-navigation"
                    class="home-navigation"
                    :class="{ expanded: menuOpen }"
                    aria-label="Main navigation"
                >
                    <button @click="goTo('possibilities')">
                        The possibilities</button
                    ><button @click="goTo('occasions')">
                        For every occasion</button
                    ><button @click="goTo('how-it-works')">How it works</button>
                </nav>
                <div class="home-account">
                    <Link :href="route('login')">Log in</Link
                    ><Link :href="route('register')" class="home-button small"
                        >Start planning <span>↗</span></Link
                    >
                </div>
            </header>

            <main>
                <section class="home-hero">
                    <div class="hero-introduction">
                        <p class="home-kicker">{{ concept.label }}</p>
                        <h1>
                            {{ concept.title }}<br /><em>{{ concept.line }}</em>
                        </h1>
                        <p class="hero-description">
                            One thoughtful space for your tasks, your people,
                            and all the little details. Plan a wedding, a
                            company gathering, or something entirely your own.
                        </p>
                        <div class="hero-buttons">
                            <Link :href="route('register')" class="home-button"
                                >Plan something wonderful <span>↗</span></Link
                            ><a href="/?view=workspace#estate" class="home-link"
                                >Step inside the atelier <span>→</span></a
                            >
                        </div>
                        <p class="hero-reassurance">
                            <span>✳</span> Free to start. A little calmer from
                            here.
                        </p>
                    </div>
                    <div
                        v-if="active !== 'planning-room'"
                        class="home-hero-visual"
                    >
                        <figure class="home-main-photo">
                            <img
                                :src="
                                    active === 'journal'
                                        ? '/images/preview-garden.jpg'
                                        : '/images/preview-table.jpg'
                                "
                                :alt="
                                    active === 'journal'
                                        ? 'A couple sharing a moment in warm evening light'
                                        : 'A celebration table with colourful flowers, glassware, and carefully arranged place settings'
                                "
                                fetchpriority="high"
                            />
                            <figcaption>
                                THE DETAILS FADE. THE FEELING STAYS.
                            </figcaption>
                        </figure>
                        <div class="home-photo-seal" aria-hidden="true">
                            <span>A LITTLE</span><b>✳</b
                            ><span>MORE TOGETHER</span>
                        </div>
                        <div class="home-floating-note">
                            <span class="floating-check">✓</span>
                            <div>
                                One less thing on your mind.<small
                                    >Venue booked. Possibilities,
                                    endless.</small
                                >
                            </div>
                        </div>
                        <span class="photo-side-note"
                            >GOOD COMPANY. BEAUTIFUL POSSIBILITIES.</span
                        >
                    </div>
                    <div v-else class="hero-product">
                        <PlanningIllustration /><span class="product-footnote"
                            >A place for the plans. More space for the
                            moment.</span
                        >
                        <div class="product-stamp">
                            Less juggling.<br /><em>More joy.</em><span>✳</span>
                        </div>
                    </div>
                    <div v-if="active === 'journal'" class="journal-cover-line">
                        <span>WEDDINGS / WORK / EVERYTHING IN BETWEEN</span>
                        <p>
                            Some things deserve<br /><em
                                >a little more thought.</em
                            >
                        </p>
                        <span>SCROLL TO EXPLORE ↓</span>
                    </div>
                </section>

                <div class="home-occasion-strip">
                    <span>A PLACE FOR EVERY KIND OF TOGETHER</span>
                    <p>
                        Weddings <i>✳</i> Company gatherings <i>✳</i> Private
                        celebrations
                    </p>
                    <span>AND ALL THE MOMENTS IN BETWEEN</span>
                </div>

                <section id="possibilities" class="home-features home-section">
                    <div class="section-introduction">
                        <p class="home-kicker">THE BEAUTY IS IN THE DETAILS</p>
                        <h2>
                            A lot to bring together.<br /><em
                                >One lovely place to do it.</em
                            >
                        </h2>
                        <p>
                            Less searching through spreadsheets and message
                            threads.<br />More time for the parts you’re looking
                            forward to.
                        </p>
                    </div>
                    <div class="home-feature-grid">
                        <article
                            v-for="feature in features"
                            :key="feature.number"
                        >
                            <div class="feature-index">
                                <span>{{ feature.number }}</span
                                ><i>{{ feature.mark }}</i>
                            </div>
                            <p class="home-kicker">{{ feature.detail }}</p>
                            <h3>{{ feature.title }}</h3>
                            <p>{{ feature.copy }}</p>
                        </article>
                    </div>
                </section>

                <section
                    v-if="active !== 'planning-room'"
                    class="home-product-section home-section"
                >
                    <div class="product-section-copy">
                        <p class="home-kicker">WELCOME TO YOUR PLANNING ROOM</p>
                        <h2>
                            The big picture.<br /><em>The tiny details.</em
                            ><br />All right here.
                        </h2>
                        <p>
                            A calm home for everything that goes into a
                            wonderful occasion. Yours to organise. Easy to
                            share.
                        </p>
                        <a href="/?view=workspace#editorial" class="home-link"
                            >Take a look around <span>↗</span></a
                        >
                    </div>
                    <PlanningIllustration compact />
                </section>

                <section id="occasions" class="home-occasions home-section">
                    <div class="occasion-photo">
                        <img
                            :src="occasionText.image"
                            :alt="occasionText.alt"
                            loading="lazy"
                        /><span>THOUGHTFULLY YOURS.</span>
                    </div>
                    <div class="occasion-copy">
                        <p class="home-kicker">WHATEVER BRINGS YOU TOGETHER</p>
                        <div class="occasion-tabs" aria-label="Event examples">
                            <button
                                :aria-pressed="occasion === 'wedding'"
                                @click="occasion = 'wedding'"
                            >
                                For couples</button
                            ><button
                                :aria-pressed="occasion === 'corporate'"
                                @click="occasion = 'corporate'"
                            >
                                For teams</button
                            ><button
                                :aria-pressed="occasion === 'planner'"
                                @click="occasion = 'planner'"
                            >
                                For planners
                            </button>
                        </div>
                        <h2>{{ occasionText.title }}</h2>
                        <p>{{ occasionText.copy }}</p>
                        <Link :href="route('register')" class="home-link"
                            >Make it your own <span>↗</span></Link
                        >
                    </div>
                </section>

                <section id="how-it-works" class="home-steps home-section">
                    <div>
                        <p class="home-kicker">FROM AN IDEA TO AN OCCASION</p>
                        <h2>
                            Start with a spark.<br /><em
                                >We’ll help with the details.</em
                            >
                        </h2>
                    </div>
                    <ol>
                        <li>
                            <span>01</span>
                            <div>
                                <h3>Give your gathering a home.</h3>
                                <p>
                                    A name, a date, a little possibility. Create
                                    your event workspace.
                                </p>
                            </div>
                        </li>
                        <li>
                            <span>02</span>
                            <div>
                                <h3>Bring the details together.</h3>
                                <p>
                                    Make your list, compare your vendors, and
                                    give your budget a plan.
                                </p>
                            </div>
                        </li>
                        <li>
                            <span>03</span>
                            <div>
                                <h3>Let your people in.</h3>
                                <p>
                                    Invite collaborators and share the details
                                    that matter to them.
                                </p>
                            </div>
                        </li>
                    </ol>
                </section>

                <section class="home-final-invitation">
                    <span class="invitation-star" aria-hidden="true">✳</span>
                    <p class="home-kicker">
                        THERE’S SOMETHING TO LOOK FORWARD TO.
                    </p>
                    <h2>Let’s make it<br /><em>something wonderful.</em></h2>
                    <Link :href="route('register')" class="home-button light"
                        >Find your planning space <span>↗</span></Link
                    >
                    <p>Free for everyone. Made for your kind of gathering.</p>
                </section>
            </main>
            <footer class="home-footer">
                <a href="/" class="home-wordmark"
                    ><span class="home-monogram">a<span>✳</span></span
                    ><span>event<br />atelier</span></a
                >
                <p>Every detail, together.</p>
                <span>© 2026 EVENT ATELIER</span
                ><a href="/?view=workspace#editorial"
                    >Explore the workspace ↗</a
                >
            </footer>
        </div>
        <footer class="home-review-footer">
            Homepage design exploration · Sample workspace data · Editorial
            warmth, Estate character.
        </footer>
    </div>
</template>
