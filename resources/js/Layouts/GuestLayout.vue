<script setup>
import { Link } from '@inertiajs/vue3';
import AtelierBrand from '@/Components/AtelierBrand.vue';
import AtelierIcon from '@/Components/AtelierIcon.vue';
import '../../css/atelier.css';
</script>

<template>
    <div class="ea-auth">
        <a class="ea-skip" href="#auth-form-panel">Skip to form</a>

        <header class="ea-auth-header">
            <AtelierBrand />
            <Link href="/" class="ea-auth-home">
                Back to the atelier <AtelierIcon name="diagonal" />
            </Link>
        </header>

        <main id="auth-content" class="ea-auth-main">
            <section class="ea-auth-story" aria-label="Event Atelier introduction">
                <div>
                    <h1>A calmer way to bring every detail together.</h1>
                    <p>
                        One thoughtful planning room for the tasks, people,
                        decisions, and spending behind your occasion.
                    </p>
                </div>
                <figure>
                    <img
                        src="/images/preview-estate.jpg"
                        alt="An elegant event room prepared with tables and chandeliers"
                        width="1400"
                        height="933"
                    />
                    <figcaption>
                        <span>For the occasion.</span>
                        <em>And everything before it.</em>
                    </figcaption>
                </figure>
            </section>

            <section id="auth-form-panel" class="ea-auth-panel" tabindex="-1">
                <div class="ea-auth-card"><slot /></div>
                <p class="ea-auth-foot">Free for everyone · Private by default</p>
            </section>
        </main>
    </div>
</template>
