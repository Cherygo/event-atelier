<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import EventLayout from '@/Layouts/EventLayout.vue';
import EventTaskEditor from '@/Components/EventTaskEditor.vue';
import EventTaskRow from '@/Components/EventTaskRow.vue';

const props = defineProps({ event: Object, tasks: Object, filters: Object, canEdit: Boolean, today: String });
const adding = ref(false);
const addButton = ref(null);
const heading = ref(null);
const search = useForm({ search: props.filters.search, status: props.filters.status, due: props.filters.due, sort: props.filters.sort });
const statuses = { active: 'Active', todo: 'To do', in_progress: 'In progress', completed: 'Completed', all: 'All tasks' };
const applyFilters = () => search.get(route('events.tasks.index', props.event.id), { preserveState: true, preserveScroll: true, replace: true });
const openEditor = async () => { adding.value = true; await nextTick(); document.getElementById('task-new-title')?.focus(); };
const closeEditor = async () => { adding.value = false; await nextTick(); addButton.value?.focus(); };
const focusList = async () => { await nextTick(); heading.value?.focus(); };
</script>

<template>
    <EventLayout :event="event">
        <Head :title="'Tasks · ' + event.name" />
        <template #description><p>A shared list for every next step.</p></template>
        <section class="ea-event-section" aria-labelledby="task-list-heading">
            <div class="ea-task-toolbar">
                <div><h2 id="task-list-heading" ref="heading" tabindex="-1">Tasks <span class="ea-section-count">{{ tasks.total }}</span></h2><p v-if="!canEdit" class="ea-task-readonly">You have read-only access to this plan.</p></div>
                <button v-if="canEdit && !adding" ref="addButton" type="button" class="ea-button" @click="openEditor">Add task</button>
            </div>
            <EventTaskEditor v-if="adding" :event-id="event.id" @saved="closeEditor" @cancel="closeEditor" />
            <div class="ea-task-status-filters" role="group" aria-label="Filter tasks by status">
                <button v-for="(label, value) in statuses" :key="value" type="button" :aria-pressed="filters.status === value" :disabled="search.processing" @click="search.status = value; applyFilters()">{{ label }}</button>
            </div>
            <form class="ea-task-search" role="search" @submit.prevent="applyFilters">
                <label class="sr-only" for="task-search">Search tasks</label>
                <input id="task-search" v-model="search.search" type="search" maxlength="180" placeholder="Search tasks" />
                <label class="ea-task-filter-field">Deadline<select v-model="search.due"><option value="all">Any date</option><option value="overdue">Overdue</option><option value="today">Due today</option><option value="upcoming">Next 7 days</option><option value="unscheduled">No due date</option></select></label>
                <label class="ea-task-filter-field">Sort by<select v-model="search.sort"><option value="newest">Newest first</option><option value="due">Due date</option></select></label>
                <button class="ea-button ea-button-outline" :disabled="search.processing">Search</button>
                <Link v-if="filters.search || filters.due !== 'all' || filters.sort !== 'newest'" :href="route('events.tasks.index', event.id)" class="ea-text-link">Clear filters</Link>
            </form>
            <TransitionGroup v-if="tasks.data.length" name="ea-live-task" tag="ul" class="ea-live-task-list">
                <EventTaskRow v-for="task in tasks.data" :key="task.id" :task="task" :event-id="event.id" :can-edit="canEdit" :today="today" @removed="focusList" />
            </TransitionGroup>
            <div v-else class="ea-task-empty">
                <h3>{{ tasks.total ? 'No tasks on this page.' : 'No tasks in this view yet.' }}</h3>
                <p>{{ filters.search ? 'Try another phrase or clear the search.' : 'Choose another status to see more tasks, or add the next step to your plan.' }}</p>
            </div>
            <nav v-if="tasks.last_page > 1" class="ea-task-pagination" aria-label="Task pages">
                <Link v-if="tasks.prev_page_url" :href="tasks.prev_page_url" class="ea-text-link" preserve-scroll>Previous</Link>
                <span>Page {{ tasks.current_page }} of {{ tasks.last_page }}</span>
                <Link v-if="tasks.next_page_url" :href="tasks.next_page_url" class="ea-text-link" preserve-scroll>Next</Link>
            </nav>
        </section>
    </EventLayout>
</template>
