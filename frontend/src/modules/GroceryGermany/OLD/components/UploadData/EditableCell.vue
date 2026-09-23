<!-- src/components/UploadData/EditableCell.vue -->
<template>
  <td
    class="editable-cell"
    :class="{ 'has-error': hasError }"
    @click="startEdit"
  >
    <div v-if="!isEditing" class="cell-content">
      <span class="value-display">
        {{ prefix }}{{ displayValue }}
      </span>
    </div>
    <div v-else class="cell-edit">
      <input
        ref="inputRef"
        v-model="localValue"
        :type="inputType"
        :step="type === 'number' ? '0.01' : undefined"
        class="form-control form-control-sm"
        @blur="finishEdit"
        @keypress.enter="finishEdit"
      />
    </div>
    <span v-if="hasError && errorMessage" class="error-message">
      {{ errorMessage }}
    </span>
  </td>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  modelValue: [String, Number],
  rowIndex: Number,
  colIndex: Number,
  rowId: [String, Number],
  type: {
    type: String,
    default: 'text'
  },
  prefix: {
    type: String,
    default: ''
  },
  hasError: Boolean,
  errorMessage: String
})

const emit = defineEmits(['update:modelValue', 'update'])

const isEditing = ref(false)
const localValue = ref(props.modelValue)
const inputRef = ref(null)

const inputType = computed(() => {
  return props.type === 'number' ? 'number' : 'text'
})

const displayValue = computed(() => {
  if (props.type === 'number' && localValue.value) {
    const num = parseFloat(localValue.value)
    return isNaN(num) ? '' : num.toFixed(2)
  }
  return localValue.value || ''
})

const startEdit = async () => {
  isEditing.value = true
  await nextTick()
  inputRef.value?.focus()
}

const finishEdit = () => {
  isEditing.value = false
  emit('update:modelValue', localValue.value)
  
  // Emit update event with row data
  const rowData = {
    id: props.rowId
  }
  
  // Build the complete row object with updated value
  const colMap = {
    1: 'item_name',
    2: 'quantity',
    3: 'min_stock_alert',
    4: 'mrp',
    5: 'sale_price',
    7: 'hsn',
    8: 'gst',
    9: 'cess'
  }
  
  if (colMap[props.colIndex]) {
    rowData[colMap[props.colIndex]] = localValue.value
  }
  
  emit('update', props.rowIndex, props.colIndex, rowData)
}

watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal
})
</script>

<style scoped>
.editable-cell {
  cursor: pointer;
  min-width: 80px;
  padding: 8px 12px;
}

.editable-cell:hover:not(.has-error) {
  background-color: #f0f8ff;
}

.cell-content {
  min-height: 20px;
}

.value-display {
  display: inline-block;
  width: 100%;
}

.cell-edit input {
  width: 100%;
  border: 2px solid #0d6efd;
  outline: none;
  text-align: center;
  padding: 4px 8px;
  border-radius: 4px;
}

.has-error {
  background-color: #f8d7da !important;
  border: 2px solid #dc3545 !important;
}

.error-message {
  color: #dc3545;
  font-size: 0.75rem;
  display: block;
  margin-top: 0.25rem;
  font-weight: 500;
}
</style>
