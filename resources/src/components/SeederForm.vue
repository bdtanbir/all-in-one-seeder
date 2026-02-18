<template>
  <div class="aios-field">
    <label class="aios-field__label">{{ label }}</label>
    <input
      class="aios-input"
      type="number"
      :value="modelValue"
      min="0"
      step="1"
      @input="emit('update:modelValue', Math.max(0, parseInt($event.target.value) || 0))"
    />
  </div>
</template>

<script setup>
defineProps({
  label:      { type: String,  required: true },
  modelValue: { type: Number,  default: 0     },
});

const emit = defineEmits(['update:modelValue']);
</script>
