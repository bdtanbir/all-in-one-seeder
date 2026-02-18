<template>
  <div class="aios-plugin-view">
    <!-- Back nav -->
    <div class="aios-nav">
      <router-link to="/" class="aios-back-link">← All Plugins</router-link>
    </div>

    <!-- Loading metadata -->
    <div v-if="loading" class="aios-loading">
      <span class="aios-spinner" />
      <span>Loading…</span>
    </div>

    <template v-else>
      <!-- Result / truncate feedback -->
      <SeederResult
        v-if="result"
        :seeded="result.seeded"
        :errors="result.errors"
        :success="result.success"
        :mode="resultMode"
        @reset="result = null"
      />

      <!-- Seeder form -->
      <template v-else>
        <h2 class="aios-view-title">FluentCRM Seeder</h2>
        <p class="aios-view-subtitle">
          Configure how many records to generate per table, then click
          <strong>Run Seeder</strong>.
        </p>

        <div
          v-for="section in sections"
          :key="section.label"
          class="aios-section"
        >
          <h3 class="aios-section__title">{{ section.label }}</h3>
          <div class="aios-field-row">
            <SeederForm
              v-for="field in section.fields"
              :key="field.key"
              :label="field.label"
              v-model="form[field.key]"
            />
          </div>
        </div>

        <!-- Derived fields note -->
        <p class="aios-note">
          Campaign emails, funnel enrollments, and metrics are generated automatically
          from the records above.
        </p>

        <div class="aios-action-bar">
          <button
            class="aios-btn aios-btn--lg"
            :disabled="running"
            @click="runSeed"
          >
            <span v-if="running && runningAction === 'seed'" class="aios-spinner aios-spinner--sm" />
            {{ running && runningAction === 'seed' ? 'Seeding…' : 'Run Seeder' }}
          </button>

          <button
            class="aios-btn aios-btn--danger"
            :disabled="running"
            @click="runTruncate"
          >
            <span v-if="running && runningAction === 'truncate'" class="aios-spinner aios-spinner--sm" />
            {{ running && runningAction === 'truncate' ? 'Clearing…' : 'Truncate All' }}
          </button>
        </div>
      </template>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useApi }      from '../composables/useApi.js';
import SeederForm      from '../components/SeederForm.vue';
import SeederResult    from '../components/SeederResult.vue';

const { get, post, del } = useApi();

const loading       = ref(true);
const running       = ref(false);
const runningAction = ref('');
const sections      = ref([]);
const form          = reactive({});
const result        = ref(null);
const resultMode    = ref('seed');

onMounted(async () => {
  try {
    const data   = await get('/plugins');
    const plugin = (data.plugins ?? []).find(p => p.id === 'fluent-crm');

    if (plugin) {
      sections.value = plugin.sections ?? [];
      for (const section of sections.value) {
        for (const field of section.fields) {
          form[field.key] = field.default ?? 0;
        }
      }
    }
  } catch {
    // leave sections empty; form will be empty
  } finally {
    loading.value = false;
  }
});

async function runSeed() {
  running.value       = true;
  runningAction.value = 'seed';
  resultMode.value    = 'seed';

  try {
    const data = await post('/seed/fluent-crm', { ...form });
    result.value = {
      success: data.success ?? true,
      seeded:  data.seeded  ?? {},
      errors:  data.errors  ?? [],
    };
  } catch (e) {
    result.value = { success: false, seeded: {}, errors: [e.message] };
  } finally {
    running.value = false;
  }
}

async function runTruncate() {
  if (!window.confirm('This will permanently delete all seeded FluentCRM data. Continue?')) return;

  running.value       = true;
  runningAction.value = 'truncate';
  resultMode.value    = 'truncate';

  try {
    const data = await del('/seed/fluent-crm');
    result.value = {
      success: data.success   ?? true,
      seeded:  data.truncated ?? {},
      errors:  data.errors    ?? [],
    };
  } catch (e) {
    result.value = { success: false, seeded: {}, errors: [e.message] };
  } finally {
    running.value = false;
  }
}
</script>
