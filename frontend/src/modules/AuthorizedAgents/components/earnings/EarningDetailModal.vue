<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop" @click.self="$emit('close')">
      <div class="detail-modal">
        <!-- Header -->
        <div class="modal-header-bar">
          <div>
            <span class="transaction-label">{{ $t("common.Transaction ID") }}</span>
            <span class="transaction-id">
              {{ !isEmpty(detail?.transaction_id) ? detail.transaction_id : "NA" }}
            </span>
          </div>
          <button
            class="close-btn"
            @click="$emit('close')"
            :aria-label="$t('common.Close')"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="detail-loading">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
          </div>
          <p class="text-muted mt-2 small mb-0">{{ $t("common.Loading data") }}...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="detail-error">
          <i
            class="bi bi-exclamation-circle text-danger d-block mb-2"
            style="font-size: 2rem"
          ></i>
          <p class="text-muted mb-0">{{ error }}</p>
        </div>

        <!-- Content -->
        <div v-else-if="detail" class="modal-body-content">
          <!-- Agent Info -->
          <div class="agent-section">
            <img
              v-if="detail.agent_photo"
              :src="detail.agent_photo"
              :alt="detail.agent_name"
              class="agent-avatar"
            />
            <div v-else class="agent-avatar-placeholder">
              <i class="bi bi-person-fill"></i>
            </div>
            <div>
              <div class="agent-name">{{ detail.agent_name }}</div>
              <div class="agent-sub-label">{{ $t("common.Agent") }}</div>
            </div>
          </div>

          <hr class="divider" />

          <!-- Detail Rows -->
          <div class="detail-grid">
            <!-- Shop Name -->
            <div class="detail-row">
              <span class="detail-label">
                <i class="bi bi-shop me-2 text-primary"></i>
                {{ $t("common.Shop Name") }}
              </span>
              <span class="detail-value">
                {{ detail.shop_name }}
                <small v-if="detail.shop_entity_id" class="entity-id">
                  #{{ detail.shop_entity_id }}
                </small>
              </span>
            </div>

            <!-- Date of Subscription -->
            <div class="detail-row">
              <span class="detail-label">
                <i class="bi bi-calendar-event me-2 text-primary"></i>
                {{ $t("common.Date of Subscription") }}
              </span>
              <span class="detail-value">{{
                formatDate(detail.date_of_subscription)
              }}</span>
            </div>

            <!-- Amount (Commission) -->
            <div class="detail-row">
              <span class="detail-label">
                <i class="bi bi-currency-rupee me-2 text-success"></i>
                {{ $t("common.Amount") }} ({{ $t("common.Commission") }})
              </span>
              <span class="detail-value amount-highlight">
                {{ $formatCurrency(detail.amount) }}
              </span>
            </div>

            <!-- Amount Paid On -->
            <div class="detail-row">
              <span class="detail-label">
                <i class="bi bi-calendar-check me-2 text-primary"></i>
                {{ $t("common.Amount Paid On") }}
              </span>
              <span class="detail-value">
                {{
                  !isEmpty(detail.agent_payment_date)
                    ? formatDate(detail.agent_payment_date)
                    : "NA"
                }}
              </span>
            </div>

            <!-- Payment Mode -->
            <div class="detail-row">
              <span class="detail-label">
                <i class="bi bi-credit-card me-2 text-primary"></i>
                {{ $t("common.Payment Mode") }}
              </span>
              <span class="detail-value">
                <span v-if="!isEmpty(detail.payment_mode)" class="payment-mode-badge">
                  {{ detail.payment_mode }}
                </span>
                <span v-else class="na-text">NA</span>
              </span>
            </div>

            <!-- Payment Status (only if present in response) -->
            <div class="detail-row" v-if="detail.status">
              <span class="detail-label">
                <i class="bi bi-info-circle me-2 text-primary"></i>
                {{ $t("common.Payment Status") }}
              </span>
              <span class="detail-value">
                <span
                  class="badge rounded-pill"
                  :class="
                    detail.status === 'paid'
                      ? 'bg-success-subtle text-success'
                      : 'bg-warning-subtle text-warning'
                  "
                >
                  <i
                    class="bi me-1"
                    :class="
                      detail.status === 'paid' ? 'bi-check-circle-fill' : 'bi-clock-fill'
                    "
                  ></i>
                  {{ detail.status === "paid" ? $t("common.Paid") : $t("common.Unpaid") }}
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  show: { type: Boolean, default: false },
  detail: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
});

