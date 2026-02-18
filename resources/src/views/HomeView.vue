<template>
  <div class="aios-home">
    <!-- Loading -->
    <div v-if="loading" class="aios-loading">
      <span class="aios-spinner" />
      <span>Loading plugins…</span>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="aios-notice aios-notice--error">
      <strong>Could not load plugins:</strong> {{ error }}
      <button class="aios-btn aios-btn--sm" style="margin-left:12px" @click="fetchPlugins">Retry</button>
    </div>

    <!-- Plugin grid -->
    <div v-else>
      <p class="aios-home__subtitle">Select a plugin to seed test data.</p>
      <div class="aios-plugin-grid">
        <div
          v-for="plugin in plugins"
          :key="plugin.id"
          class="aios-plugin-card"
          :class="{ 'aios-plugin-card--inactive': !plugin.active }"
        >
          <div class="aios-plugin-card__head">
            <span class="aios-plugin-card__name">{{ plugin.name }}</span>
            <span :class="['aios-badge', plugin.active ? 'aios-badge--active' : 'aios-badge--inactive']">
              {{ plugin.active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <p class="aios-plugin-card__desc">{{ plugin.description }}</p>

          <div class="aios-plugin-card__foot">
            <button
              class="aios-btn"
              :disabled="!plugin.active"
              @click="$router.push('/' + plugin.id)"
            >
              Configure →
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../composables/useApi.js';

const { get }   = useApi();
const plugins   = ref([]);
const loading   = ref(true);
const error     = ref(null);

async function fetchPlugins() {
  loading.value = true;
  error.value   = null;
  try {
    const data    = await get('/plugins');
    plugins.value = data.plugins ?? [];
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
}

onMounted(fetchPlugins);
</script>
