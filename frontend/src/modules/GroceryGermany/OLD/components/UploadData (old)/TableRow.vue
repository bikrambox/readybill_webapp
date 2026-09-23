<template>
  <tr :class="{ 'table-danger': hasError }">
    <td>
      <input 
        type="checkbox" 
        class="form-check-input"
        :checked="isSelected"
        @change="emit('select')"
      />
    </td>
    
    <EditableCell 
      v-model="localRow.item_name"
      :error="getCellError(1)"
      @blur="handleUpdate"
    />
    
    <EditableCell 
      v-model="localRow.quantity"
      type="number"
      :error="getCellError(2)"
      @blur="handleUpdate"
    />
    
    <EditableCell 
      v-model="localRow.min_stock_alert"
      type="number"
      :error="getCellError(3)"
      @blur="handleUpdate"
    />
    
    <EditableCell 
      v-model="localRow.mrp"
      type="number"
      prefix="₹"
      :error="getCellError(4)"
      @blur="handleUpdate"
    />
    
    <EditableCell 
      v-model="localRow.sale_price"
      type="number"
      prefix="₹"
      :error="getCellError(5)"
      @blur="handleUpdate"
    />
    
    <td>
      <select 
        v-model="localRow.unit"
        class="form-select form-select-sm"
        @change="handleUpdate"
      >
        <option value="">Select Unit</option>
        <option 
          v-for="unit in unitList" 
          :key="unit.value"
          :value="unit.value"
        >
          {{ unit.label }}
        </option>
      </select>
      <span v-if="getCellError(6)" class="text-danger small d-block mt-1">
        {{ getCellError(6) }}
      </span>
    </td>
    
    <EditableCell 
      v-model="localRow.hsn"
      :error="getCellError(7)"
      @blur="handleUpdate"
    />
    
    <EditableCell 
      v-model="localRow.gst"
      type="number"
      :error="getCellError(8)"
      @blur="handleUpdate"
    />
    
    <EditableCell 
      v-model="localRow.cess"
      type="number"
      :error="getCellError(9)"
      @blur="handleUpdate"
    />
  </tr>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import EditableCell from './EditableCell.vue'

const props = defineProps({
  row: {
    type: Object,
    required: true
  },
  rowIndex: {
    type: Number,
    required: true
  },
  unitList: {
    type: Array,
    default: () => []
  },
  isSelected: {
    type: Boolean,
    default: false
  },
  errorCoordinates: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['select', 'update'])

const localRow = ref({ ...props.row })

// Watch for prop changes
watch(() => props.row, (newVal) => {
  localRow.value = { ...newVal }
}, { deep: true })

// Check if row has error
const hasError = computed(() => {
  return props.errorCoordinates.some(coord => {
    const [row] = coord.split(',').map(Number)
    return row === props.rowIndex
  })
})

// Get cell error
const getCellError = (colIndex) => {
  const hasError = props.errorCoordinates.some(coord => {
    const [row, col] = coord.split(',').map(Number)
    return row === props.rowIndex && col === colIndex
  })
  return hasError ? 'Invalid value' : null
}

// Handle update
const handleUpdate = () => {
  emit('update', {
    ...localRow.value,
    row_index: props.rowIndex
  })
}
</script>
