<template>
  <main class="aios-app">
    <header class="aios-header">
      <h1>All-in-One Seeder</h1>
      <p>Vue 3 admin panel is loaded from the resources directory.</p>
    </header>

    <section class="aios-card">
      <h2>Setup Complete</h2>
      <p>
        Your WordPress admin page is now rendering a Vue app. Start adding modules,
        routes, and API calls here.
      </p>
      <button class="aios-btn" @click="count++">Clicked {{ count }} times</button>
    </section>
  </main>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);
</script>
