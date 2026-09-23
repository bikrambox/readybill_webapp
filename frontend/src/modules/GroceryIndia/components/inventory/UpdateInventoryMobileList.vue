<template>
  <div id="mobileInventoryList" :key="`mobile-list-${listKey}`">
    <!-- Selection Header (shows when items selected) -->
    <!-- <div v-if="selectedItems.size > 0 && !loading" class="selection-header mb-3">
      <div class="d-flex align-items-center gap-2">
        <input
          type="checkbox"
          class="form-check-input"
          :checked="selectedItems.size > 0"
          @change="$emit('deselect-all')"
        />
        <span class="text-muted small"
          >{{ selectedItems.size }} {{ $t("common.selected") }}</span
        >
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" @click="$emit('deselect-all')">
          {{ $t("common.Clear") }}
        </button>
        <button class="btn btn-sm btn-danger" @click="$emit('delete-selected')">
          <i class="bi bi-trash"></i> {{ $t("common.Delete") }} ({{ selectedItems.size }})
        </button>
      </div>
    </div> -->

    <!-- Loading State -->
    <template v-if="loading">
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
        </div>
        <p class="text-muted mt-2 small">{{ $t("common.Loading data") }}...</p>
      </div>
    </template>

    <!-- Empty State -->
    <template v-else-if="!loading && displayData.length === 0">
      <div class="text-center text-muted py-5">
        <i class="bi bi-inbox d-block mb-2" style="font-size: 3rem; color: #dee2e6"></i>
        <p class="mb-0">{{ $t("common.No items found") }}</p>
      </div>
    </template>

    <!-- Data Cards -->
    <template v-else-if="!loading && displayData.length > 0">
      <div
        v-for="item in displayData"
        :key="`mobile-card-${item.id}-${listKey}`"
        class="inventory-card mb-3"
        :class="{ 'low-stock-alert': shouldHighlight(item) }"
      >
        <div class="card-header-section">
          <div class="d-flex align-items-start gap-2">
            <input
              v-if="isAdmin"
              class="form-check-input mt-1"
              type="checkbox"
              :checked="selectedItems.has(item.id)"
              @change="toggleSelection(item.id, $event.target.checked)"
            />
            <div class="flex-grow-1">
              <h6 class="item-title mb-2">
                {{ item.name }}
              </h6>
              <div class="row g-2">
                <div class="col-6">
                  <div class="info-box">
                    <div class="info-label">{{ $t("common.SKU") }}</div>
                    <div class="info-value">{{ item.sku }}</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="info-box text-end">
                    <div class="info-label">{{ $t("common.Category") }}</div>
                    <div class="info-value">{{ item.category_name }}</div>
                  </div>
                </div>

                <div class="col-6">
                  <div class="info-box">
                    <div class="info-label">{{ $t("common.Stock Qty") }}</div>
                    <div class="info-value">{{ formatStock(item.stock) }}</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="info-box text-end">
                    <div class="info-label">{{ $t("common.Unit") }}</div>
                    <div class="info-value">{{ item.unit }}</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="info-box">
                    <div class="info-label">{{ $t("common.MRP") }}</div>
                    <div class="info-value">{{ $formatCurrency(item.mrp) }}</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="info-box text-end">
                    <div class="info-label">{{ $t("common.Rate") }}</div>
                    <div class="info-value rate-value">
                      {{ $formatCurrency(item.rate) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer-section" v-if="isAdmin">
          <div class="d-flex gap-2">
            <button
              class="btn btn-sm btn-view flex-fill"
              @click="$emit('view-item', item.id)"
            >
              <i class="bi bi-eye"></i> {{ $t("common.View") }}
            </button>
            <button
              class="btn btn-sm btn-edit flex-fill"
              @click="$emit('edit-item', item.id)"
            >
              <i class="bi bi-pencil"></i> {{ $t("common.Edit") }}
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination Info -->
      <div class="text-center text-muted small mt-3 mb-2">
        {{ pageInfo.start }}-{{ pageInfo.end }} of {{ pageInfo.total }}
      </div>

      <!-- Pagination Buttons -->
      <div class="d-flex justify-content-center gap-2 mt-2 mb-4">
        <button
          class="btn btn-sm btn-outline-secondary px-3"
          @click="$emit('prev-page')"
          :disabled="!pageInfo.hasPrev || loading"
        >
          <i class="bi bi-chevron-left"></i> {{ $t("common.Previous") }}
        </button>
        <button
          class="btn btn-sm btn-outline-secondary px-3"
          @click="$emit('next-page')"
          :disabled="!pageInfo.hasNext || loading"
        >
          Next <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { watch, ref, getCurrentInstance } from "vue";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { storeToRefs } from "pinia";

const props = defineProps({
  displayData: {
    type: Array,
    required: true,
  },
  selectedItems: {
    type: Set,
    required: true,
  },
  pageInfo: {
    type: Object,
    default: () => ({
      start: 0,
      end: 0,
      total: 0,
      hasPrev: false,
      hasNext: false,
    }),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  isAdmin: { type: Boolean, default: false },
});

const emit = defineEmits([
  "row-selected",
  "row-deselected",
  "view-item",
  "edit-item",
  "prev-page",
  "next-page",
  "deselect-all",
  "delete-selected",
]);

const preferencesStore = useUserPreferencesStore();
const { preferences } = storeToRefs(preferencesStore);

// Reactive key to force list re-render
const listKey = ref(0);

const app = getCurrentInstance();
const currency = app.appContext.config.globalProperties.$currency;
const decimalSeparator = app.appContext.config.globalProperties.$decimalSeparator;

// Watch displayData for changes and force re-render
watch(
  () => props.displayData,
  (newData, oldData) => {
    console.log("📱 Mobile list data changed:", {
      oldCount: oldData?.length || 0,
      newCount: newData.length,
      oldIds: oldData?.map((i) => i.id) || [],
      newIds: newData.map((i) => i.id),
    });

    // Force re-render by incrementing key
    listKey.value++;
    console.log("🔑 Mobile list key updated to:", listKey.value);
  },
  { deep: true }
);

// Watch pageInfo.total for changes
watch(
  () => props.pageInfo.total,
  (newVal, oldVal) => {
    console.log("📊 Mobile list total changed:", { old: oldVal, new: newVal });
    if (newVal !== oldVal) {
      listKey.value++;
      console.log("🔑 Mobile list key updated to:", listKey.value);
    }
  }
);

// const formatCurrency = (value) => {
//   return value ? `₹${parseFloat(value).toFixed(2)}` : "-";
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
  return value !== null && value !== undefined ? parseFloat(value).toFixed(2) : "-";
};

const toggleSelection = (id, checked) => {
  if (checked) {
    emit("row-selected", id);
  } else {
    emit("row-deselected", id);
  }
};

const shouldHighlight = (item) => {
  const stockPreference = preferences.value.preference_quantity;

  if (stockPreference === 1 || stockPreference === true) {
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
</script>

<style scoped>
/* Selection Header */
.selection-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Inventory Card */
.inventory-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: all 0.2s;
}

/* Low stock alert styling */
.inventory-card.low-stock-alert {
  background-color: #fff3cd;
  border-left: 4px solid #ffc107;
}

.inventory-card.low-stock-alert .card-header-section {
  background-color: #fff3cd;
}

.card-header-section {
  padding: 14px;
}

.item-title {
  font-size: 15px;
  font-weight: 600;
  color: #212529;
  margin: 0;
  line-height: 1.4;
}

/* Info Boxes */
.info-box {
  background-color: #f8f9fa;
  padding: 8px 10px;
  border-radius: 6px;
}

.info-label {
  font-size: 11px;
  color: #6c757d;
  margin-bottom: 4px;
}

.info-value {
  font-size: 14px;
  font-weight: 600;
  color: #212529;
}

.rate-value {
  color: #198754;
}

/* Card Footer */
.card-footer-section {
  padding: 12px 14px;
  background-color: #f8f9fa;
  border-top: 1px solid #e9ecef;
}

/* Buttons */
.btn-view {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  color: #495057;
  font-size: 13px;
  padding: 8px 12px;
}

.btn-view:hover {
  background-color: #e9ecef;
  border-color: #adb5bd;
}

.btn-edit {
  background-color: white;
  border: 1px solid #0d6efd;
  color: #0d6efd;
  font-size: 13px;
  padding: 8px 12px;
}

.btn-edit:hover {
  background-color: #f8f9ff;
}

.btn-edit i,
.btn-view i {
  font-size: 12px;
}

/* Pagination buttons disabled state */
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Empty state */
.text-center.text-muted.py-5 i {
  color: #dee2e6;
}
</style>
