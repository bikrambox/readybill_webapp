<template>
  <div :key="`mobile-earnings-${listKey}`">
    <!-- Loading -->
    <template v-if="loading">
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
        </div>
        <p class="text-muted mt-2 small">{{ $t("common.Loading data") }}...</p>
      </div>
    </template>

    <!-- Empty -->
    <template v-else-if="displayData.length === 0">
      <div class="text-center text-muted py-5">
        <i class="bi bi-wallet2 d-block mb-2" style="font-size: 3rem; color: #dee2e6"></i>
        <p class="mb-0">{{ $t("common.No items found") }}</p>
      </div>
    </template>

    <!-- Cards -->
    <template v-else>
      <div
        v-for="(item, index) in displayData"
        :key="`earn-card-${item.id}`"
        class="earnings-card mb-3"
        :class="{ 'unpaid-card': item.status === 'unpaid' }"
        @click="$emit('card-click', item)"
        style="cursor: pointer"
      >
        <div class="card-header-section">
          <!-- Row 1: Shop name + Status badge -->
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="item-title mb-0">{{ item.shop_name }}</h6>
            <span
              class="badge rounded-pill"
              :class="
                item.payment_status === 'paid'
                  ? 'bg-success-subtle text-success'
                  : 'bg-warning-subtle text-warning'
              "
            >
              <i
                class="bi me-1"
                :class="
                  item.payment_status === 'paid'
                    ? 'bi-check-circle-fill'
                    : 'bi-clock-fill'
                "
              ></i>
              {{
                item.payment_status === "paid" ? $t("common.Paid") : $t("common.Unpaid")
              }}
            </span>
          </div>

          <!-- Row 2: Commission ID -->
          <div class="commission-id-row mb-2">
            <span class="info-label">{{ $t("common.Commission ID") }}:</span>
            <span class="commission-id-value">{{ item.commission_id }}</span>
          </div>

          <!-- Row 3: Amount + Date -->
          <div class="row g-2">
            <div class="col-6">
              <div class="info-box">
                <div class="info-label">{{ $t("common.Amount") }}</div>
                <div class="info-value amount-value">
                  {{ $formatCurrency(item.agent_amount) }}
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="info-box text-end">
                <div class="info-label">{{ $t("common.Date") }}</div>
                <div class="info-value">{{ formatDate(item.created_at) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="text-center text-muted small mt-3 mb-2">
        {{ pageInfo.start }}-{{ pageInfo.end }} of {{ pageInfo.total }}
      </div>
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
          {{ $t("common.Next") }} <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  displayData: { type: Array, required: true },
  pageInfo: {
    type: Object,
    default: () => ({ start: 0, end: 0, total: 0, hasPrev: false, hasNext: false }),
  },
  loading: { type: Boolean, default: false },
});

defineEmits(["prev-page", "next-page", "card-click"]);

const listKey = ref(0);

watch(
  () => props.displayData,
  () => {
    listKey.value++;
  },
  { deep: true }
);

const formatDate = (value) => {
  if (!value) return "-";
  return new Date(value).toLocaleDateString("en-IN", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};
</script>

<style scoped>
.earnings-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: all 0.2s;
}
.unpaid-card {
  background-color: #fff9e6;
  border-left: 4px solid #ffc107;
}
.card-header-section {
  padding: 14px;
}
.item-title {
  font-size: 15px;
  font-weight: 600;
  color: #212529;
  line-height: 1.4;
}
.commission-id-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.commission-id-value {
  font-size: 12px;
  font-weight: 600;
  color: #495057;
  background-color: #e9ecef;
  padding: 2px 8px;
  border-radius: 4px;
  font-family: monospace;
}
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
.amount-value {
  color: #198754;
}
.badge {
  font-size: 12px;
  padding: 5px 10px;
}
.bg-success-subtle {
  background-color: #d1e7dd;
}
.bg-warning-subtle {
  background-color: #fff3cd;
}
</style>
