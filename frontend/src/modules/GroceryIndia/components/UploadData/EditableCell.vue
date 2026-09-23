<template>
  <td
    class="editable-cell"
    :class="{
      'has-error': hasError,
      'is-updating': isUpdating,
      'is-success': isSuccess,
      'is-select': isSelectType,
      'is-readonly': !editable,
    }"
    @click="startEdit"
  >
    <div v-if="!isEditing" class="cell-content">
      <span class="value-display"> {{ prefix }}{{ displayValue }} </span>

      <span v-if="isUpdating" class="cell-indicator">
        <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
      </span>

      <span v-else-if="isSuccess" class="cell-indicator">
        <i class="bi bi-check-circle-fill text-success"></i>
      </span>

      <span v-else-if="hasError" class="cell-indicator">
        <i class="bi bi-exclamation-circle-fill text-danger"></i>
      </span>
    </div>

    <div v-else class="cell-edit" @click.stop>
      <select
        v-if="isSelectType"
        ref="inputRef"
        v-model="localValue"
        class="form-select form-select-sm"
        @change="finishEdit"
        @blur="finishEdit"
        @keydown.esc.prevent="cancelEdit"
      >
        <option value="">{{ placeholderText }}</option>
        <option
          v-for="option in normalizedOptions"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>

      <input
        v-else
        ref="inputRef"
        v-model="localValue"
        :type="inputType"
        :step="type === 'number' ? '0.01' : undefined"
        class="form-control form-control-sm"
        @blur="finishEdit"
        @keydown.enter.prevent="finishEdit"
        @keydown.esc.prevent="cancelEdit"
      />
    </div>

    <span v-if="hasError && errorMessage" class="error-tooltip" :title="errorMessage">
      <i class="bi bi-info-circle-fill"></i>
    </span>
  </td>
</template>

<script setup>
import { ref, computed, watch, nextTick } from "vue";

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean, null],
    default: "",
  },
  rowIndex: {
    type: Number,
    required: true,
  },
  colIndex: {
    type: Number,
    required: true,
  },
  rowId: {
    type: [String, Number],
    default: null,
  },
  field: {
    type: String,
    default: "",
  },
  type: {
    type: String,
    default: "text",
  },
  prefix: {
    type: String,
    default: "",
  },
  editable: {
    type: Boolean,
    default: true,
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: "",
  },
  isUpdating: {
    type: Boolean,
    default: false,
  },
  isSuccess: {
    type: Boolean,
    default: false,
  },
  options: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: "",
  },
  displayFormatter: {
    type: Function,
    default: null,
  },
});

const emit = defineEmits(["update:modelValue", "update"]);

const isEditing = ref(false);
const localValue = ref(props.modelValue);
const originalValue = ref(props.modelValue);
const inputRef = ref(null);

const isSelectType = computed(() => props.type === "select");

const inputType = computed(() => {
  if (props.type === "number") return "number";
  return "text";
});

const normalizedOptions = computed(() =>
  (props.options || []).map((option) => ({
    label: option?.label ?? option?.name ?? option?.value ?? "",
    value: option?.value ?? option?.id ?? "",
  }))
);

const placeholderText = computed(() => {
  if (props.placeholder) return props.placeholder;
  if (isSelectType.value) return "Select option";
  return "";
});

const displayValue = computed(() => {
  if (props.displayFormatter) {
    return props.displayFormatter(props.modelValue);
  }

  if (isSelectType.value) {
    const matched = normalizedOptions.value.find(
      (option) => String(option.value) === String(props.modelValue)
    );
    return matched?.label || props.modelValue || "-";
  }

  if (props.type === "number") {
    const num = parseFloat(props.modelValue);
    return Number.isNaN(num) ? "0.00" : num.toFixed(2);
  }

  return props.modelValue === null ||
    props.modelValue === undefined ||
    props.modelValue === ""
    ? "-"
    : props.modelValue;
});

const startEdit = async () => {
  if (!props.editable || props.isUpdating) return;

  isEditing.value = true;
  originalValue.value = props.modelValue;
  localValue.value = props.modelValue;

  await nextTick();

  inputRef.value?.focus();

  if (!isSelectType.value && inputRef.value?.select) {
    inputRef.value.select();
  }
};

const finishEdit = () => {
  if (!isEditing.value) return;

  const newValue =
    props.type === "number" ? normalizeNumber(localValue.value) : localValue.value;

  const oldValue =
    props.type === "number" ? normalizeNumber(originalValue.value) : originalValue.value;

  isEditing.value = false;

  if (newValue === oldValue) return;

  emit("update:modelValue", newValue);

  emit("update", {
    rowIndex: props.rowIndex,
    colIndex: props.colIndex,
    rowId: props.rowId,
    field: props.field,
    value: newValue,
  });
};

const cancelEdit = () => {
  localValue.value = originalValue.value;
  isEditing.value = false;
};

const normalizeNumber = (value) => {
  const num = parseFloat(value ?? 0);
  return Number.isNaN(num) ? 0 : num;
};

watch(
  () => props.modelValue,
  (newVal) => {
    if (!isEditing.value) {
      localValue.value = newVal;
      originalValue.value = newVal;
    }
  }
);
</script>

<style scoped>
.editable-cell {
  position: relative;
  cursor: pointer;
  min-width: 80px;
  padding: 8px 12px;
  transition: all 0.3s ease;
  vertical-align: middle;
}

.editable-cell:hover:not(.has-error):not(.is-updating):not(.is-readonly) {
  background-color: #f0f8ff;
}

.editable-cell.is-readonly {
  cursor: default;
  background-color: #f8f9fa;
}

.cell-content {
  min-height: 20px;
  position: relative;
  display: flex;
  align-items: center;
  padding-right: 24px;
}

.value-display {
  display: inline-block;
  width: calc(100% - 24px);
  word-break: break-word;
}

.cell-indicator {
  position: absolute;
  top: 2px;
  right: 2px;
  font-size: 0.875rem;
  animation: fadeIn 0.3s ease;
}

.cell-edit input,
.cell-edit select {
  width: 100%;
  border: 2px solid #0d6efd !important;
  outline: none;
  padding: 6px 10px;
  border-radius: 4px;
  font-size: 0.9rem;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  transition: border-color 0.15s ease-in-out;
}

.cell-edit input:focus,
.cell-edit select:focus {
  border-color: #0a58ca !important;
}

.is-updating {
  background-color: #fff3cd !important;
  border: 2px solid #ffc107 !important;
  animation: pulse 1.5s ease-in-out infinite;
}

.is-success {
  background-color: #d1e7dd !important;
  border: 2px solid #198754 !important;
  animation: successPulse 0.5s ease;
}

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

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.85;
  }
}

@keyframes successPulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
}
</style>
