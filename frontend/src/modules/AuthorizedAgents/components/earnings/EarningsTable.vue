<template>
  <div class="card border-0 shadow-sm position-relative">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="width: 100%">
          <thead class="table-light">
            <tr>
              <th class="ps-4">#</th>
              <th>{{ $t("common.Commission ID") }}</th>
              <th>{{ $t("common.Shop Name") }}</th>
              <th>{{ $t("common.Amount") }}</th>
              <th>{{ $t("common.Payment Status") }}</th>
              <!-- <th>{{ $t("earnings.Status") }}</th> -->
              <th>{{ $t("common.Date") }}</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="!loading && data.length > 0">
              <tr
                v-for="(item, index) in data"
                :key="`row-${item.id}`"
                :class="{ 'unpaid-row': item.status === 'unpaid' }"
                class="clickable-row"
                @click="$emit('row-click', item)"
              >
                <td class="ps-4 text-muted">
                  {{ currentPage * pageLength + index + 1 }}
                </td>
                <td class="text-muted">{{ item.commission_id }}</td>
                <td class="fw-semibold">{{ item.shop_name }}</td>
                <td class="fw-semibold text-success">
                  {{ $formatCurrency(item.agent_amount) }}
                </td>
                <td>
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
                      item.payment_status === "paid"
                        ? $t("common.Paid")
                        : $t("common.Unpaid")
                    }}
                  </span>
                </td>
                <td class="text-muted">{{ formatDate(item.created_at) }}</td>
              </tr>
            </template>

            <template v-else-if="!loading && data.length === 0">
              <tr>
                <td colspan="5" class="text-center py-5 text-muted">
                  <i
                    class="bi bi-wallet2 d-block mb-2"
                    style="font-size: 2.5rem; color: #dee2e6"
                  ></i>
                  {{ $t("common.No items found") }}
                </td>
              </tr>
            </template>

            <template v-else>
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                  <div
                    class="spinner-border spinner-border-sm text-primary"
                    role="status"
                  >
                    <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
                  </div>
                  <span class="ms-2">{{ $t("common.Loading data") }}...</span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Footer Pagination -->
    <div class="card-footer bg-white">
      <div class="footer-layout">
        <div class="footer-left">
          <span class="footer-text">{{ $t("common.Rows per page") }}:</span>
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
import { computed } from "vue";

const props = defineProps({
  data: { type: Array, required: true },
  currentPage: { type: Number, default: 0 },
  pageLength: { type: Number, default: 10 },
  recordsTotal: { type: Number, default: 0 },
  recordsFiltered: { type: Number, default: 0 },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(["page-change", "page-length-change", "row-click"]);

const formatDate = (value) => {
  if (!value) return "-";
  return new Date(value).toLocaleDateString("en-IN", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const paginationInfo = computed(() => {
  if (props.recordsFiltered === 0) return "0-0 of 0";
  const start = props.currentPage * props.pageLength + 1;
  const end = Math.min((props.currentPage + 1) * props.pageLength, props.recordsFiltered);
  return `${start}-${end} of ${props.recordsFiltered}`;
});

const totalPages = computed(
  () => Math.ceil(props.recordsFiltered / props.pageLength) || 1
);
const canGoPrev = computed(() => props.currentPage > 0);
const canGoNext = computed(() => props.currentPage < totalPages.value - 1);

const handlePageLengthChange = (e) => {
  if (props.loading) return;
  emit("page-length-change", parseInt(e.target.value));
};
const handlePrevPage = () => {
  if (canGoPrev.value && !props.loading) emit("page-change", props.currentPage - 1);
};
const handleNextPage = () => {
  if (canGoNext.value && !props.loading) emit("page-change", props.currentPage + 1);
};
</script>

<style scoped>
.unpaid-row {
  --bs-table-bg: #fff9e6 !important;
  background-color: #fff9e6 !important;
}
.unpaid-row:hover {
  --bs-table-bg: #fff3cd !important;
  background-color: #fff3cd !important;
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

/* Footer — reused from UpdateInventoryTable */
.card-footer {
  padding: 16px 20px;
  border-top: 1px solid #dee2e6;
}
.footer-layout {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.footer-left {
  display: flex;
  align-items: center;
  gap: 8px;
}
.footer-right {
  display: flex;
  align-items: center;
  gap: 12px;
}
.footer-text {
  font-size: 14px;
  color: #6c757d;
  font-weight: 500;
}
.footer-info {
  font-size: 14px;
  color: #6c757d;
  font-weight: 500;
  min-width: 80px;
  text-align: right;
}
.footer-select {
  height: 32px;
  padding: 4px 30px 4px 10px;
  font-size: 14px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  appearance: none;
  background: white
    url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e")
    no-repeat right 8px center/12px 12px;
  cursor: pointer;
}
.footer-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background-color: #f8f9fa;
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
}
.footer-arrow:disabled,
.footer-arrow.disabled {
  opacity: 0.35;
  cursor: not-allowed;
  pointer-events: none;
}
.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  padding: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #6c757d;
}
.table tbody td {
  padding: 14px 12px;
  border-bottom: 1px solid #dee2e6;
  font-size: 14px;
  vertical-align: middle;
}

.clickable-row {
  cursor: pointer;
}
.clickable-row:hover td {
  background-color: #f0f4ff !important;
}
</style>
