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

  <!-- Invalid link -->
  <div v-else-if="statusCode === 0" class="invoice-state-wrapper">
    <div class="invoice-state-box">
      <div class="state-icon mb-3">
        <i class="bi bi-shield-exclamation text-danger"></i>
      </div>
      <h6 class="fw-bold mb-2">{{ $t("common.Invalid Link") }}</h6>
      <p class="text-muted small mb-1">
        {{ $t("common.This invoice link is invalid") }}.
      </p>
      <p class="text-muted small mb-0">
        {{ $t("common.invalid_invoice_link_message") }}.
      </p>
    </div>
  </div>

  <!-- Token expired -->
  <div v-else-if="statusCode === 1" class="invoice-state-wrapper">
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

  <!-- Invoice component -->
  <component
    v-else-if="statusCode === 2 && invoiceComponent"
    :is="invoiceComponent"
    :auto-print="true"
  />

  <!-- No data fallback -->
  <div v-else class="invoice-state-wrapper">
    <div class="invoice-state-box">
      <div class="state-icon mb-3">
        <i class="bi bi-file-earmark-x text-secondary"></i>
      </div>
      <h6 class="fw-bold text-secondary mb-2">
        {{ $t("common.Invoice Not Found") }}
      </h6>
      <p class="text-muted small mb-0">
        {{ errorMessage || $t("common.invoic_not_found") }}.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, onUnmounted } from "vue";
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

const { invoiceData, loading: isLoading, errorMessage, statusCode } = storeToRefs(
  invoiceStore
);

const token = computed(() => route.params.bill_id);

const invoiceComponent = computed(() => {
  if (!invoiceData.value?.preferences) return null;

  const format = Number(invoiceData.value.preferences.preference_invoice_format);

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

watch(
  () => route.params.bill_id,
  async (newToken) => {
    if (!newToken) return;

    try {
      await invoiceStore.fetchInvoice(newToken);
    } catch {
      // already handled in store
    }
  },
  { immediate: true }
);

onUnmounted(() => {
  invoiceStore.resetInvoice();
});
</script>

<style scoped>
.invoice-state-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  padding: 1rem;
}

.invoice-state-box {
  text-align: center;
  padding: 2.5rem 2rem;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
  max-width: 360px;
  width: 100%;
}

.state-icon i {
  font-size: 2.8rem;
}

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
