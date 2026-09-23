<template>
  <div class="mb-3 mb-lg-4">
    <nav aria-label="breadcrumb" v-if="breadcrumbs && breadcrumbs.length">
      <ol class="breadcrumb bg-transparent p-0 mb-2">
        <li
          v-for="(crumb, index) in breadcrumbs"
          :key="index"
          :class="['breadcrumb-item', { active: crumb.active }]"
          :aria-current="crumb.active ? 'page' : undefined"
        >
          <a
            v-if="!crumb.active && crumb.href"
            :href="crumb.href"
            class="text-decoration-none"
          >
            {{ crumb.label }}
          </a>
          <span v-else>{{ crumb.label }}</span>
        </li>
      </ol>
    </nav>
    <h1 class="h3 fw-bold mb-0">{{ title }}</h1>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  breadcrumbs: {
    type: Array,
    default: () => [],
  },
});
</script>

<style scoped>
.breadcrumb-item a {
  color: #6c757d;
}

.breadcrumb-item.active {
  color: #333;
}
</style>
