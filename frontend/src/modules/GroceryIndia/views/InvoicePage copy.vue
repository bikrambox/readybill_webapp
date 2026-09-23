<template>
  <!-- Loading state -->
  <div v-if="isLoading" class="invoice-state-wrapper">
    <div class="invoice-state-box">
      <div class="spinner-border text-success mb-3" role="status">
        <span class="visually-hidden">{{ $t("common.Loading") }}</span>
      </div>
      <p class="text-muted mb-0">
        {{ $t("common.Please wait while we prepare your invoice") }}...
      </p>
    </div>
  </div>

  <!-- Error state -->
  <div v-else-if="invoiceError" class="invoice-state-wrapper">
    <div class="invoice-state-box">
      <div class="state-icon mb-3">
        <i class="bi bi-clock-history text-warning"></i>
      </div>
      <h6 class="fw-bold mb-2">{{ $t("common.Link Expired") }}</h6>
      <p class="text-muted small mb-1">
        {{ $t("common.This invoice link is no longer valid") }}.
      </p>
      <p class="text-muted small mb-0">
        {{ $t("common.Please close this tab and open the invoice again") }}.
      </p>
    </div>
  </div>

  <!-- Invoice component — dynamically selected by format -->
  <!-- <component v-else-if="invoiceComponent" :is="invoiceComponent" /> -->

  <!-- Invoice component — dynamically selected by format -->
  <div v-else-if="invoiceComponent">
    <div class="invoice-toolbar no-print">
      <button type="button" class="btn btn-success" @click="handlePrint">
        <i class="bi bi-printer me-2"></i>
        {{ $t("common.Print") }}
      </button>
    </div>

    <component :is="invoiceComponent" />
  </div>

  <!-- No data fallback -->
  <div v-else class="invoice-state-wrapper">
    <div class="invoice-state-box">
      <div class="state-icon mb-3">
        <i class="bi bi-file-earmark-x text-secondary"></i>
      </div>
      <h6 class="fw-bold text-secondary mb-2">{{ $t("common.Invoice Not Found") }}</h6>
      <p class="text-muted small mb-0">{{ $t("common.invoic_not_found") }}.</p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, nextTick } from "vue";
import { useRoute } from "vue-router";
import { storeToRefs } from "pinia";
import { useInvoiceStore } from "@/modules/GroceryIndia/stores/invoiceStore";
import InvoiceA4 from "@/modules/GroceryIndia/components/invoice/InvoiceA4.vue";
import Invoice80mm from "@/modules/GroceryIndia/components/invoice/Invoice80mm.vue";
import Invoice50mm from "@/modules/GroceryIndia/components/invoice/Invoice50mm.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const route = useRoute();
const invoiceStore = useInvoiceStore();

// Reactive state from store
const { invoiceData, loading: isLoading, error: invoiceError } = storeToRefs(
  invoiceStore
);

// Token comes from route param
const token = computed(() => route.params.bill_id);

// Dynamically select invoice component based on preference_invoice_format
const invoiceComponent = computed(() => {
  if (!invoiceData.value?.preferences) return null;

  const format = invoiceData.value.preferences.preference_invoice_format;

  switch (format) {
    case 0:
      return InvoiceA4;
    case 1:
      return Invoice80mm;
    case 2:
      return Invoice50mm;
    default:
      return InvoiceA4;
  }
});

// Fetch invoice on mount using token
onMounted(async () => {
  try {
    // await invoiceStore.fetchInvoice(token.value);
  } catch {
    // Error is already captured in invoiceStore.error — no extra handling needed
  }
});

// Reset store state when leaving the page
onUnmounted(() => {
  invoiceStore.resetInvoice();
});

const handlePrint = async () => {
  await nextTick();
  setTimeout(() => {
    window.print();
  }, 300);
};
</script>

<style scoped>
/* ── State Wrapper ── */
.invoice-state-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  padding: 1rem;
}

/* ── State Box ── */
.invoice-state-box {
  text-align: center;
  padding: 2.5rem 2rem;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
  max-width: 360px;
  width: 100%;
}

/* ── Icon ── */
.state-icon i {
  font-size: 2.8rem;
}

.invoice-toolbar {
  position: sticky;
  top: 0;
  z-index: 1000;
  display: flex;
  justify-content: flex-end;
  padding: 12px 16px;
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

@media print {
  .no-print {
    display: none !important;
  }
}

/* ── Mobile ── */
@media (max-width: 576px) {
  .invoice-state-box {
    padding: 2rem 1.25rem;
    border-radius: 10px;
  }

  .state-icon i {
    font-size: 2.2rem;
  }
}
</style>
