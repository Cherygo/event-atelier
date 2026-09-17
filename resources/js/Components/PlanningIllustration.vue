<script setup>
defineProps({ compact: Boolean });
</script>

<template>
    <div
        class="planning-illustration"
        :class="{ compact }"
        aria-label="Sample planning workspace showing tasks, vendors, and budget"
    >
        <div class="illustration-bar">
            <span class="illustration-brand">event atelier</span
            ><span>YOUR PLANNING ROOM <i></i></span>
        </div>
        <div class="illustration-body">
            <div class="illustration-heading">
                <div>
                    <span class="home-kicker">A COUNTRYSIDE WEDDING</span>
                    <h3>Olivia & Alexander</h3>
                    <p>24 October 2026 · The Cotswolds</p>
                </div>
                <div class="illustration-count">
                    <strong>37</strong><span>DAYS TO GO</span>
                </div>
            </div>
            <div class="illustration-stats">
                <div>
                    <small>THE CHECKLIST</small><strong>24 <em>/ 36</em></strong
                    ><span class="illustration-track"><i></i></span>
                </div>
                <div>
                    <small>THE BUDGET</small><strong>£18,450</strong>
                    <p>£6,550 left for the little extras</p>
                </div>
                <div>
                    <small>YOUR TEAM</small
                    ><span class="illustration-avatars"
                        ><b>EL</b><b>OA</b><b>JM</b></span
                    >
                    <p>Better, together.</p>
                </div>
            </div>
            <div class="illustration-lists">
                <section>
                    <h4>On the horizon <span>ALL TASKS ↗</span></h4>
                    <p><i></i>Confirm the seasonal menu <small>Today</small></p>
                    <p>
                        <i></i>Review the floral proposal
                        <small>Tomorrow</small>
                    </p>
                    <p class="is-done">
                        <i>✓</i>Send the invitations <small>Done</small>
                    </p>
                </section>
                <section>
                    <h4>A lovely little team</h4>
                    <div class="illustration-vendor">
                        <b>O</b><span>The Orangery<small>Venue</small></span
                        ><em>Booked</em>
                    </div>
                    <div class="illustration-vendor">
                        <b>G</b
                        ><span>Gather & Graze<small>Catering</small></span
                        ><em>Shortlisted</em>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
