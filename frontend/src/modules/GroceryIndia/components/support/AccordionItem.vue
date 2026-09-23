<script setup>
import { defineEmits, computed } from 'vue';

const props = defineProps({
  title: String,
  expanded: Boolean
});
const emit = defineEmits(['toggle']);

const ariaExpanded = computed(() => (props.expanded ? 'true' : 'false'));
const ariaHidden = computed(() => (!props.expanded ? 'true' : 'false'));
const arrowRotation = computed(() => (props.expanded ? 'rotate(90deg)' : 'none'));

function onToggle() {
  emit('toggle');
}
</script>

<template>
  <div class="accordion-item">
    <button
      class="accordion-trigger btn btn-link w-100 text-start d-flex align-items-center gap-2"
      :aria-expanded="ariaExpanded"
      @click="onToggle"
    >
      <span
        class="accordion-arrow"
        aria-hidden="true"
        :style="{ transform: arrowRotation }"
      >
        ▸
      </span>
      <span class="accordion-title" v-html="title"></span>
    </button>
    <div
      class="accordion-panel px-3 pb-3 border-bottom"
      role="region"
      v-show="expanded"
      :aria-hidden="ariaHidden"
    >
      <div class="accordion-body text-break">
        <slot />
      </div>
    </div>
  </div>
</template>

<style scoped>
.accordion-trigger {
  padding: 12px 16px;
  background: #f8f9fb;
  border: 0;
  border-bottom: 1px solid #e2e6ea;
  font-weight: 600;
  font-size: 16px;
  line-height: 1.4;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  cursor: pointer;
  transition: background 0.2s ease;
}

.accordion-trigger[aria-expanded='true'] {
  background: #eef5ff;
}

.accordion-trigger:focus {
  outline: 2px solid #0d6efd;
  outline-offset: 2px;
}

.accordion-arrow {
  display: inline-block;
  transition: transform 0.2s ease;
  transform-origin: 50% 50%;
}
</style>