defineEmits(["close"]);

// API returns "–" (en-dash) for missing values — treat as empty
const isEmpty = (val) => {
  if (!val) return true;
  const str = String(val).trim();
  return str === "" || str === "–" || str === "-";
};

const formatDate = (value) => {
  if (!value || isEmpty(value)) return "NA";
  const d = new Date(value);
  if (isNaN(d)) return "NA";
  return d.toLocaleDateString("en-IN", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 1050;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 0;
}
@media (min-width: 576px) {
  .modal-backdrop {
    align-items: center;
    padding: 16px;
  }
}

.detail-modal {
  background: #fff;
  width: 100%;
  max-width: 520px;
  border-radius: 16px 16px 0 0;
  max-height: 90dvh;
  overflow-y: auto;
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.12);
  animation: slideUp 0.25s ease;
}
@media (min-width: 576px) {
  .detail-modal {
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    animation: fadeIn 0.2s ease;
  }
}

@keyframes slideUp {
  from {
    transform: translateY(40px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.97);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Header */
.modal-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 18px 20px 14px;
  border-bottom: 1px solid #dee2e6;
  position: sticky;
  top: 0;
  background: #fff;
  z-index: 1;
}
.transaction-label {
  display: block;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #6c757d;
  margin-bottom: 4px;
}
.transaction-id {
  font-size: 15px;
  font-weight: 700;
  color: #212529;
  font-family: monospace;
}
.close-btn {
  background: none;
  border: none;
  color: #6c757d;
  font-size: 16px;
  padding: 4px;
  line-height: 1;
  cursor: pointer;
  transition: color 0.15s;
}
.close-btn:hover {
  color: #212529;
}

/* Agent section */
.agent-section {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px 20px 0;
}
.agent-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #dee2e6;
  flex-shrink: 0;
}
.agent-avatar-placeholder {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #adb5bd;
  flex-shrink: 0;
}
.agent-name {
  font-size: 16px;
  font-weight: 700;
  color: #212529;
}
.agent-sub-label {
  font-size: 12px;
  color: #6c757d;
  margin-top: 2px;
}

.divider {
  margin: 14px 20px;
  border-color: #f0f0f0;
}

/* Detail rows */
.detail-grid {
  padding: 0 20px 24px;
  display: flex;
  flex-direction: column;
}
.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f5f5f5;
  gap: 12px;
}
.detail-row:last-child {
  border-bottom: none;
}
.detail-label {
  font-size: 13px;
  color: #6c757d;
  white-space: nowrap;
  flex-shrink: 0;
}
.detail-value {
  font-size: 14px;
  font-weight: 600;
  color: #212529;
  text-align: right;
}
.amount-highlight {
  color: #198754;
  font-size: 15px;
}
.entity-id {
  display: block;
  font-size: 11px;
  color: #6c757d;
  font-weight: 400;
  margin-top: 2px;
}
.payment-mode-badge {
  background: #e9ecef;
  color: #495057;
  font-size: 12px;
  padding: 3px 10px;
  border-radius: 20px;
  font-weight: 600;
  text-transform: capitalize;
}
.na-text {
  color: #adb5bd;
  font-weight: 400;
  font-size: 13px;
}

/* Loading / Error */
.detail-loading,
.detail-error {
  text-align: center;
  padding: 48px 24px;
}

/* Bootstrap badge overrides */
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
