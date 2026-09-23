<template>
  <div class="subscription-manager">
    <div class="card-label mb-3">
      <i class="bi bi-patch-check me-2 text-primary"></i>
      <span>Subscription Management</span>
    </div>

    <!-- Submit Error -->
    <div v-if="submitError" class="alert alert-danger py-2 mt-3 rounded-3 small">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ submitError }}
    </div>

    <!-- Current Status Banner -->
    <div
      class="status-banner"
      :class="`status-${shop?.subscriptionStatus ?? 'inactive'}`"
    >
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <div class="status-label">Current Status</div>
          <div class="status-value text-capitalize">
            {{ shop?.subscriptionStatus ?? "inactive" }}
          </div>
          <div v-if="shop?.subscriptionExpiry" class="status-expiry">
            {{ shop.subscriptionStatus === "active" ? "Expires" : "Expired" }} on
            {{ formatDate(shop.subscriptionExpiry) }}
          </div>
        </div>
        <SubscriptionBadge :status="shop?.subscriptionStatus ?? 'inactive'" size="lg" />
      </div>
    </div>

    <!-- Plan Selection -->
    <div class="section-label mt-4 mb-3">Choose a Plan</div>

    <div v-if="isLoadingPlans" class="text-center py-4 text-muted small">
      <div class="spinner-border spinner-border-sm me-2" role="status"></div>
      Loading plans...
    </div>

    <div v-else-if="plansError" class="alert alert-danger py-2 small rounded-3">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ plansError }}
      <button
        class="btn btn-sm btn-link p-0 ms-2 text-danger"
        @click="store.fetchPlans()"
      >
        Retry
      </button>
    </div>

    <div
      v-else-if="plans.length === 0"
      class="alert alert-secondary py-2 small rounded-3"
    >
      <i class="bi bi-info-circle me-2"></i>No subscription plans available.
    </div>

    <!-- ══════════════════════════════════════════════════════
         PLAN LIST ROWS
    ══════════════════════════════════════════════════════ -->
    <div v-else class="plans-list">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="plan-row"
        :class="{
          selected: selectedPlanId === plan.id,
          'is-best': plan.isBestValue,
        }"
        @click="selectedPlanId = plan.id"
      >
        <!-- Best Value top-left badge -->
        <div v-if="plan.isBestValue" class="best-badge">⭐ Best Value</div>

        <div class="plan-row-inner">
          <!-- Radio -->
          <div class="plan-radio">
            <div class="radio-dot" :class="{ active: selectedPlanId === plan.id }"></div>
          </div>

          <!-- Name + Subheading -->
          <div class="plan-row-info">
            <div class="plan-row-name">{{ plan.heading || plan.name }}</div>
            <div v-if="plan.subheading" class="plan-row-subheading">
              {{ plan.subheading }}
            </div>
          </div>

          <!-- Price (right) -->
          <div class="plan-row-right">
            <div class="plan-row-price">
              <span class="price-currency">₹</span>
              <span class="price-amount">{{ plan.price.toLocaleString("en-IN") }}</span>
            </div>
            <div class="plan-row-duration">
              {{
                plan.months === 12
                  ? "/ year"
                  : `/ ${plan.months} ${plan.months === 1 ? "month" : "months"}`
              }}
              <span class="plan-gst">+GST</span>
            </div>
          </div>
        </div>

        <!-- Description — shown below row only when selected -->
        <div v-if="plan.description && selectedPlanId === plan.id" class="plan-row-desc">
          {{ plan.description }}
        </div>
      </div>
    </div>

    <!-- Payment Mode -->
    <div class="section-label mt-4 mb-3">Payment Mode</div>
    <div class="payment-options">
      <label class="payment-option" :class="{ selected: paymentMode === 'cash' }">
        <input type="radio" v-model="paymentMode" value="cash" class="d-none" />
        <i class="bi bi-cash-coin me-2"></i>Cash
      </label>
      <label class="payment-option" :class="{ selected: paymentMode === 'online' }">
        <input type="radio" v-model="paymentMode" value="online" class="d-none" />
        <i class="bi bi-credit-card me-2"></i>Online
      </label>
    </div>

    <!-- Notes -->
    <div class="mt-4">
      <label class="form-label text-muted small fw-semibold">Notes (Optional)</label>
      <textarea
        v-model="notes"
        class="form-control"
        rows="2"
        placeholder="Add any notes about this subscription..."
        style="resize: none; border-radius: 10px"
      ></textarea>
    </div>

    <!-- ══════════════════════════════════════════════════════
         SUBSCRIPTION SUMMARY
    ══════════════════════════════════════════════════════ -->
    <div class="summary-box mt-4" v-if="selectedPlanId && paymentMode">
      <div class="summary-title">Subscription Summary</div>

      <div class="summary-row">
        <span>Shop</span>
        <strong>{{ shop?.shopName ?? "—" }}</strong>
      </div>
      <div class="summary-row">
        <span>Entity ID</span>
        <strong class="font-monospace text-primary small">
          {{ shop?.entityId ?? "—" }}
        </strong>
      </div>
      <div class="summary-row">
        <span>Mobile</span>
        <strong>{{ shop?.mobile ?? "—" }}</strong>
      </div>
      <div class="summary-row">
        <span>Email</span>
        <strong>{{ shop?.email ?? "—" }}</strong>
      </div>
      <div class="summary-row">
        <span>Plan</span>
        <strong>{{ selectedPlanData?.name ?? "—" }}</strong>
      </div>
      <div class="summary-row">
        <span>Duration</span>
        <strong>
          {{
            selectedPlanData?.months === 12
              ? "1 Year (12 Months)"
              : `${selectedPlanData?.months ?? "—"} Month(s)`
          }}
        </strong>
      </div>
      <div class="summary-row">
        <span>Payment Mode</span>
        <strong class="text-capitalize">{{ paymentMode }}</strong>
      </div>
      <div class="summary-row">
        <span>Start Date</span>
        <strong>{{ formatDate(new Date().toISOString()) }}</strong>
      </div>

      <hr class="my-2" style="border-color: #dee2e6" />

      <div class="summary-row total">
        <span>Total Amount</span>
        <strong class="text-primary">
          ₹{{ selectedPlanData?.price?.toLocaleString("en-IN") ?? "0" }}
          <span class="text-muted small fw-normal">+GST</span>
        </strong>
      </div>
    </div>

    <!-- Actions -->
    <div class="action-row mt-4">
      <button
        class="btn btn-outline-secondary"
        @click="resetForm"
        :disabled="isSubmitting"
      >
        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
      </button>
      <button
        class="btn btn-primary btn-subscribe"
        @click="confirmSubscription"
        :disabled="!selectedPlanId || !paymentMode || isSubmitting || isLoadingPlans"
      >
        <span
          v-if="isSubmitting"
          class="spinner-border spinner-border-sm me-2"
          role="status"
        ></span>
        <i v-else class="bi bi-patch-check-fill me-2"></i>
        {{ isSubmitting ? "Activating..." : "Activate Subscription" }}
      </button>
    </div>

    <!-- Success Toast -->
    <transition name="toast-slide">
      <div v-if="showSuccess" class="success-toast">
        <i class="bi bi-check-circle-fill me-2"></i>
        Subscription activated for
        <strong>{{ shop?.shopName ?? "—" }}</strong
        >!
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import SubscriptionBadge from "@/modules/AuthorizedAgents/components/subscription/SubscriptionBadge.vue";
import { useAssignSubscriptionStore } from "@/modules/AuthorizedAgents/stores/useAssignSubscriptionStore";

