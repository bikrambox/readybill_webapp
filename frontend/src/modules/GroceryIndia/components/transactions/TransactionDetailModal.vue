<script setup>
import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { defineProps, defineEmits, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { useInvoiceStore } from "@/modules/GroceryIndia/stores/invoiceStore";

const { t } = useI18n();
const router = useRouter();

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  transaction: {
    type: Object,
    default: null,
  },
  showMarkPaid: {
    type: Boolean,
    default: false,
  },
});

// ── Preferences ──────────────────────────────────────────────────────────────
const preferencesStore = useUserPreferencesStore();
const { preferences } = storeToRefs(preferencesStore);
const invoiceStore = useInvoiceStore();

onMounted(async () => {
  await preferencesStore.fetchUserPreferences();
});

// GST compliance — drives field visibility in CustomerInfoModal
const isGstCompliant = computed(
  () =>
    preferences.value.preference_invoice_gst_complaint === 1 ||
    preferences.value.preference_invoice_gst_complaint === true
);

const emit = defineEmits(["close", "mark-paid"]);

const handleClose = () => {
  emit("close");
};

const handleMarkPaid = () => {
  if (props.transaction) {
    emit("mark-paid", props.transaction.id);
  }
};

// const showInvoice = (billId) => {
//   const routeData = router.resolve({
//     name: "Invoice",
//     params: { bill_id: billId },
//   });
//   window.open(routeData.href, "_blank");
// };

// const showInvoice = async (billId) => {
//   try {
//     const token = await invoiceStore.getInvoiceToken(billId);

//     const routeData = router.resolve({
//       name: "Invoice",
//       params: { bill_id: token },
//     });
//     window.open(routeData.href, "_blank");
//   } catch (err) {
//     console.error("Failed to open invoice:", err);
//   }
// };

const showInvoice = async (billId) => {
  try {
    const token = await invoiceStore.getInvoiceToken(billId);

    const routeData = router.resolve({
      name: "Invoice",
      params: { bill_id: token },
    });

    const newTab = window.open(routeData.href, "_blank", "noopener,noreferrer");

    if (newTab) {
      newTab.opener = null;
    }
  } catch (err) {
    console.error("Failed to open invoice:", err);
  }
};

const isUnpaid = computed(() => {
  return props.transaction?.status === "unpaid";
});

const calculatedTotal = computed(() => {
  if (!props.transaction || !props.transaction.items) return 0;
  return props.transaction.items.reduce((sum, item) => {
    const itemTotal = parseFloat(item.total || 0);
    return sum + (item.isRefund == "1" ? -itemTotal : itemTotal);
  }, 0);
});

const calculatedSubtotal = computed(() => {
  if (!props.transaction || !props.transaction.items) return 0;
  return props.transaction.items.reduce((sum, item) => {
    const itemAmount = parseFloat(item.total || 0);
    return sum + (item.isRefund == "1" ? -itemAmount : itemAmount);
  }, 0);
});

