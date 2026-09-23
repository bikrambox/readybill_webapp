<template>
  <td 
    :class="{ 'border-danger bg-danger bg-opacity-10': !!error }"
    @click="startEdit"
  >
    <div v-if="!isEditing" class="d-flex align-items-center">
      <span v-if="prefix" class="me-1">{{ prefix }}</span>
      <span>{{ displayValue }}</span>
    </div>
    
    <input 
      v-else
      ref="inputRef"
      v-model="localValue"
      :type="type"
      class="form-control form-control-sm"
      @blur="finishEdit"
      @keyup.enter="finishEdit"
    />
    
    <span v-if="error" class="text-danger small d-block mt-1">
      {{ error }}
    </span>
  </td>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  prefix: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'blur'])

const isEditing = ref(false)
const localValue = ref(props.modelValue)
const inputRef = ref(null)

const displayValue = computed(() => {
  if (props.type === 'number' && props.modelValue) {
    return parseFloat(props.modelValue).toFixed(2)
  }
  return props.modelValue || ''
})

const startEdit = async () => {
  isEditing.value = true
  localValue.value = props.modelValue
  await nextTick()
  inputRef.value?.focus()
}

const finishEdit = () => {
  isEditing.value = false
  emit('update:modelValue', localValue.value)
  emit('blur')
}
</script>

<style scoped>
td {
  cursor: pointer;
  min-width: 100px;
  padding: 0.5rem;
}
</style>
