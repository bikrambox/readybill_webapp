<template>
  <div class="card border-0 shadow-sm position-relative">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table
          ref="tableRef"
          :key="`table-refresh-${tableKey}`"
          class="table table-hover align-middle mb-0"
          style="width: 100%"
        >
          <thead class="table-light">
            <tr>
              <th class="ps-4">
                <input
                  type="checkbox"
                  class="form-check-input"
                  @change="handleSelectAllChange"
                  :checked="selectAll"
                  :disabled="loading"
                />
              </th>
              <th>{{ $t('common.Name') }}</th>
              <th>{{ $t('common.Stock') }}</th>
              <th>{{ $t('common.MRP') }}</th>
              <th>{{ $t('common.Rate') }}</th>
              <th>{{ $t('common.Unit') }}</th>
              <th v-if="isAdmin">{{ $t('common.Action') }}</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="!loading && data.length > 0">
              <tr
                v-for="item in data"
                :key="`row-${item.id}-${tableKey}`"
                :class="{ inventoryMinStockAlert: shouldHighlightRow(item) }"
              >
                <td class="ps-4">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    :checked="selectedItems.has(item.id)"
                    @change="handleCheckboxChange(item.id, $event)"
                    :disabled="loading"
                  />
                </td>
                <td>{{ item.name }}</td>
                <td>{{ formatStock(item.stock) }}</td>
                <td>{{ $formatCurrency(item.mrp) }}</td>
                <td>
                  <span class="text-success fw-semibold">{{
                    $formatCurrency(item.rate)
                  }}</span>
                </td>
                <td>{{ item.unit }}</td>
                <td v-if="isAdmin">
                  <button
                    class="btn btn-sm btn-light"
                    @click="$emit('view-item', item.id)"
                    :disabled="loading"
                  >
                    <i class="bi bi-eye"></i> {{ $t('common.View') }}
                  </button>
                </td>
              </tr>
            </template>
            <template v-else-if="!loading && data.length === 0">
              <tr>
                <td
                  :colspan="isAdmin ? 7 : 6"
                  class="text-center py-4 text-muted"
                >
                  <i
                    class="bi bi-inbox d-block mb-2"
                    style="font-size: 2.5rem; color: #dee2e6"
                  ></i>
                  {{ $t('common.No items found') }}
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td
                  :colspan="isAdmin ? 7 : 6"
                  class="text-center py-4 text-muted"
                >
                  <div
                    class="spinner-border spinner-border-sm text-primary"
                    role="status"
                  >
                    <span class="visually-hidden">{{ $t('common.Loading') }}...</span>
                  </div>
                  <span class="ms-2">{{ $t('common.Loading data') }}...</span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Custom Footer with Pagination -->
    <div class="card-footer bg-white">
      <div class="footer-layout">
        <!-- Left: Rows per page -->
        <div class="footer-left">
          <span class="footer-text">{{ $t('common.Rows per page') }}:</span>
          <select
            class="footer-select"
            :value="pageLength"
            @change="handlePageLengthChange"
            :disabled="loading"
          >
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>

        <!-- Right: Info and Arrows -->
        <div class="footer-right">
          <span class="footer-info">{{ paginationInfo }}</span>
          <button
            type="button"
            class="footer-arrow"
            :class="{ disabled: !canGoPrev || loading }"
            @click="handlePrevPage"
            :disabled="!canGoPrev || loading"
          >
            <i class="bi bi-chevron-left"></i>
          </button>
          <button
            type="button"
            class="footer-arrow"
            :class="{ disabled: !canGoNext || loading }"
            @click="handleNextPage"
            :disabled="!canGoNext || loading"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref, getCurrentInstance } from "vue";
