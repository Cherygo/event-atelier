<script setup>
import { computed, ref } from "vue";
import { Head } from "@inertiajs/vue3";
import "../../css/design-preview.css";

const concepts = [
    {
        id: "editorial",
        name: "The Editorial",
        detail: "A magazine for the moments that matter.",
        note: "Airy composition · warm paper · quiet luxury",
        title: "A little closer to",
        emphasis: "something wonderful.",
    },
    {
        id: "estate",
        name: "The Estate",
        detail: "An intimate, beautifully appointed planning room.",
        note: "Forest green · architectural details · rich contrast",
        title: "Great occasions.",
        emphasis: "Beautifully considered.",
    },
    {
        id: "studio",
        name: "The Studio",
        detail: "Clarity for every detail. Space for the big picture.",
        note: "Structured workspace · crisp type · planner-first",
        title: "Everything in place.",
        emphasis: "Room to create.",
    },
    {
        id: "keepsake",
        name: "The Keepsake",
        detail: "Your plans, gathered somewhere lovely.",
        note: "Rose stationery · personal touches · journal rhythm",
        title: "The beginning of",
        emphasis: "a beautiful gathering.",
    },
    {
        id: "folio",
        name: "The Folio",
        detail: "A bold point of view. A beautifully simple plan.",
        note: "Oversized type · timeline layout · graphic restraint",
        title: "Make room",
        emphasis: "for remarkable.",
    },
];
const initial =
    typeof window === "undefined" ? "" : window.location.hash.slice(1);
const active = ref(
    concepts.some((item) => item.id === initial) ? initial : "editorial",
);
const concept = computed(() =>
    concepts.find((item) => item.id === active.value),
);
const corporate = ref(false);
const section = ref("Overview");
const mobileMenu = ref(false);
const message = ref("");
const event = computed(() =>
    corporate.value
        ? {
              name: "The Autumn Assembly",
              type: "COMPANY RETREAT",
              place: "Heckfield Place, Hampshire",
              date: "24 October 2026",
              initials: "AA",
              people: "64 attendees",
              short: "Your next great gathering.",
          }
        : {
              name: "Olivia & Alexander",
              type: "A COUNTRYSIDE WEDDING",
              place: "The Orangery, Cotswolds",
              date: "24 October 2026",
              initials: "OA",
              people: "120 guests",
              short: "Your day, taking shape.",
          },
);
const tasks = ref([
    {
        id: 1,
        wedding: "Confirm the seasonal menu",
        corporate: "Confirm the retreat menu",
        category: "Catering",
        due: "Today",
        done: false,
    },
    {
        id: 2,
        wedding: "Review the floral proposal",
        corporate: "Review the stage design",
        category: "Design",
        due: "Tomorrow",
        done: false,
    },
    {
        id: 3,
        wedding: "Send the final invitations",
        corporate: "Send the team invitations",
        category: "Guests",
        due: "21 Sep",
        done: true,
    },
    {
        id: 4,
        wedding: "Book the evening musicians",
        corporate: "Confirm the keynote speaker",
        category: "Experience",
        due: "23 Sep",
        done: false,
    },
]);
const vendors = [
    {
        name: "The Orangery",
        type: "Venue",
        quote: "£8,500",
        status: "Booked",
        letter: "O",
    },
    {
        name: "Gather & Graze",
        type: "Catering",
        quote: "£4,200",
        status: "Shortlisted",
        letter: "G",
    },
    {
        name: "Stem & Story",
        type: "Florals & styling",
        quote: "£1,800",
        status: "Review quote",
        letter: "S",
    },
];
const finished = computed(
    () => 23 + tasks.value.filter((task) => task.done).length,
);
const tabs = ["Overview", "Tasks", "Vendors", "Budget", "Shared page"];
function selectConcept(id) {
    active.value = id;
    window.history.replaceState(null, "", "#" + id);
    message.value = "";
}
function selectSection(tab) {
    section.value = tab;
    mobileMenu.value = false;
}
function toggleTask(task) {
    task.done = !task.done;
    message.value = task.done
        ? "A little progress, beautifully made. Task completed in this preview."
        : "Task reopened in this preview.";
}
</script>

