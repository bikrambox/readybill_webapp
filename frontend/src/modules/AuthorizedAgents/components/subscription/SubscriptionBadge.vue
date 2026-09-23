<template>
  <span class="sub-badge" :class="[`badge-${status}`, `size-${size}`]">
    <!-- <i :class="iconMap[status]" class="me-1"></i>
    {{ labelMap[status] }} -->
  </span>
</template>

<script setup>
defineProps({
  status: { type: String, default: "inactive" }, // active | inactive | expired
  size: { type: String, default: "md" }, // sm | md | lg
});

const labelMap = { active: "Active", inactive: "Inactive", expired: "Expired" };
const iconMap = {
  active: "bi bi-check-circle-fill",
  inactive: "bi bi-dash-circle-fill",
  expired: "bi bi-x-circle-fill",
};
</script>

<style scoped>
.sub-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 20px;
  font-weight: 600;
  white-space: nowrap;
}

/* Sizes */
.size-sm {
  font-size: 0.7rem;
  padding: 3px 8px;
}
.size-md {
  font-size: 0.78rem;
  padding: 4px 12px;
}
.size-lg {
  font-size: 0.88rem;
  padding: 6px 16px;
}

/* Colors */
.badge-active {
  background: #d1fae5;
  color: #065f46;
}
.badge-inactive {
  background: #f3f4f6;
  color: #6b7280;
}
.badge-expired {
  background: #fee2e2;
  color: #b91c1c;
}
</style>