import { useUserPreferencesStore } from "@/modules/GroceryGermany/stores/userPreferences";
import { storeToRefs } from "pinia";

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  selectedItems: {
    type: Set,
    required: true,
  },
  selectAll: {
    type: Boolean,
    default: false,
  },
  currentPage: {
    type: Number,
    default: 0,
  },
  pageLength: {
    type: Number,
    default: 10,
  },
  recordsTotal: {
    type: Number,
    default: 0,
  },
  recordsFiltered: {
    type: Number,
    default: 0,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  isAdmin: { type: Boolean, default: false },
  currency: {
    type: String,
  },
  // decimalSeparator: {
  //   type: String,
  // },
});

const emit = defineEmits([
  "row-selected",
  "row-deselected",
  "view-item",
  "toggle-select-all",
  "page-change",
  "page-length-change",
]);

const preferencesStore = useUserPreferencesStore();
const { preferences } = storeToRefs(preferencesStore);

// Reactive key to force table re-render
const tableKey = ref(0);

const app = getCurrentInstance();
const currency = app.appContext.config.globalProperties.$currency;
const decimalSeparator =
  app.appContext.config.globalProperties.$decimalSeparator;


// Watch data prop for changes and force re-render
watch(
  () => props.data,
  (newData, oldData) => {
    console.log("🔄 Table data changed:", {
      oldCount: oldData?.length || 0,
      newCount: newData.length,
      oldIds: oldData?.map((i) => i.id) || [],
      newIds: newData.map((i) => i.id),
    });

    // Force re-render by incrementing key
    tableKey.value++;
    console.log("🔑 Table key updated to:", tableKey.value);
  },
  { deep: true }
);

// Watch recordsFiltered for changes
watch(
  () => props.recordsFiltered,
  (newVal, oldVal) => {
    console.log("📊 RecordsFiltered changed:", { old: oldVal, new: newVal });
    if (newVal !== oldVal) {
      tableKey.value++;
      console.log("🔑 Table key updated to:", tableKey.value);
    }
  }
);

// const formatCurrency = (value) => {

//   console.log('currency',decimalSeparator);

//   // return value ? `₹${parseFloat(value).toFixed(2)}` : '-';
//   return value ? `${currency}${parseFloat(value).toFixed(2)}` : "-";
// };


// const formatCurrency = (value) => {
//   if (value === null || value === undefined || value === '') return '-';

//   let number = parseFloat(value);

//   if (isNaN(number)) return '-';

//   // Format to 2 decimals
//   let formatted = number.toFixed(2);

//   // Replace decimal separator dynamically
//   if (decimalSeparator !== '.') {
//     formatted = formatted.replace('.', decimalSeparator);
//   }

//   return `${currency}${formatted}`;
// };


const formatStock = (value) => {
  return value !== null && value !== undefined
    ? parseFloat(value).toFixed(2)
    : "-";
};

const shouldHighlightRow = (item) => {
  const stockPreference = preferences.value.preference_quantity;

  if (stockPreference == 1) {
    const quantity = parseFloat(item.stock) || 0;
    const minStockAlert = parseFloat(item.minStockAlert) || 0;

    if (quantity === 0) {
      return true;
    }
    if (minStockAlert >= quantity && minStockAlert > 0) {
      return true;
    }
  }

  return false;
};

const paginationInfo = computed(() => {
  if (props.recordsFiltered === 0) {
    return "0-0 of 0";
  }
  const start = props.currentPage * props.pageLength + 1;
  const end = Math.min(
    (props.currentPage + 1) * props.pageLength,
    props.recordsFiltered
  );
  return `${start}-${end} of ${props.recordsFiltered}`;
});

const totalPages = computed(() => {
  return Math.ceil(props.recordsFiltered / props.pageLength) || 1;
});

const canGoPrev = computed(() => props.currentPage > 0);
const canGoNext = computed(() => props.currentPage < totalPages.value - 1);

const handleCheckboxChange = (id, event) => {
  if (props.loading) return;

  if (event.target.checked) {
    emit("row-selected", id);
  } else {
    emit("row-deselected", id);
  }
};

const handleSelectAllChange = (event) => {
  if (props.loading) return;
  emit("toggle-select-all", event.target.checked);
};

