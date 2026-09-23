<script setup>
import { ref, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useReportStore } from "@/modules/GroceryIndia/stores/report.js";

const { t } = useI18n();
const reportStore = useReportStore();

const props = defineProps({
  transactionId: {
    type: [String, Number],
    default: null,
  },
});

const emit = defineEmits(["close", "mark-paid"]);

const showModal = ref(false);
const loading = ref(false);
const error = ref(null);

// ✅ Transform API data to match modal template structure
const transaction = computed(() => {
  const detail = reportStore.transactionDetail;
  if (!detail) return null;

  // Parse items from JSON string
  let items = [];
  try {
    const parsedItems = JSON.parse(detail.item_list || "[]");
    items = parsedItems.map((item) => ({
      name: item.itemName || "N/A",
      quantity: item.quantity || 0,
      unit: item.selectedUnit || "",
      total: parseFloat(item.amount || 0),
    }));
  } catch (e) {
    console.error("Error parsing items:", e);
  }

  return {
    id: detail.invoice_number || "N/A",
    total: parseFloat(detail.total_price || 0),
    user: detail.user_name || "N/A",
    status: detail.status || "paid", // Adjust based on your API
    date: formatDateTime(detail.created_at),
    items: items,
    subtotal: parseFloat(detail.subtotal || detail.total_price || 0),
    tax: parseFloat(detail.tax || 0),
    discount: parseFloat(detail.discount || 0),
  };
});

const formatDateTime = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "Invalid Date";

  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();

  let hours = date.getHours();
  const minutes = date.getMinutes().toString().padStart(2, "0");
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12 || 12;

  return `${day}/${month}/${year} - ${hours}:${minutes} ${ampm}`;
};

const handleClose = () => {
  showModal.value = false;
  reportStore.clearTransactionDetail();
  emit("close");
};

const handleMarkPaid = () => {
  if (transaction.value) {
    emit("mark-paid", props.transactionId);
  }
};

const isUnpaid = computed(() => {
  return transaction.value?.status === "unpaid";
});

// ✅ Watch for transactionId changes and fetch data
watch(
  () => props.transactionId,
  async (newId) => {
    if (newId) {
      loading.value = true;
      error.value = null;

      try {
        await reportStore.fetchTransactionDetails(newId);
        showModal.value = true;
      } catch (err) {
        error.value = "Failed to load transaction details";
        console.error("Error fetching transaction:", err);
      } finally {
        loading.value = false;
      }
    }
  },
  { immediate: true }
);

// ✅ Expose show method for parent component
const show = () => {
  if (props.transactionId) {
    showModal.value = true;
  }
};

defineExpose({
  show,
  handleClose,
});
</script>

