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
          <strong>Run Seeder</strong>. Maximum 5 000 per field.
        </p>

        <!-- Current record counts card -->
        <div v-if="stats" class="aios-stats-card">
          <h3 class="aios-stats-card__title">Current data in FluentCRM</h3>
          <div class="aios-stats-grid">
            <div
              v-for="key in MAIN_STAT_KEYS"
              :key="key"
              class="aios-stat"
            >
              <span class="aios-stat__count">{{ (stats[key] ?? 0).toLocaleString() }}</span>
              <span class="aios-stat__label">{{ STAT_LABELS[key] }}</span>
            </div>
          </div>
          <details class="aios-stats-detail">
            <summary>Derived tables</summary>
            <div class="aios-stats-grid aios-stats-grid--sm">
              <div
                v-for="key in DERIVED_STAT_KEYS"
                :key="key"
                class="aios-stat aios-stat--sm"
              >
                <span class="aios-stat__count">{{ (stats[key] ?? 0).toLocaleString() }}</span>
                <span class="aios-stat__label">{{ STAT_LABELS[key] }}</span>
              </div>
            </div>
          </details>
        </div>

        <!-- Form sections -->
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

        <p class="aios-note">
          Derived tables (relationships, campaign emails/metrics, funnel enrollments/metrics)
          run only when their related parent inputs are greater than zero.
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
            class="aios-btn aios-btn--outline"
            :disabled="running"
            @click="clearInputs"
          >
            Clear Inputs
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
import { useApi }   from '../composables/useApi.js';
import SeederForm   from '../components/SeederForm.vue';
import SeederResult from '../components/SeederResult.vue';

const { get, post, del } = useApi();

// ── State ────────────────────────────────────────────────────────────────────
const loading       = ref(true);
const running       = ref(false);
const runningAction = ref('');
const sections      = ref([]);
const form          = reactive({});
const result        = ref(null);
const resultMode    = ref('seed');
const stats         = ref(null);

// ── Stats display config ─────────────────────────────────────────────────────
const MAIN_STAT_KEYS = [
  'subscribers', 'companies', 'lists', 'tags', 'campaigns', 'funnels',
];
const DERIVED_STAT_KEYS = [
  'recurring_campaigns', 'email_sequences', 'email_templates',
  'subscriber_pivot', 'subscriber_notes', 'subscriber_meta',
  'campaign_emails', 'url_stores', 'campaign_url_metrics',
  'funnel_sequences', 'funnel_subscribers', 'funnel_metrics',
];
const STAT_LABELS = {
  subscribers:          'Subscribers',
  companies:            'Companies',
  lists:                'Lists',
  tags:                 'Tags',
  campaigns:            'Campaigns',
  recurring_campaigns:  'Recurring Campaigns',
  email_sequences:      'Email Sequences',
  email_templates:      'Email Templates',
  funnels:              'Funnels',
  subscriber_pivot:     'Relationships',
  subscriber_notes:     'Notes',
  subscriber_meta:      'Meta',
  campaign_emails:      'Emails',
  url_stores:           'Tracked URLs',
  campaign_url_metrics: 'URL Metrics',
  funnel_sequences:     'Sequences',
  funnel_subscribers:   'Enrollments',
  funnel_metrics:       'Metrics',
};

// ── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  // Load plugin metadata and live table counts in parallel
  const [pluginRes, statsRes] = await Promise.allSettled([
    get('/plugins'),
    get('/seed/fluent-crm/stats'),
  ]);

  if (pluginRes.status === 'fulfilled') {
    const plugin = (pluginRes.value.plugins ?? []).find(p => p.id === 'fluent-crm');
    if (plugin) {
      sections.value = plugin.sections ?? [];
      for (const section of sections.value) {
        for (const field of section.fields) {
          form[field.key] = field.default ?? 0;
        }
      }
    }
  }

  if (statsRes.status === 'fulfilled') {
    stats.value = statsRes.value.counts ?? null;
  }

  loading.value = false;
});

// ── Helpers ──────────────────────────────────────────────────────────────────
async function refreshStats() {
  try {
    const data  = await get('/seed/fluent-crm/stats');
    stats.value = data.counts ?? null;
  } catch {
    // non-fatal — stats card just keeps its previous values
  }
}

function clearInputs() {
  for (const section of sections.value) {
    for (const field of section.fields) {
      form[field.key] = 0;
    }
  }
}

// ── Actions ──────────────────────────────────────────────────────────────────
async function runSeed() {
  running.value       = true;
  runningAction.value = 'seed';
  resultMode.value    = 'seed';

  try {
    const data   = await post('/seed/fluent-crm', { ...form });
    result.value = {
      success: data.success ?? true,
      seeded:  data.seeded  ?? {},
      errors:  data.errors  ?? [],
    };
    await refreshStats();
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
    const data   = await del('/seed/fluent-crm');
    result.value = {
      success: data.success   ?? true,
      seeded:  data.truncated ?? {},
      errors:  data.errors    ?? [],
    };
    await refreshStats();
  } catch (e) {
    result.value = { success: false, seeded: {}, errors: [e.message] };
  } finally {
    running.value = false;
  }
}
</script>