const handlePageLengthChange = (event) => {
  if (props.loading) return;
  const newLength = parseInt(event.target.value);
  emit("page-length-change", newLength);
};

const handlePrevPage = () => {
  if (canGoPrev.value && !props.loading) {
    emit("page-change", props.currentPage - 1);
  }
};

const handleNextPage = () => {
  if (canGoNext.value && !props.loading) {
    emit("page-change", props.currentPage + 1);
  }
};
</script>

<style scoped>
/* Row highlighting for low stock */
.inventoryMinStockAlert {
  background-color: #fff3cd !important;
}

.inventoryMinStockAlert:hover {
  background-color: #ffe69c !important;
}

/* Footer */
.card-footer {
  padding: 16px 20px;
  background-color: #fff;
  border-top: 1px solid #dee2e6;
}

.footer-layout {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Left side */
.footer-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.footer-text {
  font-size: 14px;
  color: #6c757d;
  font-weight: 500;
}

.footer-select {
  height: 32px;
  padding: 4px 30px 4px 10px;
  font-size: 14px;
  color: #212529;
  border: 1px solid #ced4da;
  border-radius: 4px;
  background: white
    url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e")
    no-repeat right 8px center/12px 12px;
  appearance: none;
  cursor: pointer;
  transition: all 0.15s ease-in-out;
}

.footer-select:hover:not(:disabled) {
  border-color: #adb5bd;
}

.footer-select:focus {
  border-color: #86b7fe;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.footer-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background-color: #f8f9fa;
}

/* Right side */
.footer-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.footer-info {
  font-size: 14px;
  color: #6c757d;
  font-weight: 500;
  min-width: 80px;
  text-align: right;
}

.footer-arrow {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  background-color: white;
  color: #212529;
  cursor: pointer;
  padding: 0;
  transition: all 0.15s ease-in-out;
}

.footer-arrow:hover:not(:disabled):not(.disabled) {
  background-color: #e9ecef;
  border-color: #adb5bd;
}

.footer-arrow:active:not(:disabled):not(.disabled) {
  background-color: #dee2e6;
  transform: scale(0.95);
}

.footer-arrow:disabled,
.footer-arrow.disabled {
  opacity: 0.35;
  cursor: not-allowed;
  pointer-events: none;
  background-color: #f8f9fa;
}

.footer-arrow i {
  font-size: 14px;
  line-height: 1;
}

/* Table */
.table {
  margin-bottom: 0;
}

.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  padding: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #6c757d;
  white-space: nowrap;
}

.table tbody td {
  padding: 14px 12px;
  border-bottom: 1px solid #dee2e6;
  font-size: 14px;
  color: #212529;
  vertical-align: middle;
}

.table tbody tr {
  transition: background-color 0.15s ease-in-out;
}

.table tbody tr:hover:not(.loading-row) {
  background-color: #f5f5f5;
}

.btn-light {
  transition: all 0.15s ease-in-out;
}

.btn-light:hover:not(:disabled) {
  background-color: #e9ecef;
  border-color: #adb5bd;
}

.btn-light:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Mobile */
@media (max-width: 768px) {
  .card-footer {
    padding: 12px 15px;
  }

  .footer-layout {
    gap: 10px;
  }

  .footer-text {
    font-size: 13px;
  }

  .footer-select {
    height: 28px;
    padding: 2px 26px 2px 8px;
    font-size: 13px;
    background-size: 10px 10px;
    background-position: right 6px center;
  }

  .footer-right {
    gap: 8px;
  }

  .footer-info {
    font-size: 13px;
    min-width: 70px;
  }

  .footer-arrow {
    width: 28px;
    height: 28px;
  }

  .footer-arrow i {
    font-size: 12px;
  }
}

@media (max-width: 480px) {
  .footer-text {
    font-size: 12px;
  }

  .footer-select {
    font-size: 12px;
  }

  .footer-info {
    font-size: 12px;
    min-width: 60px;
  }
}
</style>
