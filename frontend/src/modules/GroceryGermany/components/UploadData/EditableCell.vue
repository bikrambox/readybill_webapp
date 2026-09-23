<template>
  <td
    class="editable-cell"
    :class="{ 
      'has-error': hasError,
      'is-updating': isUpdating,
      'is-success': isSuccess
    }"
    @click="startEdit"
  >
    <div v-if="!isEditing" class="cell-content">
      <span class="value-display">
        {{ prefix }}{{ displayValue }}
      </span>
      <!-- State indicators -->
      <span v-if="isUpdating" class="cell-indicator">
        <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
      </span>
      <span v-if="isSuccess" class="cell-indicator">
        <i class="bi bi-check-circle-fill text-success"></i>
      </span>
      <span v-if="hasError" class="cell-indicator">
        <i class="bi bi-exclamation-circle-fill text-danger"></i>
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
        @keypress.enter.prevent="finishEdit"
        @keydown.esc.prevent="cancelEdit"
      />
    </div>
    <span v-if="hasError && errorMessage" class="error-tooltip" :title="errorMessage">
      <i class="bi bi-info-circle-fill"></i>
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
  errorMessage: String,
  isUpdating: Boolean,
  isSuccess: Boolean
})

const emit = defineEmits(['update:modelValue', 'update'])

const isEditing = ref(false)
const localValue = ref(props.modelValue)
const originalValue = ref(props.modelValue)
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
  if (!isEditing.value) {
    originalValue.value = localValue.value
    isEditing.value = true
    await nextTick()
    inputRef.value?.focus()
    if (inputRef.value?.select && props.type !== 'select') {
      inputRef.value.select()
    }
  }
}

const finishEdit = () => {
  // Only save if value changed
  if (localValue.value === originalValue.value) {
    isEditing.value = false
    return
  }
  
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

const cancelEdit = () => {
  localValue.value = originalValue.value
  isEditing.value = false
}

watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal
  originalValue.value = newVal
})
</script>

<style scoped>
.editable-cell {
  position: relative;
  cursor: pointer;
  min-width: 80px;
  padding: 8px 12px;
  transition: all 0.3s ease;
}

.editable-cell:hover:not(.has-error):not(.is-updating) {
  background-color: #f0f8ff;
}

.cell-content {
  min-height: 20px;
  position: relative;
}

.value-display {
  display: inline-block;
  width: calc(100% - 24px);
}

.cell-indicator {
  position: absolute;
  top: 2px;
  right: 2px;
  font-size: 0.875rem;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1); }
}

.cell-edit input {
  width: 100%;
  border: 2px solid #0d6efd;
  outline: none;
  text-align: center;
  padding: 6px 10px;
  border-radius: 4px;
  font-size: 0.9rem;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  transition: border-color 0.15s ease-in-out;
}

.cell-edit input:focus {
  border-color: #0a58ca;
}

/* Updating state */
.is-updating {
  background-color: #fff3cd !important;
  border: 2px solid #ffc107 !important;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.85; }
}

/* Success state */
.is-success {
  background-color: #d1e7dd !important;
  border: 2px solid #198754 !important;
  animation: successPulse 0.5s ease;
}

@keyframes successPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.02); }
}

/* Error state */
.has-error {
  background-color: #f8d7da !important;
  border: 2px solid #dc3545 !important;
}

.error-tooltip {
  position: absolute;
  bottom: 2px;
  right: 2px;
  color: #dc3545;
  font-size: 0.75rem;
  cursor: help;
}

.error-message {
  color: #dc3545;
  font-size: 0.75rem;
  display: block;
  margin-top: 0.25rem;
  font-weight: 500;
}
</style>
