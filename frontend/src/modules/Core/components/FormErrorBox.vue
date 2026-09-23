<template>
  <div
    v-if="messages && messages.length"
    class="alert p-0 border"
    role="alert"
    style="border-color: #f1aeb5;"
  >
    <!-- Header -->
    <div
      class="d-flex justify-content-between align-items-center px-3 py-2 rounded-top"
      style="background-color: #b94a2f; color: #ffffff;"
    >
      <strong>{{ title }}</strong>
      <button
        type="button"
        class="btn-close btn-close-white"
        aria-label="Close"
        @click="$emit('clear')"
      ></button>
    </div>

    <!-- Body -->
    <div
      class="px-3 py-3 rounded-bottom"
      style="background-color: #fdecea; color: #b42318;"
    >
      <ul class="mb-0 ps-3 text-start">
        <!-- <li v-for="(msg, index) in messages" :key="index">
          {{ msg }}
        </li> -->

         <li 
          v-for="(msg, index) in messages" 
          :key="index"
          v-html="msg"
        ></li>

      </ul>
    </div>
  </div>
</template>

<script setup>
defineProps({

  title: {
    type: String,
    default: 'Error'
  },

  messages: {
    type: Array,
    default: () => [],
  },
})
</script>