const props = defineProps({
  shop: { type: Object, required: true },
});
const emit = defineEmits(["subscriptionUpdated"]);
const store = useAssignSubscriptionStore();
const router = useRouter();

const selectedPlanId = ref(null);
const paymentMode = ref("cash");
const notes = ref("");

const plans = computed(() => store.plans);
const isLoadingPlans = computed(() => store.isLoadingPlans);
const plansError = computed(() => store.plansError);
const isSubmitting = computed(() => store.isSubmitting);
const submitError = computed(() => store.submitError);
const showSuccess = computed(() => store.submitSuccess);

const selectedPlanData = computed(() =>
  plans.value.find((p) => p.id === selectedPlanId.value)
);

const formatDate = (isoDate) => {
  if (!isoDate) return "—";
  return new Date(isoDate).toLocaleDateString("en-IN", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const resetForm = () => {
  selectedPlanId.value = null;
  paymentMode.value = "cash";
  notes.value = "";
};

const confirmSubscription = async () => {
  if (!selectedPlanId.value || !paymentMode.value) return;

  try {
    await store.assignSubscription({
      shop_module: props.shop._connection,
      shop_id: props.shop.shopId,
      subscription_id: selectedPlanId.value,
      payment_mode: paymentMode.value,
      notes: notes.value || null,
    });

    emit("subscriptionUpdated", {
      subscriptionStatus: "active",
      subscriptionPlan: selectedPlanId.value,
    });

    setTimeout(() => {
      store.submitSuccess = false;
    }, 4000);
    resetForm();
  } catch (err) {
    if (err?.response?.status === 401) {
      router.push({ name: "AgentLogin" });
    }
  }
};
</script>

<style scoped>
.subscription-manager {
  background: #fff;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
  border: 1px solid #f0f0f0;
  position: relative;
}
.card-label {
  font-weight: 600;
  font-size: 0.95rem;
  color: #495057;
}

/* ── Status Banner ── */
.status-banner {
  border-radius: 12px;
  padding: 16px 20px;
}
.status-active {
  background: #d1fae5;
  border: 1px solid #6ee7b7;
}
.status-inactive {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
}
.status-expired {
  background: #fee2e2;
  border: 1px solid #fca5a5;
}
.status-label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6b7280;
  font-weight: 600;
}
.status-value {
  font-weight: 700;
  font-size: 1rem;
  color: #111827;
}
.status-expiry {
  font-size: 0.78rem;
  color: #9ca3af;
  margin-top: 2px;
}

.section-label {
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #6c757d;
}

/* ══════════════════════════════════════════════════════
   PLAN LIST ROWS — single row layout
══════════════════════════════════════════════════════ */
.plans-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.plan-row {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 14px 18px;
  cursor: pointer;
  position: relative;
  background: #fafafa;
  transition: all 0.2s ease;
}
.plan-row:hover {
  border-color: #86b7fe;
  background: #f8f9ff;
}
.plan-row.selected {
  border-color: #0d6efd;
  background: #eef2ff;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.08);
}
.plan-row.is-best {
  border-color: #f59e0b;
  margin-top: 14px;
}
.plan-row.is-best.selected {
  background: #fffbeb;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

/* Best Value badge */
.best-badge {
  position: absolute;
  top: -13px;
  left: 0;
  background-color: #f59e0b;
  color: #fff;
  padding: 0.28rem 1rem;
  border-radius: 8px;
  border-bottom-left-radius: 0 !important;
  font-size: 0.75rem;
  font-weight: 700;
  pointer-events: none;
  z-index: 1;
}

/* Single row: radio | info | price */
.plan-row-inner {
  display: flex;
  align-items: center;
  gap: 14px;
}

/* Radio */
.plan-radio {
  flex-shrink: 0;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s;
}
.plan-row.selected .plan-radio {
  border-color: #0d6efd;
}
.plan-row.is-best.selected .plan-radio {
  border-color: #f59e0b;
}

.radio-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: transparent;
  transition: background 0.2s;
}
.radio-dot.active {
  background: #0d6efd;
}
.plan-row.is-best .radio-dot.active {
  background: #f59e0b;
}

/* Info — grows to fill space */
.plan-row-info {
  flex: 1;
  min-width: 0;
}

.plan-row-name {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1a1a1a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.plan-row-subheading {
  font-size: 0.78rem;
  color: #6c757d;
  margin-top: 1px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Price — right aligned, never wraps */
.plan-row-right {
  flex-shrink: 0;
  text-align: right;
}
.plan-row-price {
  display: flex;
  align-items: baseline;
  justify-content: flex-end;
  gap: 1px;
}
.price-currency {
  font-size: 0.75rem;
  color: #6c757d;
  align-self: flex-start;
  margin-top: 2px;
}
.price-amount {
  font-size: 1.15rem;
  font-weight: 800;
  color: #1a1a1a;
  line-height: 1;
}
.plan-row-duration {
  font-size: 0.72rem;
  color: #adb5bd;
  margin-top: 1px;
}
.plan-gst {
  font-size: 0.68rem;
  color: #adb5bd;
  margin-left: 2px;
}

/* Description — revealed below row when selected */
.plan-row-desc {
  font-size: 0.78rem;
  color: #9ca3af;
  line-height: 1.6;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #e9ecef;
}

/* ── Payment Mode ── */
.payment-options {
  display: flex;
  gap: 10px;
}
.payment-option {
  flex: 1;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  padding: 12px 16px;
  cursor: pointer;
  font-size: 0.88rem;
  font-weight: 600;
  text-align: center;
  transition: all 0.2s;
  color: #495057;
  background: #fafafa;
  display: flex;
  align-items: center;
  justify-content: center;
}
.payment-option:hover {
  border-color: #86b7fe;
  background: #f8f9ff;
}
.payment-option.selected {
  border-color: #0d6efd;
  background: #eef2ff;
  color: #0d6efd;
}

/* ── Summary ── */
.summary-box {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 18px;
  border: 1px solid #e9ecef;
}
.summary-title {
  font-weight: 700;
  font-size: 0.85rem;
  color: #495057;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  color: #6c757d;
  margin-bottom: 6px;
}
.summary-row.total {
  font-size: 0.95rem;
  color: #212529;
}

/* ── Actions ── */
.action-row {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}
.btn-subscribe {
  padding: 10px 24px;
  font-weight: 600;
  border-radius: 10px;
}

/* ── Success Toast ── */
.success-toast {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #065f46;
  color: #fff;
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 0.88rem;
  font-weight: 500;
  white-space: nowrap;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  z-index: 100;
}
.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.3s ease;
}
.toast-slide-enter-from,
.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(20px);
}

/* ── Responsive ── */
@media (max-width: 576px) {
  .plan-row-name,
  .plan-row-subheading {
    white-space: normal;
  }
  .payment-options {
    flex-direction: column;
  }
  .action-row {
    flex-direction: column;
  }
  .btn-subscribe {
    width: 100%;
  }
}
</style>
