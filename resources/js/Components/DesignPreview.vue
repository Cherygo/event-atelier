<script setup>
import { computed, nextTick, reactive, ref, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AtelierBrand from './AtelierBrand.vue';
import AtelierIcon from './AtelierIcon.vue';
import { demoEvents, money } from '../atelierDemo';

const page = usePage();
const params = new URLSearchParams(page.url.split('?')[1] || '');
const events = reactive(structuredClone(demoEvents));
const selectedEvent = ref(
    params.get('event') === 'corporate' ? 'corporate' : 'wedding',
);
const navItems = [
    { id: 'overview', title: 'Overview', icon: 'overview' },
    { id: 'tasks', title: 'Tasks', icon: 'tasks' },
    { id: 'vendors', title: 'Vendors', icon: 'vendors' },
    { id: 'budget', title: 'Budget', icon: 'budget' },
    { id: 'share', title: 'Shared page', icon: 'share' },
];
const section = ref(
    navItems.some((item) => item.id === params.get('section'))
        ? params.get('section')
        : 'overview',
);
const event = computed(() =>
    events.find((item) => item.id === selectedEvent.value),
);
const currentSection = computed(() =>
    navItems.find((item) => item.id === section.value),
);
const completed = computed(
    () => event.value.tasks.filter((task) => task.done).length,
);
const committed = computed(() =>
    event.value.expenses.reduce((sum, item) => sum + item.amount, 0),
);
const paid = computed(() =>
    event.value.expenses.reduce((sum, item) => sum + item.paid, 0),
);
const upcoming = computed(() =>
    event.value.tasks.filter((task) => !task.done).slice(0, 3),
);
const menuOpen = ref(false);
const feedback = ref('');
const taskFilter = ref('all');
const search = ref('');
const visibleTasks = computed(() =>
    event.value.tasks.filter(
        (task) =>
            (taskFilter.value === 'all' ||
                (taskFilter.value === 'done' ? task.done : !task.done)) &&
            task.title.toLowerCase().includes(search.value.toLowerCase()),
    ),
);
const addTaskOpen = ref(false);
const taskTitle = ref('');
const taskOwner = ref('');
const taskError = ref('');
const newTaskInput = ref(null);
const compareIds = ref([]);
const compared = computed(() =>
    event.value.vendors.filter((vendor) =>
        compareIds.value.includes(vendor.id),
    ),
);
const compareCategory = ref('');
const vendorFilter = ref('All vendors');
const filteredVendors = computed(() =>
    event.value.vendors.filter(
        (vendor) =>
            vendorFilter.value === 'All vendors' ||
            vendor.category === vendorFilter.value,
    ),
);
const categories = computed(() => [
    ...new Set(event.value.vendors.map((vendor) => vendor.category)),
]);
const shareOptions = reactive({
    wedding: { location: true, vendors: true, tasks: false, budget: false },
    corporate: { location: true, vendors: true, tasks: false, budget: false },
});
const sharing = computed(() => shareOptions[selectedEvent.value]);
const sharedVendors = computed(() =>
    event.value.vendors.filter((vendor) => vendor.status === 'Booked'),
);

watch(selectedEvent, () => {
    taskFilter.value = 'all';
    search.value = '';
    addTaskOpen.value = false;
    taskTitle.value = '';
    taskOwner.value = '';
    taskError.value = '';
    compareIds.value = [];
    compareCategory.value = '';
    vendorFilter.value = 'All vendors';
    feedback.value = '';
});
function navigate(id) {
    section.value = id;
    menuOpen.value = false;
    feedback.value = '';
}
async function showTaskForm() {
    section.value = 'tasks';
    addTaskOpen.value = true;
    await nextTick();
    newTaskInput.value?.focus();
}
function addTask() {
    const title = taskTitle.value.trim();
    if (!title) {
        taskError.value = 'Give your task a name before adding it.';
        newTaskInput.value?.focus();
        return;
    }
    event.value.tasks.push({
        id: Math.max(...event.value.tasks.map((task) => task.id)) + 1,
        title,
        owner: taskOwner.value || event.value.people[0],
        category: 'Planning',
        due: 'No due date',
        done: false,
    });
    taskTitle.value = '';
    taskOwner.value = '';
    taskError.value = '';
    addTaskOpen.value = false;
    taskFilter.value = 'all';
    search.value = '';
    feedback.value =
        'Task added to this sample. Changes last until you leave or reload the workspace.';
}
function toggleTask(task) {
    task.done = !task.done;
    feedback.value = task.done
        ? 'Task completed in this sample.'
        : 'Task reopened in this sample.';
}
function toggleComparison(vendor, changeEvent) {
    if (compareIds.value.includes(vendor.id)) {
        compareIds.value = compareIds.value.filter((id) => id !== vendor.id);
        if (!compareIds.value.length) compareCategory.value = '';
    } else {
        if (
            compareCategory.value &&
            compareCategory.value !== vendor.category
        ) {
            feedback.value =
                'Choose vendors in the same category for a useful comparison. Clear your selection to change category.';
            changeEvent.target.checked = false;
            return;
        }
        compareCategory.value = vendor.category;
        compareIds.value.push(vendor.id);
        feedback.value = '';
    }
}
function toggleShortlist(vendor) {
    vendor.status =
        vendor.status === 'Shortlisted' ? 'Considering' : 'Shortlisted';
    feedback.value =
        vendor.name +
        (vendor.status === 'Shortlisted'
            ? ' added to your sample shortlist.'
            : ' removed from your sample shortlist.');
}
</script>

<template>
    <div class="ea-workspace">
        <Head :title="currentSection.title + ' · ' + event.name" />
        <a href="#workspace-main" class="ea-skip">Skip to workspace</a>
        <aside class="ea-sidebar">
            <div class="ea-sidebar-top">
                <AtelierBrand /><button
                    class="ea-mobile-toggle ea-icon-button"
                    :aria-label="
                        menuOpen
                            ? 'Close workspace navigation'
                            : 'Open workspace navigation'
                    "
                    :aria-expanded="menuOpen"
                    aria-controls="workspace-navigation"
                    @click="menuOpen = !menuOpen"
                >
                    <AtelierIcon :name="menuOpen ? 'close' : 'menu'" />
                </button>
            </div>
            <div
                id="workspace-navigation"
                class="ea-sidebar-content"
                :class="{ 'is-open': menuOpen }"
            >
                <label class="ea-event-switch"
                    ><span>Your occasion</span
                    ><select
                        v-model="selectedEvent"
                        aria-label="Switch sample event"
                    >
                        <option
                            v-for="item in events"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.name }}
                        </option>
                    </select></label
                >
                <nav aria-label="Workspace">
                    <button
                        v-for="item in navItems"
                        :key="item.id"
                        :aria-current="section === item.id ? 'page' : undefined"
                        @click="navigate(item.id)"
                    >
                        <AtelierIcon :name="item.icon" /><span>{{
                            item.title
                        }}</span
                        ><span
                            v-if="item.id === 'tasks'"
                            class="ea-nav-count"
                            >{{
                                event.tasks.filter((task) => !task.done).length
                            }}</span
                        >
                    </button>
                </nav>
                <div class="ea-sidebar-bottom">
                    <p>Made for<br /><em>the beautiful details.</em></p>
                    <Link href="/" class="ea-sidebar-home"
                        ><AtelierIcon name="back" /> Back to Event Atelier</Link
                    >
                    <div class="ea-demo-person">
                        <span class="ea-avatar">EL</span>
                        <div>Emma Laurent<small>Sample planner</small></div>
                        <span class="ea-tag">Owner</span>
                    </div>
                </div>
            </div>
        </aside>

        <div class="ea-workspace-body">
            <div class="ea-preview-notice" role="note" aria-label="Demo workspace notice">
                <span
                    ><span class="ea-status-dot"></span><span><strong>Demo mode</strong> — changes disappear when you leave or reload.</span></span
                ><Link :href="route('register')"
                    >Create your own <AtelierIcon name="diagonal"
                /></Link>
            </div>
            <main id="workspace-main" class="ea-workspace-main">
                <header class="ea-workspace-heading">
                    <div>
                        <p class="ea-breadcrumb">
                            {{ event.name }} <span>/</span>
                            {{ currentSection.title }}
                        </p>
                        <h1>
                            {{
                                section === 'overview'
                                    ? 'Your day, taking shape.'
                                    : currentSection.title
                            }}
                        </h1>
                    </div>
                    <div class="ea-workspace-actions">
                        <button
                            v-if="section === 'overview' || section === 'tasks'"
                            class="ea-button ea-button-outline"
                            @click="showTaskForm"
                        >
                            <AtelierIcon name="plus" /> Add a task</button
                        ><button
                            v-if="section !== 'share'"
                            class="ea-button"
                            @click="navigate('share')"
                        >
                            Preview shared page <AtelierIcon name="share" />
                        </button>
                    </div>
                </header>
                <Transition name="ea-feedback">
                    <p v-if="feedback" class="ea-feedback" role="status">
                        {{ feedback
                        }}<button
                            class="ea-icon-button"
                            aria-label="Dismiss message"
                            @click="feedback = ''"
                        >
                            <AtelierIcon name="close" />
                        </button>
                    </p>
                </Transition>

                <template v-if="section === 'overview'">
                    <section class="ea-event-banner">
                        <img
                            :src="event.image"
                            :alt="event.imageAlt"
                            width="1400"
                            height="933"
                        />
                        <div>
                            <h2>{{ event.name }}</h2>
                            <p><AtelierIcon name="pin" /> {{ event.place }}</p>
                            <span class="ea-event-type">{{ event.type }}</span>
                        </div>
                        <div class="ea-event-date">
                            <AtelierIcon name="calendar" /><strong>{{
                                event.date
                            }}</strong
                            ><span
                                >{{ event.guests }}
                                {{
                                    event.id === 'wedding'
                                        ? 'guests'
                                        : 'attendees'
                                }}</span
                            >
                        </div>
                    </section>
                    <div class="ea-workspace-summary">
                        <div>
                            <span>Checklist</span
                            ><strong
                                >{{ completed }}
                                <small
                                    >of {{ event.tasks.length }} complete</small
                                ></strong
                            ><progress
                                :value="completed"
                                :max="event.tasks.length"
                                :aria-label="
                                    completed +
                                    ' of ' +
                                    event.tasks.length +
                                    ' tasks complete'
                                "
                            ></progress>
                        </div>
                        <div>
                            <span>Budget available</span
                            ><strong>{{
                                money(event.budget - committed)
                            }}</strong
                            ><small>of {{ money(event.budget) }} planned</small>
                        </div>
                        <div>
                            <span>Your vendors</span
                            ><strong
                                >{{
                                    event.vendors.filter(
                                        (vendor) => vendor.status === 'Booked',
                                    ).length
                                }}
                                <small>booked</small></strong
                            ><small
                                >{{
                                    event.vendors.filter(
                                        (vendor) =>
                                            vendor.status === 'Shortlisted',
                                    ).length
                                }}
                                on the shortlist</small
                            >
                        </div>
                        <div>
                            <span>Your planning circle</span>
                            <div class="ea-avatar-group">
                                <span
                                    v-for="(person, index) in event.people"
                                    :key="person"
                                    class="ea-avatar"
                                    :class="'tone-' + index"
                                    :title="person"
                                    >{{ person.slice(0, 1) }}</span
                                >
                            </div>
                            <small>{{ event.people.join(', ') }}</small>
                        </div>
                    </div>
                    <div class="ea-dashboard-columns">
                        <section class="ea-up-next">
                            <div class="ea-panel-heading">
                                <h2>On the horizon</h2>
                                <button
                                    class="ea-text-link"
                                    @click="navigate('tasks')"
                                >
                                    All tasks <AtelierIcon name="arrow" />
                                </button>
                            </div>
                            <p class="ea-panel-description">
                                A few small steps towards the big day.
                            </p>
                            <label
                                v-for="task in upcoming"
                                :key="task.id"
                                class="ea-task-row"
                                ><input
                                    type="checkbox"
                                    :checked="task.done"
                                    @change="toggleTask(task)"
                                /><span class="ea-task-copy"
                                    >{{ task.title
                                    }}<small
                                        >{{ task.category }} ·
                                        {{ task.owner }}</small
                                    ></span
                                ><span
                                    class="ea-tag"
                                    :class="{
                                        'ea-tag-rose': task.due === 'Today',
                                    }"
                                    >{{ task.due }}</span
                                ></label
                            >
                            <div v-if="!upcoming.length" class="ea-empty">
                                <AtelierIcon name="check" />
                                <h3>A little breathing room.</h3>
                                <p>
                                    Your sample checklist is complete. Add a
                                    task whenever you need one.
                                </p>
                                <button
                                    class="ea-text-link"
                                    @click="showTaskForm"
                                >
                                    Add a task <AtelierIcon name="plus" />
                                </button>
                            </div>
                            <button
                                v-else
                                class="ea-add-line"
                                @click="showTaskForm"
                            >
                                <AtelierIcon name="plus" /> Add a little detail
                            </button>
                        </section>
                        <section class="ea-budget-glance">
                            <div class="ea-panel-heading">
                                <h2>A considered budget</h2>
                                <AtelierIcon name="budget" />
                            </div>
                            <dl>
                                <div>
                                    <dt>Planned</dt>
                                    <dd>{{ money(event.budget) }}</dd>
                                </div>
                                <div>
                                    <dt>Committed</dt>
                                    <dd>{{ money(committed) }}</dd>
                                </div>
                                <div>
                                    <dt>Paid so far</dt>
                                    <dd>{{ money(paid) }}</dd>
                                </div>
                            </dl>
                            <div class="ea-budget-next">
                                <span>Next payment</span
                                ><strong
                                    >{{
                                        money(
                                            event.expenses[0].amount -
                                                event.expenses[0].paid,
                                        )
                                    }}
                                    <small
                                        >· {{ event.expenses[0].due }}</small
                                    ></strong
                                ><span>{{ event.expenses[0].name }}</span>
                            </div>
                            <button
                                class="ea-text-link"
                                @click="navigate('budget')"
                            >
                                See the full budget <AtelierIcon name="arrow" />
                            </button>
                        </section>
                    </div>
                    <section class="ea-vendor-glance">
                        <div class="ea-panel-heading">
                            <h2>The people behind it</h2>
                            <button
                                class="ea-text-link"
                                @click="navigate('vendors')"
                            >
                                Compare vendors <AtelierIcon name="arrow" />
                            </button>
                        </div>
                        <div class="ea-vendor-glance-list">
                            <article
                                v-for="vendor in event.vendors.slice(0, 3)"
                                :key="vendor.id"
                            >
                                <span class="ea-vendor-initial">{{
                                    vendor.initial
                                }}</span>
                                <div>
                                    <h3>{{ vendor.name }}</h3>
                                    <p>{{ vendor.category }}</p>
                                </div>
                                <span
                                    class="ea-tag"
                                    :class="{
                                        'ea-tag-green':
                                            vendor.status === 'Booked',
                                    }"
                                    >{{ vendor.status }}</span
                                >
                            </article>
                        </div>
                    </section>
                </template>

                <section v-else-if="section === 'tasks'" class="ea-tasks-view">
                    <p class="ea-view-description">
                        Every little detail, with a person and a next step.
                        {{ completed }} of {{ event.tasks.length }} tasks
                        complete.
                    </p>
                    <form
                        v-if="addTaskOpen"
                        class="ea-inline-form"
                        @submit.prevent="addTask"
                    >
                        <h2>A new detail</h2>
                        <div class="ea-form-grid">
                            <label
                                >Task name<input
                                    ref="newTaskInput"
                                    v-model="taskTitle"
                                    type="text"
                                    maxlength="120"
                                    placeholder="What needs to happen?"
                                    :aria-invalid="!!taskError"
                                    :aria-describedby="
                                        taskError ? 'task-error' : undefined
                                    " /></label
                            ><label
                                >Assigned to<select v-model="taskOwner">
                                    <option value="">Choose a person</option>
                                    <option
                                        v-for="person in event.people"
                                        :key="person"
                                    >
                                        {{ person }}
                                    </option>
                                </select></label
                            >
                        </div>
                        <p
                            v-if="taskError"
                            id="task-error"
                            class="ea-error"
                            role="alert"
                        >
                            {{ taskError }}
                        </p>
                        <div class="ea-form-actions">
                            <button class="ea-button" type="submit">
                                Add to checklist
                                <AtelierIcon name="plus" /></button
                            ><button
                                type="button"
                                class="ea-text-link"
                                @click="
                                    addTaskOpen = false;
                                    taskError = '';
                                "
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                    <div class="ea-task-tools">
                        <div class="ea-segmented" aria-label="Filter tasks">
                            <button
                                v-for="filter in [
                                    { id: 'all', name: 'All tasks' },
                                    { id: 'open', name: 'To do' },
                                    { id: 'done', name: 'Completed' },
                                ]"
                                :key="filter.id"
                                :aria-pressed="taskFilter === filter.id"
                                @click="taskFilter = filter.id"
                            >
                                {{ filter.name }}
                            </button>
                        </div>
                        <label class="ea-search"
                            ><AtelierIcon name="search" /><input
                                v-model="search"
                                type="search"
                                aria-label="Search tasks"
                                placeholder="Find a task"
                        /></label>
                    </div>
                    <TransitionGroup
                        name="ea-task-shift"
                        tag="div"
                        class="ea-task-table"
                    >
                        <label
                            v-for="task in visibleTasks"
                            :key="task.id"
                            class="ea-task-row"
                            :class="{ 'is-complete': task.done }"
                            ><input
                                type="checkbox"
                                :checked="task.done"
                                @change="toggleTask(task)"
                            /><span class="ea-task-copy"
                                >{{ task.title
                                }}<small>{{ task.category }}</small></span
                            ><span class="ea-task-owner">{{ task.owner }}</span
                            ><span
                                class="ea-tag"
                                :class="{
                                    'ea-tag-rose':
                                        task.due === 'Today' && !task.done,
                                }"
                                >{{ task.done ? 'Completed' : task.due }}</span
                            ></label
                        >
                    </TransitionGroup>
                    <div v-if="!visibleTasks.length" class="ea-empty">
                        <AtelierIcon name="tasks" />
                        <h2>
                            {{
                                search
                                    ? 'No matching details.'
                                    : 'Nothing on this list yet.'
                            }}
                        </h2>
                        <p>
                            {{
                                search
                                    ? 'Try a different word, or clear your search.'
                                    : 'Add a task or switch filters to see the rest of your plan.'
                            }}
                        </p>
                        <button
                            class="ea-text-link"
                            @click="
                                search = '';
                                taskFilter = 'all';
                            "
                        >
                            Show all tasks <AtelierIcon name="arrow" />
                        </button>
                    </div>
                </section>

                <section
                    v-else-if="section === 'vendors'"
                    class="ea-vendors-view"
                >
                    <p class="ea-view-description">
                        A thoughtful shortlist makes the decision a little
                        easier. Select vendors in the same category to compare
                        them.
                    </p>
                    <div class="ea-task-tools">
                        <label class="ea-select-label"
                            >Category<select v-model="vendorFilter">
                                <option>All vendors</option>
                                <option
                                    v-for="category in categories"
                                    :key="category"
                                >
                                    {{ category }}
                                </option>
                            </select></label
                        ><button
                            v-if="compareIds.length"
                            class="ea-text-link"
                            @click="
                                compareIds = [];
                                compareCategory = '';
                                feedback = '';
                            "
                        >
                            Clear comparison ({{ compareIds.length }})
                            <AtelierIcon name="close" />
                        </button>
                    </div>
                    <div class="ea-vendor-directory">
                        <article
                            v-for="vendor in filteredVendors"
                            :key="vendor.id"
                        >
                            <div class="ea-vendor-identity">
                                <span class="ea-vendor-initial">{{
                                    vendor.initial
                                }}</span>
                                <div>
                                    <h2>{{ vendor.name }}</h2>
                                    <span>{{ vendor.category }}</span>
                                </div>
                                <span
                                    class="ea-tag"
                                    :class="{
                                        'ea-tag-green':
                                            vendor.status === 'Booked',
                                    }"
                                    >{{ vendor.status }}</span
                                >
                            </div>
                            <p>{{ vendor.detail }}</p>
                            <div class="ea-vendor-price">
                                <span>Sample quote</span
                                ><strong>{{ money(vendor.price) }}</strong>
                            </div>
                            <div class="ea-vendor-actions">
                                <label
                                    ><input
                                        type="checkbox"
                                        :checked="
                                            compareIds.includes(vendor.id)
                                        "
                                        :aria-label="`Compare ${vendor.name}`"
                                        @change="
                                            toggleComparison(vendor, $event)
                                        "
                                    />
                                    Compare</label
                                ><button
                                    v-if="vendor.status !== 'Booked'"
                                    class="ea-text-link"
                                    @click="toggleShortlist(vendor)"
                                >
                                    {{
                                        vendor.status === 'Shortlisted'
                                            ? 'Remove from shortlist'
                                            : 'Add to shortlist'
                                    }}</button
                                ><span v-else class="ea-booked-note"
                                    ><AtelierIcon name="check" /> Booking
                                    confirmed</span
                                >
                            </div>
                        </article>
                    </div>
                    <section v-if="compared.length" class="ea-comparison">
                        <h2>
                            Your {{ compareCategory.toLowerCase() }} comparison
                        </h2>
                        <p v-if="compared.length === 1">
                            Select another
                            {{ compareCategory.toLowerCase() }} vendor to see
                            the options together.
                        </p>
                        <div class="ea-comparison-grid">
                            <article
                                v-for="vendor in compared"
                                :key="vendor.id"
                            >
                                <h3>{{ vendor.name }}</h3>
                                <strong>{{ money(vendor.price) }}</strong>
                                <p>{{ vendor.detail }}</p>
                                <span class="ea-tag">{{ vendor.status }}</span>
                            </article>
                        </div>
                    </section>
                </section>

                <section
                    v-else-if="section === 'budget'"
                    class="ea-budget-view"
                >
                    <p class="ea-view-description">
                        The big picture, without losing the small print. All
                        amounts in GBP.
                    </p>
                    <div class="ea-budget-summary">
                        <div>
                            <span>Planned budget</span
                            ><strong>{{ money(event.budget) }}</strong>
                        </div>
                        <div>
                            <span>Committed</span
                            ><strong>{{ money(committed) }}</strong>
                        </div>
                        <div>
                            <span>Paid so far</span
                            ><strong>{{ money(paid) }}</strong>
                        </div>
                        <div>
                            <span>Still available</span
                            ><strong>{{
                                money(event.budget - committed)
                            }}</strong>
                        </div>
                    </div>
                    <div
                        class="ea-table-scroll"
                        role="region"
                        tabindex="0"
                        aria-label="Expense breakdown, scroll horizontally on small screens"
                    >
                        <table>
                            <caption>
                                Where the budget goes
                            </caption>
                            <thead>
                                <tr>
                                    <th scope="col">Category</th>
                                    <th scope="col">Committed</th>
                                    <th scope="col">Paid</th>
                                    <th scope="col">Remaining</th>
                                    <th scope="col">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="expense in event.expenses"
                                    :key="expense.name"
                                >
                                    <th scope="row">{{ expense.name }}</th>
                                    <td>{{ money(expense.amount) }}</td>
                                    <td>{{ money(expense.paid) }}</td>
                                    <td>
                                        {{
                                            money(expense.amount - expense.paid)
                                        }}
                                    </td>
                                    <td>{{ expense.due }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="row">Total</th>
                                    <td>{{ money(committed) }}</td>
                                    <td>{{ money(paid) }}</td>
                                    <td>{{ money(committed - paid) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="ea-view-footnote">
                        Sample expenses and payment dates. No payments are
                        processed in this preview.
                    </p>
                </section>

                <section v-else class="ea-sharing-view">
                    <div class="ea-share-controls">
                        <h2>A little more together.</h2>
                        <p>
                            Choose what your people would see. The preview
                            updates as you make changes.
                        </p>
                        <fieldset>
                            <legend>Include on the shared page</legend>
                            <label
                                v-for="option in [
                                    {
                                        id: 'location',
                                        name: 'Venue & location',
                                    },
                                    { id: 'vendors', name: 'Booked vendors' },
                                    { id: 'tasks', name: 'Planning checklist' },
                                    { id: 'budget', name: 'Budget summary' },
                                ]"
                                :key="option.id"
                                ><input
                                    v-model="sharing[option.id]"
                                    type="checkbox"
                                />{{ option.name }}</label
                            >
                        </fieldset>
                        <p class="ea-privacy-note">
                            <AtelierIcon name="lock" /> This is a read-only
                            sample preview. No public link is created.
                        </p>
                    </div>
                    <article class="ea-shared-invitation">
                        <span class="ea-tag">Shared page preview</span
                        ><span
                            class="ea-invitation-monogram"
                            aria-hidden="true"
                            >{{ event.initial }}</span
                        >
                        <h2>{{ event.name }}</h2>
                        <p>{{ event.date }}</p>
                        <p v-if="sharing.location">{{ event.place }}</p>
                        <div class="ea-shared-welcome">
                            <h3>Something lovely is coming together.</h3>
                            <p>
                                A place for our people to follow the plans and
                                feel part of the occasion.
                            </p>
                        </div>
                        <section v-if="sharing.vendors">
                            <h3>The people behind it</h3>
                            <p v-for="vendor in sharedVendors" :key="vendor.id">
                                {{ vendor.name }} · {{ vendor.category }}
                            </p>
                        </section>
                        <section v-if="sharing.tasks">
                            <h3>The planning checklist</h3>
                            <p v-for="task in event.tasks" :key="task.id">
                                {{ task.title }} —
                                {{ task.done ? 'Completed' : 'To do' }}
                            </p>
                        </section>
                        <section v-if="sharing.budget">
                            <h3>Budget summary</h3>
                            <p>
                                {{ money(committed) }} committed of
                                {{ money(event.budget) }} planned
                            </p>
                        </section>
                        <span class="ea-shared-signature"
                            >Planned with Event Atelier</span
                        >
                    </article>
                </section>
                <footer class="ea-workspace-footer">
                    <span>Every detail, together.</span
                    ><span>Illustrative data · 20 September 2026</span>
                </footer>
            </main>
        </div>
    </div>
</template>