const calculatedTax = computed(() => {
  if (!props.transaction || !props.transaction.items) return 0;
  const taxValue = props.transaction.items.reduce((sum, item) => {
    const tax1 = parseFloat(item.tax1?.amount || 0);
    const tax2 = parseFloat(item.tax2?.amount || 0);
    const taxTotal = tax1 + tax2;
    return sum + (item.isRefund == "1" ? -taxTotal : taxTotal);
  }, 0);
  return Math.abs(taxValue);
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
        <!-- ── Modal Header ── -->
        <div class="modal-header border-0 pb-0">
          <div class="d-flex align-items-center gap-2">
            <div class="modal-header-icon">
              <i class="bi bi-receipt"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold mb-0">
                {{ $t("common.Transaction Details") }}
              </h6>
              <small
                class="fw-bold px-2 py-0"
                style="
                  font-size: 0.75rem;
                  letter-spacing: 0.04em;
                  background: #e8f5e9;
                  color: #1b5e20;
                  border-radius: 5px;
                  display: inline-block;
                  margin-top: 2px;
                "
              >
                #{{ transaction.id }}
              </small>
            </div>
          </div>
          <button
            type="button"
            class="btn-close btn-close-sm"
            @click="handleClose"
          ></button>
        </div>

        <!-- ── Modal Body ── -->
        <div class="modal-body px-3 py-3">
          <!-- Section: Invoice Overview -->
          <div class="section-block mb-3">
            <div class="section-heading">
              <i class="bi bi-file-earmark-text me-1"></i>
              {{ $t("common.Invoice") }}
            </div>
            <div class="card border-0 section-card">
              <div class="card-body p-3">
                <div class="row g-2">
                  <div class="col-6">
                    <div class="info-label">{{ $t("common.Sell By") }}</div>
                    <span class="badge bg-success-subtle text-success px-2 py-1 small">
                      {{ transaction.user }}
                    </span>
                  </div>
                  <div class="col-6 text-end">
                    <div class="info-label">{{ $t("common.Status") }}</div>
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
                    <div class="info-label">{{ $t("common.Date & Time") }}</div>
                    <p class="mb-0 small fw-medium">{{ transaction.date }}</p>
                  </div>
                  <div class="col-12">
                    <div class="info-label">{{ $t("common.Total") }}</div>
                    <h5
                      class="fw-bold mb-0 text-nowrap"
                      :class="calculatedTotal >= 0 ? 'text-success' : 'text-danger'"
                    >
                      <span v-if="calculatedTotal < 0">-</span>
                      {{ $formatCurrency(Math.abs(calculatedTotal)) }}
                    </h5>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: Customer Info -->
          <div class="section-block mb-3" v-if="transaction.customer_mobile">
            <div class="section-heading">
              <i class="bi bi-person me-1"></i>
              {{ $t("common.Customer Details") }}
            </div>
            <div class="card border-0 section-card">
              <div class="card-body p-3">
                <div class="row g-2">
                  <div class="col-6">
                    <div class="info-label">{{ $t("common.Name") }}</div>
                    <p class="text-primary fw-semibold mb-0 small">
                      {{ transaction.customer_name }}
                    </p>
                  </div>
                  <div class="col-6 text-end">
                    <div class="info-label">{{ $t("common.Mobile") }}</div>
                    <p class="text-primary fw-semibold mb-0 small">
                      {{ transaction.customer_mobile }}
                    </p>
                  </div>
                  <div class="col-12">
                    <div class="info-label">{{ $t("common.Address") }}</div>
                    <p class="mb-0 small">{{ transaction.customer_address }}</p>
                  </div>
                  <div class="col-6">
                    <div class="info-label">{{ $t("common.State") }}</div>
                    <p class="mb-0 small">{{ transaction.customer_state }}</p>
                  </div>
                  <div class="col-6 text-end">
                    <div class="info-label">{{ $t("common.GSTIN") }}</div>
                    <p class="mb-0 small font-monospace">
                      {{ transaction.customer_gstin || "—" }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: Items -->
          <div class="section-block mb-3">
            <div class="section-heading">
              <i class="bi bi-cart3 me-1"></i>
              {{ $t("common.Items") }}
              <span class="badge bg-secondary ms-1" style="font-size: 0.65rem">
                {{ transaction.items?.length }}
              </span>
            </div>
            <div class="table-responsive section-card rounded-3 overflow-hidden">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-head">
                  <tr>
                    <th class="small ps-3">{{ $t("common.Products") }}</th>
                    <th class="text-center small">{{ $t("common.Qty") }}</th>
                    <th class="text-end small pe-3">{{ $t("common.Price") }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in transaction.items"
                    :key="index"
                    :class="{ 'row-refund': item.isRefund == '1' }"
                  >
                    <td class="small ps-3">
                      {{ item.name }}
                      <span
                        v-if="item.isRefund == '1'"
                        class="badge bg-danger text-white ms-1"
                        style="font-size: 0.62rem"
                      >
                        {{ $t("common.Refund") }}
                      </span>
                    </td>
                    <td class="text-center small">{{ item.quantity }} {{ item.unit }}</td>
                    <td
                      class="text-end small fw-semibold text-nowrap pe-3"
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

          <!-- Section: Payment Summary -->
          <div class="section-block">
            <div class="section-heading">
              <i class="bi bi-calculator me-1"></i>
              {{ $t("common.Payment Summary") }}
            </div>
            <div class="card border-0 section-card">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between mb-2">
                  <span class="small text-muted">{{ $t("common.Subtotal") }}</span>
                  <span
                    class="small fw-semibold text-nowrap"
                    :class="calculatedSubtotal >= 0 ? '' : 'text-danger'"
                  >
                    <span v-if="calculatedSubtotal < 0">-</span>
                    {{ $formatCurrency(Math.abs(calculatedSubtotal)) }}
                  </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="small text-muted">{{ $t("common.Tax") }}</span>
                  <span class="small fw-semibold text-nowrap text-secondary">
                    + {{ $formatCurrency(calculatedTax) }}
                  </span>
                </div>
                <hr class="my-2 summary-divider" />
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold small">{{ $t("common.Total") }}</span>
                  <span
                    class="fw-bold total-amount text-nowrap"
                    :class="calculatedTotal >= 0 ? 'text-success' : 'text-danger'"
                  >
                    <span v-if="calculatedTotal < 0">-</span>
                    {{ $formatCurrency(Math.abs(calculatedTotal)) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Modal Footer ── -->
        <div class="modal-footer border-0 pt-2 pb-3 px-3 gap-2">
          <button
            type="button"
            class="btn btn-sm btn-outline-primary px-3"
            @click="showInvoice(transaction.bill_id)"
          >
            <i class="bi bi-file-earmark-arrow-down me-1"></i>{{ $t("common.Invoice") }}
          </button>

          <button
            v-if="showMarkPaid && isUnpaid"
            type="button"
            class="btn btn-sm btn-success px-3"
            @click="handleMarkPaid"
          >
            <i class="bi bi-check-circle me-1"></i>{{ $t("common.Mark Paid") }}
          </button>

          <button
            type="button"
            class="btn btn-sm btn-secondary px-3 ms-auto"
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
/* ── Modal Size ── */
.modal-sm-custom {
  max-width: 460px;
}

.modal.show {
  background-color: rgba(0, 0, 0, 0.45);
}

.modal-content {
  border-radius: 14px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.14);
  border: none;
}

/* ── Header Icon ── */
.modal-header-icon {
  width: 32px;
  height: 32px;
  background: #e8f5e9;
  color: #2e7d32;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
}

/* ── Section Headings ── */
.section-heading {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #6c757d;
  margin-bottom: 0.45rem;
  display: flex;
  align-items: center;
  padding-left: 2px;
}

/* ── Section Cards ── */
.section-card {
  background-color: #f8f9fa;
  border-radius: 10px !important;
}

/* ── Info Labels ── */
.info-label {
  font-size: 0.68rem;
  color: #9e9e9e;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 3px;
  font-weight: 600;
}

/* ── Table Styles ── */
.table-head tr th {
  background-color: #f0f0f0;
  font-weight: 700;
  color: #555;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e0e0e0;
  padding: 0.5rem;
}

.table tbody tr td {
  padding: 0.5rem;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}

.table tbody tr:last-child td {
  border-bottom: none;
}

/* ── Refund Row ── */
.row-refund {
  background-color: #fff5f5 !important;
  border-left: 3px solid #dc354560 !important;
}

.row-refund td {
  color: #b71c1c;
}

/* ── Summary ── */
.summary-divider {
  border-color: #dee2e6;
  opacity: 0.7;
}

.total-amount {
  font-size: 1.05rem;
}

/* ── Scrollable Body ── */
.modal-dialog-scrollable .modal-body {
  overflow-y: auto;
  max-height: calc(100vh - 200px);
}

/* ── Small text ── */
.small {
  font-size: 0.875rem;
}

/* ── Mobile ── */
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

  .modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 150px);
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
</style>
