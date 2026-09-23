<template>
  <nav class="mt-4">
    <ol class="breadcrumb">
      <li 
        v-for="(item, index) in items" 
        :key="index"
        class="breadcrumb-item"
        :class="{ active: item.active }"
      >
        <a v-if="!item.active" :href="item.link">{{ item.label }}</a>
        <span v-else>{{ item.label }}</span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    required: true
  }
})
</script>

<style scoped>
.breadcrumb {
  background: transparent;
  padding: 0;
  margin-bottom: 1rem;
}

.breadcrumb-item a {
  color: #0d6efd;
  text-decoration: none;
}

.breadcrumb-item a:hover {
  text-decoration: underline;
}

.breadcrumb-item.active {
  color: #6c757d;
}
</style>
