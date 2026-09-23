<template>
  <div class="pagetitle mb-4">
    <h1 class="mb-2">{{ title }}</h1>
    
    <nav v-if="breadcrumbs && breadcrumbs.length > 0" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li 
          v-for="(crumb, index) in breadcrumbs" 
          :key="index"
          class="breadcrumb-item"
          :class="{ active: crumb.active }"
        >
          <router-link 
            v-if="!crumb.active && crumb.to"
            :to="crumb.to"
          >
            {{ $t(crumb.label) }}
          </router-link>
          <span v-else>{{ $t(crumb.label) }}</span>
        </li>
      </ol>
    </nav>

    <p v-if="description" class="text-muted mt-2 mb-1">{{ description }}</p>
    <p v-if="note" class="text-danger mb-0">{{ note }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  description: {
    type: String,
    default: ''
  },
  note: {
    type: String,
    default: ''
  }
})
</script>