<template>
  <div
    class="modal fade"
    :class="{ show: showModal, 'd-block': showModal }"
    tabindex="-1"
    :style="{ display: showModal ? 'block' : 'none' }"
    @click.self="handleClose"
  >
    <div
      class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm-custom"
    >
      <!-- Loading State -->
      <div v-if="loading" class="modal-content">
        <div class="modal-body text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 text-muted">Loading transaction details...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="modal-content">
        <div class="modal-header border-0">
          <h6 class="modal-title text-danger">Error</h6>
          <button type="button" class="btn-close" @click="handleClose"></button>
        </div>
        <div class="modal-body text-center py-4">
          <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
          <p class="mt-3 text-muted">{{ error }}</p>
          <button class="btn btn-sm btn-secondary mt-2" @click="handleClose">
            Close
          </button>
        </div>
      </div>

      <!-- Transaction Details -->
      <div class="modal-content" v-else-if="transaction">
        <!-- Modal Header -->
        <div class="modal-header border-0 pb-2">
          <h6 class="modal-title fw-bold mb-0">{{ t("common.Transaction Details") }}</h6>
          <button
            type="button"
            class="btn-close btn-close-sm"
            @click="handleClose"
          ></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body px-3 py-3">
          <!-- Invoice Info Card -->
          <div class="card border-0 bg-light mb-3">
            <div class="card-body p-3">
              <div class="row g-3">
                <div class="col-6">
                  <div class="small text-muted mb-1">{{ t("common.Invoice") }}</div>
                  <p class="text-primary fw-bold mb-0 small">
                    {{ transaction.id }}
                  </p>
                </div>
                <div class="col-6 text-end">
                  <div class="small text-muted mb-1">{{ t("common.Total") }}</div>
                  <h6 class="text-success fw-bold mb-0">
                    {{ $formatCurrency(transaction.total) }}
                  </h6>
                </div>
                <div class="col-6">
                  <div class="small text-muted mb-1">{{ t("common.User") }}</div>
                  <span class="badge bg-success-subtle text-success px-2 py-1 small">
                    {{ transaction.user }}
                  </span>
                </div>
                <div class="col-6">
                  <div class="small text-muted mb-1">{{ t("common.Status") }}</div>
                  <span
                    class="badge px-2 py-1 small"
                    :class="
                      transaction.status === 'paid'
                        ? 'bg-success'
                        : 'bg-warning text-dark'
                    "
                  >
                    {{ transaction.status.toUpperCase() }}
                  </span>
                </div>
                <div class="col-12">
                  <div class="small text-muted mb-1">{{ t("common.Date & Time") }}</div>
                  <p class="mb-0 small">{{ transaction.date }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Items Section -->
          <div class="mb-3">
            <h6 class="fw-bold mb-2 small">{{ t("common.Items") }}</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="small">{{ t("common.Products") }}</th>
                    <th class="text-center small">{{ t("common.Qty") }}</th>
                    <th class="text-end small">{{ t("common.Price") }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in transaction.items" :key="index">
                    <td class="small">{{ item.name }}</td>
                    <td class="text-center small">{{ item.quantity }} {{ item.unit }}</td>
                    <td class="text-end small fw-semibold">
                      {{ $formatCurrency(item.total) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Summary Section -->
          <div class="card border-0 bg-light">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between mb-2">
                <span class="small text-muted">{{ t("common.Subtotal") }}</span>
                <span class="small fw-semibold">
                  {{ $formatCurrency(transaction.subtotal) }}
                </span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="small text-muted">{{ t("common.Tax") }}</span>
                <span class="small fw-semibold">
                  {{ $formatCurrency(transaction.tax) }}
                </span>
              </div>
              <hr class="my-2" />
              <div class="d-flex justify-content-between">
                <span class="fw-bold small">{{ t("common.Total") }}</span>
                <span class="fw-bold text-success">
                  {{ $formatCurrency(transaction.total) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer border-0 pt-2 pb-3 px-3">
          <button
            type="button"
            class="btn btn-sm btn-secondary px-3"
            @click="handleClose"
          >
            {{ t("common.Close") }}
          </button>
          <button
            v-if="isUnpaid"
            type="button"
            class="btn btn-sm btn-success px-3"
            @click="handleMarkPaid"
          >
            <i class="bi bi-check-circle me-1"></i>{{ t("common.Mark Paid") }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Backdrop -->
  <div v-if="showModal" class="modal-backdrop fade show" @click="handleClose"></div>
</template>

<style scoped>
/* Modal Size - Smaller */
.modal-sm-custom {
  max-width: 450px;
}

/* Mobile Responsive */
@media (max-width: 576px) {
  .modal-sm-custom {
    max-width: 95%;
    margin: 0.5rem auto;
  }

  .modal-body {
    padding: 1rem !important;
  }

  .modal-header,
  .modal-footer {
    padding: 0.75rem 1rem !important;
  }
}

@media (max-width: 400px) {
  .modal-sm-custom {
    max-width: 98%;
    margin: 0.25rem auto;
  }

  .card-body {
    padding: 0.75rem !important;
  }

  .table {
    font-size: 0.8rem;
  }
}

.modal.show {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}

.table th,
.table td {
  padding: 0.5rem;
}

.table th {
  font-weight: 600;
  color: #6c757d;
  background-color: #f8f9fa;
}

.card {
  border-radius: 8px;
}

.badge {
  font-size: 0.75rem;
  font-weight: 500;
}

/* Scrollable Modal Body */
.modal-dialog-scrollable .modal-body {
  overflow-y: auto;
  max-height: calc(100vh - 200px);
}

@media (max-width: 576px) {
  .modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 150px);
  }
}

/* Smooth transitions */
.modal-content {
  border-radius: 12px;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Small text adjustments */
.small {
  font-size: 0.875rem;
}
</style>
