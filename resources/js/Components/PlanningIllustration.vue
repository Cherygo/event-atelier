<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AtelierIcon from './AtelierIcon.vue';
import { money } from '../atelierDemo';
const props = defineProps({ event: { type: Object, required: true } });
const committed = computed(() =>
    props.event.expenses.reduce((total, item) => total + item.amount, 0),
);
</script>

<template>
    <div class="ea-plan-sample">
        <div class="ea-sample-side">
            <span class="ea-brand-mark" aria-hidden="true">a.</span
            ><span>One occasion.<br /><em>All the details.</em></span
            ><AtelierIcon name="leaf" />
        </div>
        <div class="ea-sample-content">
            <div class="ea-sample-title">
                <div>
                    <h3>{{ event.name }}</h3>
                    <p>{{ event.date }} · {{ event.place }}</p>
                </div>
                <span class="ea-tag">Sample plan</span>
            </div>
            <div class="ea-sample-columns">
                <section>
                    <h4>Up next</h4>
                    <p
                        v-for="task in event.tasks.slice(0, 3)"
                        :key="task.id"
                        class="ea-sample-task"
                    >
                        <span
                            class="ea-demo-checkbox"
                            :class="{ checked: task.done }"
                            ><AtelierIcon v-if="task.done" name="check" /></span
                        ><span :class="{ 'ea-strike': task.done }">{{
                            task.title
                        }}</span
                        ><span class="ea-sample-due">{{
                            task.done ? 'Done' : task.due
                        }}</span>
                    </p>
                    <Link
                        :href="
                            '/?view=workspace&event=' +
                            event.id +
                            '&section=tasks'
                        "
                        class="ea-text-link"
                        >Open the checklist <AtelierIcon name="arrow"
                    /></Link>
                </section>
                <section class="ea-sample-budget">
                    <h4>Room for what matters</h4>
                    <dl>
                        <div>
                            <dt>Planned budget</dt>
                            <dd>{{ money(event.budget) }}</dd>
                        </div>
                        <div>
                            <dt>Committed</dt>
                            <dd>{{ money(committed) }}</dd>
                        </div>
                        <div>
                            <dt>Still available</dt>
                            <dd>{{ money(event.budget - committed) }}</dd>
                        </div>
                    </dl>
                    <p>
                        <AtelierIcon name="check" /> All figures are
                        illustrative.
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>
