<script setup>
import { defineProps, defineEmits, computed } from "vue";
import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  transaction: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "mark-paid"]);

const handleClose = () => {
  emit("close");
};

const handleMarkPaid = () => {
  if (props.transaction) {
    emit("mark-paid", props.transaction.id);
  }
};

const showInvoice = (billId) => {
  const routeData = router.resolve({
    name: "Invoice",
    params: { bill_id: billId },
  });
  window.open(routeData.href, "_blank");
};

const isUnpaid = computed(() => {
  return props.transaction?.status === "unpaid";
});

// Computed property to calculate actual total considering refunds
const calculatedTotal = computed(() => {
  if (!props.transaction || !props.transaction.items) return 0;

  return props.transaction.items.reduce((sum, item) => {
    const itemTotal = parseFloat(item.total || 0);
    return sum + (item.isRefund == "1" ? -itemTotal : itemTotal);
  }, 0);
});

// Computed property to calculate subtotal considering refunds
const calculatedSubtotal = computed(() => {
  if (!props.transaction || !props.transaction.items) return 0;

  return props.transaction.items.reduce((sum, item) => {
    const itemAmount = parseFloat(item.total || 0);
    return sum + (item.isRefund == "1" ? -itemAmount : itemAmount);
  }, 0);
});

// Computed property to calculate tax - always positive (absolute value)
const calculatedTax = computed(() => {
  if (!props.transaction || !props.transaction.items) return 0;

  const taxValue = props.transaction.items.reduce((sum, item) => {
    const tax1 = parseFloat(item.tax1?.amount || 0);
    const tax2 = parseFloat(item.tax2?.amount || 0);
    const taxTotal = tax1 + tax2;
    return sum + (item.isRefund == "1" ? -taxTotal : taxTotal);
  }, 0);

  return Math.abs(taxValue); // Always return positive value
});
</script>



<template>
  <div
    class="modal fade"
    :class="{ show: show, 'd-block': show }"
    tabindex="-1"
    :style="{ display: show ? 'block' : 'none' }"
    @click.self="handleClose"
  >
    <div
      class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm-custom"
    >
      <div class="modal-content" v-if="transaction">
        <!-- Modal Header -->
        <div class="modal-header border-0 pb-2">
          <h6 class="modal-title fw-bold mb-0">
            {{ $t("common.Transaction Details") }}
          </h6>
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
                  <div class="small text-muted mb-1">
                    {{ $t("common.Invoice") }}
                  </div>
                  <p class="text-primary fw-bold mb-0 small">
                    {{ transaction.id }}
                  </p>
                </div>
                <div class="col-6 text-end">
                  <div class="small text-muted mb-1">
                    {{ $t("common.Total") }}
                  </div>
                  <h6
                    class="fw-bold mb-0 text-nowrap"
                    :class="
                      calculatedTotal >= 0 ? 'text-success' : 'text-danger'
                    "
                  >
                    <span v-if="calculatedTotal < 0">-</span
                    >{{ $formatCurrency(Math.abs(calculatedTotal)) }}
                  </h6>
                </div>
                <div class="col-6">
                  <div class="small text-muted mb-1">
                    {{ $t("common.User") }}
                  </div>
                  <span
                    class="badge bg-success-subtle text-success px-2 py-1 small"
                  >
                    {{ transaction.user }}
                  </span>
                </div>
                <div class="col-6">
                  <div class="small text-muted mb-1">
                    {{ $t("common.Status") }}
                  </div>
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
                  <div class="small text-muted mb-1">
                    {{ $t("common.Date & Time") }}
                  </div>
                  <p class="mb-0 small">{{ transaction.date }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Items Section -->
          <div class="mb-3">
            <h6 class="fw-bold mb-2 small">{{ $t("common.Items") }}</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="small">{{ $t("common.Products") }}</th>
                    <th class="text-center small">{{ $t("common.Qty") }}</th>
                    <th class="text-end small">{{ $t("common.Price") }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in transaction.items"
                    :key="index"
                    :class="{
                      'table-danger bg-danger-subtle': item.isRefund == '1',
                    }"
                  >
                    <td class="small">
                      {{ item.name }}
                      <span
                        v-if="item.isRefund == '1'"
                        class="badge bg-danger text-white ms-1"
                        style="font-size: 0.65rem"
                      >
                        {{ $t("common.Refund") }}
                      </span>
                    </td>
                    <td class="text-center small">
                      {{ item.quantity }} {{ item.unit }}
                    </td>
                    <td
                      class="text-end small fw-semibold text-nowrap"
                      :class="{ 'text-danger': item.isRefund == '1' }"
                    >
                      <span v-if="item.isRefund == '1'">-</span
                      >{{ $formatCurrency(item.total) }}
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
                <span class="small text-muted">{{
                  $t("common.Subtotal")
                }}</span>
                <span
                  class="small fw-semibold text-nowrap"
                  :class="calculatedSubtotal >= 0 ? '' : 'text-danger'"
                >
                  <span v-if="calculatedSubtotal < 0">-</span
                  >{{ $formatCurrency(Math.abs(calculatedSubtotal)) }}
                </span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="small text-muted">{{ $t("common.Tax") }}</span>
                <span class="small fw-semibold text-nowrap">
                  {{ $formatCurrency(calculatedTax) }}
                </span>
              </div>
              <hr class="my-2" />
              <div class="d-flex justify-content-between">
                <span class="fw-bold small">{{ $t("common.Total") }}</span>
                <span
                  class="fw-bold text-nowrap"
                  :class="calculatedTotal >= 0 ? 'text-success' : 'text-danger'"
                >
                  <span v-if="calculatedTotal < 0">-</span
                  >{{ $formatCurrency(Math.abs(calculatedTotal)) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer border-0 pt-2 pb-3 px-3">
          <button
            type="button"
            class="btn btn-sm btn-success px-3"
            @click="showInvoice(transaction.bill_id)"
          >
            {{ $t("common.Invoice") }}
          </button>

          <button
            v-if="isUnpaid"
            type="button"
            class="btn btn-sm btn-success px-3"
            @click="handleMarkPaid"
          >
            <i class="bi bi-check-circle me-1"></i>{{ $t("common.Mark Paid") }}
          </button>

          <button
            type="button"
            class="btn btn-sm btn-secondary px-3"
            @click="handleClose"
          >
            {{ $t("common.Close") }}
          </button>
          
        </div>
      </div>
    </div>
  </div>
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

/* Refund row styling */
.bg-danger-subtle {
  background-color: #f8d7da !important;
}

.table-danger {
  border-left: 3px solid #dc3545;
}
</style>
