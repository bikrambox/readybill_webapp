<script setup>
import { ref } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    required: true,
  }
});

const expandedIndex = ref(0);

function toggle(index) {
  expandedIndex.value = expandedIndex.value === index ? -1 : index;
}
</script>

<template>
  <section class="accordion border rounded" id="faq">
    <div v-for="(item, index) in items" :key="index" class="accordion-item m-0">
      <h2 class="accordion-header">
        <button
          class="accordion-trigger d-flex align-items-center gap-2 w-100 text-start px-3 py-2 bg-light border-0 border-bottom"
          :aria-expanded="expandedIndex === index ? 'true' : 'false'"
          :aria-controls="'acc-p' + (index + 1)"
          :id="'acc-h' + (index + 1)"
          @click="toggle(index)"
        >
          <span
            class="accordion-arrow transition-transform"
            :class="{'rotate-90': expandedIndex === index}"
            aria-hidden="true"
          >▸</span>
          <span class="accordion-title" v-html="item.question" />
        </button>
      </h2>
      <div
        :id="'acc-p' + (index + 1)"
        class="accordion-panel px-3 pb-3 border-bottom"
        role="region"
        :aria-labelledby="'acc-h' + (index + 1)"
        v-show="expandedIndex === index"
      >
        <div class="accordion-body text-break" v-html="item.answer" />
      </div>
    </div>
  </section>
</template>

<style scoped>
.accordion-arrow {
  display: inline-block;
  transition: transform 0.2s ease;
  transform-origin: 50% 50%;
  user-select: none;
}
.rotate-90 {
  transform: rotate(90deg);
}
.accordion-trigger {
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  background-color: #f8f9fb;
}
.accordion-trigger:hover,
.accordion-trigger[aria-expanded="true"] {
  background-color: #eef5ff;
}
.accordion-panel {
  background-color: white;
}
</style>
