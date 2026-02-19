<template>
  <div class="aios-result">
    <!-- Header banner -->
    <div :class="['aios-result__banner', success ? 'aios-result__banner--ok' : 'aios-result__banner--fail']">
      <span v-if="mode === 'truncate'">
        {{ success ? '✓ All tables cleared.' : '✗ Truncate failed.' }}
      </span>
      <span v-else>
        {{ success ? '✓ Seeding complete.' : '✗ Seeding failed.' }}
      </span>
    </div>

    <!-- Errors -->
    <div v-if="errors.length" class="aios-errors">
      <p v-for="(err, i) in errors" :key="i">{{ err }}</p>
    </div>

    <!-- Seed results table -->
    <template v-if="mode === 'seed' && rowCount > 0">
      <table class="aios-result__table">
        <thead>
          <tr>
            <th>Table</th>
            <th class="aios-result__num">Rows inserted</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(count, key) in seeded" :key="key">
            <td>{{ label(key) }}</td>
            <td class="aios-result__num">{{ count.toLocaleString() }}</td>
          </tr>
          <tr class="aios-result__total">
            <td>Total</td>
            <td class="aios-result__num">{{ total.toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </template>

    <!-- Truncate summary -->
    <template v-if="mode === 'truncate' && rowCount > 0">
      <p class="aios-result__cleared">{{ rowCount }} table{{ rowCount === 1 ? '' : 's' }} cleared.</p>
    </template>

    <div class="aios-result__actions">
      <button class="aios-btn" @click="$emit('reset')">
        {{ mode === 'truncate' ? '← Back' : 'Run Again' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  seeded:  { type: Object,  default: () => ({}) },
  errors:  { type: Array,   default: () => ([]) },
  success: { type: Boolean, default: true       },
  mode:    { type: String,  default: 'seed'     }, // 'seed' | 'truncate'
});

defineEmits(['reset']);

const TABLE_LABELS = {
  companies:            'Companies',
  lists:                'Lists',
  tags:                 'Tags',
  subscribers:          'Subscribers',
  subscriber_pivot:     'Subscriber Relationships',
  subscriber_notes:     'Subscriber Notes',
  subscriber_meta:      'Subscriber Meta',
  campaigns:            'Campaigns',
  recurring_campaigns:  'Recurring Campaigns',
  email_sequences:      'Email Sequences',
  email_templates:      'Email Templates',
  campaign_emails:      'Campaign Emails',
  url_stores:           'Tracked URLs',
  campaign_url_metrics: 'URL Click Metrics',
  funnels:              'Funnels',
  funnel_sequences:     'Funnel Sequences',
  funnel_subscribers:   'Funnel Enrollments',
  funnel_metrics:       'Funnel Metrics',
};

function label(key) {
  return TABLE_LABELS[key] ?? key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

const rowCount = computed(() => Object.keys(props.seeded).length);

const total = computed(() =>
  Object.values(props.seeded).reduce((sum, v) => sum + (typeof v === 'number' ? v : 0), 0)
);
</script>
