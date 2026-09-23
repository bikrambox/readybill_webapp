<template>
  <div v-if="password" class="strength-wrapper">
    <div class="d-flex justify-content-between mb-1">
      <span class="strength-label">Password Strength</span>
      <span class="strength-text" :class="`text-${color}`">{{ label }}</span>
    </div>
    <div class="progress strength-track">
      <div
        class="progress-bar"
        :class="`bg-${color}`"
        :style="{ width: percent + '%', transition: 'width 0.4s ease' }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({ password: { type: String, default: "" } });

const score = computed(() => {
  const p = props.password;
  if (!p) return 0;
  let s = 0;
  if (p.length >= 8) s++;
  if (/[A-Z]/.test(p)) s++;
  if (/[0-9]/.test(p)) s++;
  if (/[^A-Za-z0-9]/.test(p)) s++;
  return s;
});

const label = computed(() => ["", "Weak", "Fair", "Good", "Strong"][score.value]);
const color = computed(() => ["", "danger", "warning", "info", "success"][score.value]);
const percent = computed(() => score.value * 25);
</script>

<style scoped>
.strength-track {
  height: 6px;
  border-radius: 10px;
  background: #e9ecef;
}
.strength-label {
  font-size: 0.75rem;
  color: #6c757d;
}
.strength-text {
  font-size: 0.75rem;
  font-weight: 600;
}
</style>