<template>
    <div class="design-lab">
        <Head title="Event Atelier — Design collection">
            <meta
                name="description"
                content="Explore five design directions for Event Atelier, a considered space for planning beautiful events."
            />
        </Head>
        <header class="lab-bar">
            <div class="lab-label">
                <span class="lab-dot"></span> DESIGN COLLECTION
                <span class="lab-season">/ 2026</span>
            </div>
            <nav class="concept-picker" aria-label="Design variations">
                <button
                    v-for="(item, index) in concepts"
                    :key="item.id"
                    :aria-pressed="active === item.id"
                    @click="selectConcept(item.id)"
                >
                    <span>0{{ index + 1 }}</span>
                    {{ item.name.replace("The ", "") }}
                </button>
            </nav>
            <span class="lab-preview">INTERACTIVE PREVIEW</span>
        </header>
        <div class="concept-caption">
            <p>
                <strong>{{ concept.name }}</strong
                ><span>{{ concept.note }}</span>
            </p>
            <div class="event-toggle" aria-label="Example event type">
                <button :aria-pressed="!corporate" @click="corporate = false">
                    Wedding
                </button>
                <button :aria-pressed="corporate" @click="corporate = true">
                    Corporate
                </button>
            </div>
        </div>

        <div class="atelier" :class="'theme-' + active">
            <aside class="app-navigation" :class="{ 'menu-open': mobileMenu }">
                <a
                    class="wordmark"
                    href="#"
                    @click.prevent="selectSection('Overview')"
                    ><span class="brand-symbol">a<span>✳</span></span
                    ><span>event<br /><em>atelier</em></span></a
                >
                <button
                    class="mobile-menu"
                    :aria-expanded="mobileMenu"
                    aria-label="Toggle workspace navigation"
                    @click="mobileMenu = !mobileMenu"
                >
                    {{ mobileMenu ? "Close −" : "Menu +" }}
                </button>
                <div class="workspace-label">
                    <span class="eyebrow">YOUR WORKSPACE</span
                    ><span
                        >{{ event.name }}
                        <span aria-hidden="true">⌄</span></span
                    >
                </div>
                <nav class="workspace-nav" aria-label="Workspace">
                    <button
                        v-for="(tab, index) in tabs"
                        :key="tab"
                        :aria-current="section === tab ? 'page' : undefined"
                        @click="selectSection(tab)"
                    >
                        <span class="nav-icon" aria-hidden="true">{{
                            ["◫", "☷", "◇", "◷", "↗"][index]
                        }}</span
                        >{{ tab
                        }}<span v-if="tab === 'Tasks'" class="nav-count"
                            >12</span
                        >
                    </button>
                </nav>
                <div class="nav-bottom">
                    <div class="nav-note">
                        <span
                            >Made for the<br /><em>beautiful details.</em></span
                        ><span class="note-star">✳</span>
                    </div>
                    <div class="person">
                        <span class="avatar">EL</span
                        ><span>Emma Laurent<small>Event planner</small></span
                        ><span class="person-dots">···</span>
                    </div>
                </div>
            </aside>

            <div class="app-body">
                <header class="workspace-top">
                    <span
                        >My events <span class="breadcrumb">/</span>
                        {{ event.name }}</span
                    >
                    <div class="top-actions">
                        <span class="saved"><i></i> All changes saved</span
                        ><span class="avatar tiny">EL</span>
                    </div>
                </header>
                <main class="workspace-content">
                    <section class="page-heading">
                        <div>
                            <p class="eyebrow">
                                THE PLANNING ROOM
                                <span class="heading-line"></span> SEPTEMBER
                                2026
                            </p>
                            <h1>
                                {{
                                    section === "Overview"
                                        ? concept.title
                                        : section
                                }}<br v-if="section === 'Overview'" /><em
                                    v-if="section === 'Overview'"
                                    >{{ concept.emphasis }}</em
                                >
                            </h1>
                            <p class="intro">{{ concept.detail }}</p>
                        </div>
                        <button
                            class="share-button"
                            @click="selectSection('Shared page')"
                        >
                            Share planning page
                            <span aria-hidden="true">↗</span>
                        </button>
                    </section>

                    <div v-if="section === 'Overview'" class="overview-layout">
                        <section class="event-hero">
                            <div class="hero-image">
                                <img
                                    :src="
                                        corporate
                                            ? '/images/preview-estate.jpg'
                                            : '/images/preview-garden.jpg'
                                    "
                                    :alt="
                                        corporate
                                            ? 'An elegant event hall with chandeliers and arranged tables'
                                            : 'A couple holding an autumnal bouquet in the evening light'
                                    "
                                />
                                <div class="image-label">
                                    A PLACE TO COME TOGETHER
                                </div>
                                <span class="image-mark" aria-hidden="true"
                                    >a.</span
                                >
                            </div>
                            <div class="hero-copy">
                                <p class="eyebrow">{{ event.type }}</p>
                                <h2>{{ event.name }}</h2>
                                <p>{{ event.place }}</p>
                                <div class="hero-footer">
                                    <span>{{ event.date }}</span
                                    ><span>{{ event.people }}</span>
                                </div>
                                <div class="hero-monogram" aria-hidden="true">
                                    {{ event.initials }}
                                </div>
                            </div>
                            <div class="countdown">
                                <span class="eyebrow">THE COUNTDOWN</span
                                ><strong>37</strong><span>days to go</span>
                                <div class="countdown-rule"></div>
                                <em>Good things<br />are on their way.</em>
                            </div>
                        </section>

                        <section
                            class="stats-strip"
                            aria-label="Planning progress"
                        >
                            <div>
                                <span class="eyebrow">THE CHECKLIST</span>
                                <p>{{ finished }}<span> / 36</span></p>
                                <div class="mini-track">
                                    <i
                                        :style="{
                                            width: (finished / 36) * 100 + '%',
                                        }"
                                    ></i>
                                </div>
                                <small>Little steps. Big moments.</small>
                            </div>
                            <div>
                                <span class="eyebrow">THE BUDGET</span>
                                <p>£18,450</p>
                                <small
                                    ><span class="positive">£6,550</span> left
                                    of £25,000</small
                                >
                            </div>
                            <div>
                                <span class="eyebrow">YOUR PEOPLE</span>
                                <p>8<span> vendors</span></p>
                                <small>5 booked, 3 to decide</small>
                            </div>
                            <div class="team-stat">
                                <span class="eyebrow">BETTER TOGETHER</span>
                                <div class="avatar-stack">
                                    <span class="avatar">EL</span
                                    ><span class="avatar rose">OA</span
                                    ><span class="avatar olive">JM</span
                                    ><span class="avatar pale">+2</span>
                                </div>
                                <small>Your planning circle</small>
                            </div>
                        </section>

                        <section class="task-panel panel">
                            <div class="panel-heading">
                                <div>
                                    <p class="eyebrow">ONE THING AT A TIME</p>
                                    <h2>On the horizon</h2>
                                </div>
                                <button
                                    class="text-link"
                                    @click="selectSection('Tasks')"
                                >
                                    All tasks ↗
                                </button>
                            </div>
                            <div class="task-list">
                                <label
                                    v-for="task in tasks.slice(0, 3)"
                                    :key="task.id"
                                    class="task-row"
                                    :class="{ completed: task.done }"
                                    ><input
                                        type="checkbox"
                                        :checked="task.done"
                                        @change="toggleTask(task)"
                                    /><span class="task-title"
                                        >{{
                                            corporate
                                                ? task.corporate
                                                : task.wedding
                                        }}<small>{{
                                            task.category
                                        }}</small></span
                                    ><span
                                        class="due"
                                        :class="{ today: task.due === 'Today' }"
                                        >{{
                                            task.done ? "Done" : task.due
                                        }}</span
                                    ></label
                                >
                            </div>
                            <p class="panel-footnote">
                                {{
                                    tasks.filter((task) => !task.done).length
                                }}
                                small decisions closer to the day.
                            </p>
                        </section>

                        <section class="vendor-panel panel">
                            <div class="panel-heading">
                                <div>
                                    <p class="eyebrow">THE PEOPLE BEHIND IT</p>
                                    <h2>A lovely little team</h2>
                                </div>
                                <button
                                    class="text-link"
                                    @click="selectSection('Vendors')"
                                >
                                    Explore ↗
                                </button>
                            </div>
                            <div
                                v-for="vendor in vendors.slice(0, 2)"
                                :key="vendor.name"
                                class="vendor-row"
                            >
                                <span class="vendor-monogram"
                                    >{{ vendor.letter }}<span>✳</span></span
                                >
                                <div>
                                    <h3>{{ vendor.name }}</h3>
                                    <small>{{ vendor.type }}</small>
                                </div>
                                <span
                                    class="vendor-status"
                                    :class="{
                                        booked: vendor.status === 'Booked',
                                    }"
                                    >{{ vendor.status }}</span
                                >
                            </div>
                            <button
                                class="compare-link"
                                @click="selectSection('Vendors')"
                            >
                                A considered choice starts with a comparison
                                <span>↗</span>
                            </button>
                        </section>

                        <section class="journal-note">
                            <span class="eyebrow">A NOTE TO YOURSELF</span>
                            <p>
                                “The best gatherings are the ones<br />that feel
                                like <em>you.</em>”
                            </p>
                            <span class="note-signature"
                                >Keep a little room for the unexpected.</span
                            ><span class="journal-star" aria-hidden="true"
                                >✳</span
                            >
                        </section>
                        <section class="timeline-panel">
                            <p class="eyebrow">THE ROAD TO OCTOBER</p>
                            <div class="timeline-items">
                                <div>
                                    <span>01</span>
                                    <p>
                                        The foundations<small
                                            >Venue, date, your people</small
                                        >
                                    </p>
                                    <b>Complete</b>
                                </div>
                                <div>
                                    <span>02</span>
                                    <p>
                                        The finer details<small
                                            >Make it feel like you</small
                                        >
                                    </p>
                                    <b>In progress</b>
                                </div>
                                <div>
                                    <span>03</span>
                                    <p>
                                        The final touches<small
                                            >Then, enjoy every moment</small
                                        >
                                    </p>
                                    <b>Coming up</b>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section
                        v-else-if="section === 'Tasks'"
                        class="detail-panel panel"
                    >
                        <div class="panel-heading">
                            <h2>The little things, all together.</h2>
                            <span class="pill"
                                >{{ finished }} of 36 complete</span
                            >
                        </div>
                        <label
                            v-for="task in tasks"
                            :key="task.id"
                            class="task-row"
                            :class="{ completed: task.done }"
                            ><input
                                type="checkbox"
                                :checked="task.done"
                                @change="toggleTask(task)"
                            /><span class="task-title"
                                >{{ corporate ? task.corporate : task.wedding
                                }}<small>{{ task.category }}</small></span
                            ><span class="due">{{
                                task.done ? "Done" : task.due
                            }}</span></label
                        >
                        <p class="panel-footnote">
                            Try checking off a task. Changes are only kept while
                            this preview is open.
                        </p>
                    </section>
                    <section
                        v-else-if="section === 'Vendors'"
                        class="detail-panel panel"
                    >
                        <div class="panel-heading">
                            <h2>Your considered shortlist.</h2>
                            <span class="pill">3 sample vendors</span>
                        </div>
                        <div class="vendor-comparison">
                            <article
                                v-for="vendor in vendors"
                                :key="vendor.name"
                            >
                                <span class="vendor-monogram">{{
                                    vendor.letter
                                }}</span>
                                <p class="eyebrow">{{ vendor.type }}</p>
                                <h3>{{ vendor.name }}</h3>
                                <p class="vendor-price">{{ vendor.quote }}</p>
                                <p class="vendor-status">{{ vendor.status }}</p>
                                <small>Sample quote · includes setup</small>
                            </article>
                        </div>
                    </section>
                    <section
                        v-else-if="section === 'Budget'"
                        class="detail-panel panel"
                    >
                        <div class="panel-heading">
                            <h2>Room for what matters.</h2>
                            <span class="pill">£25,000 planned</span>
                        </div>
                        <div class="budget-total">
                            £18,450 <small>committed · £6,550 remaining</small>
                        </div>
                        <div
                            v-for="item in [
                                {
                                    name: 'Venue',
                                    amount: '£8,500',
                                    percent: 85,
                                },
                                {
                                    name: 'Food & drink',
                                    amount: '£4,200',
                                    percent: 60,
                                },
                                {
                                    name: 'Design & experience',
                                    amount: '£5,750',
                                    percent: 72,
                                },
                            ]"
                            :key="item.name"
                            class="budget-row"
                        >
                            <div>
                                <span>{{ item.name }}</span
                                ><strong>{{ item.amount }}</strong>
                            </div>
                            <div class="mini-track">
                                <i :style="{ width: item.percent + '%' }"></i>
                            </div>
                        </div>
                    </section>
                    <section v-else class="share-preview panel">
                        <p class="eyebrow">YOUR SHARED PLANNING PAGE</p>
                        <span class="share-ornament" aria-hidden="true">✳</span>
                        <h2>{{ event.name }}</h2>
                        <p>{{ event.date }} · {{ event.place }}</p>
                        <hr />
                        <h3>Something lovely is coming together.</h3>
                        <p>
                            A place for your people to see the plans, the
                            details,<br />and everything they need to feel part
                            of it.
                        </p>
                        <span class="pill">Read-only page · Budget hidden</span>
                        <p class="panel-footnote">
                            Design preview only. No public link has been
                            created.
                        </p>
                    </section>
                    <footer class="workspace-footer">
                        <span
                            >EVENT ATELIER <span>—</span> PLANNED WITH
                            CARE.</span
                        ><span
                            >Every detail, together.
                            <span aria-hidden="true">✳</span></span
                        >
                    </footer>
                </main>
            </div>
        </div>
        <div v-if="message" class="preview-toast" role="status">
            <span>{{ message }}</span
            ><button aria-label="Dismiss notification" @click="message = ''">
                ×
            </button>
        </div>
        <footer class="lab-footer">
            <p>Five directions. One beautifully considered gathering.</p>
            <span
                >Preview content only · Choose a direction or mix your favourite
                elements.</span
            >
        </footer>
    </div>
</template>
