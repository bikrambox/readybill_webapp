<template>
  <div>
    <div class="input-group">
      <input 
        :id="id"
        :type="showPassword ? 'text' : 'password'"
        :value="modelValue"
        :placeholder="placeholder"
        class="form-control"
        :class="{ 'is-invalid': error }"
        @input="emit('update:modelValue', $event.target.value)"
        @blur="emit('blur')"
      />
      <button
        type="button"
        class="input-group-text bg-white border-start-0"
        style="cursor: pointer;"
        @click="togglePassword"
      >
        <i 
          class="bi"
          :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"
        ></i>
      </button>
    </div>
    <div v-if="error" class="invalid-feedback d-block">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  id: {
    type: String,
    required: true
  },
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Password'
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'blur'])

const showPassword = ref(false)

const togglePassword = () => {
  showPassword.value = !showPassword.value
}
</script>
