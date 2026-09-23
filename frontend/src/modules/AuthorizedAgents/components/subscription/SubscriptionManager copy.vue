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

    <div v-else class="plans-grid">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="plan-card"
        :class="{ selected: selectedPlanId === plan.id, popular: plan.popular }"
        @click="selectedPlanId = plan.id"
      >
        <div v-if="plan.popular" class="popular-badge">Most Popular</div>
        <div class="plan-icon"><i :class="plan.icon"></i></div>
        <div class="plan-name">{{ plan.name }}</div>
        <div class="plan-price">
          <span class="currency">₹</span>
          <span class="amount">{{ plan.price.toLocaleString("en-IN") }}</span>
          <span class="period">/{{ plan.period }}</span>
        </div>
        <ul class="plan-features">
          <li v-for="feat in plan.features" :key="feat">
            <i class="bi bi-check-circle-fill text-success me-1"></i>{{ feat }}
          </li>
        </ul>
        <div class="plan-check" v-if="selectedPlanId === plan.id">
          <i class="bi bi-check-circle-fill"></i>
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

    <!-- Summary -->
    <div class="summary-box mt-4" v-if="selectedPlanId && paymentMode">
      <div class="summary-title">Subscription Summary</div>
      <div class="summary-row">
        <span>Shop</span>
        <strong>{{ shop?.shopName ?? "—" }}</strong>
      </div>
      <div class="summary-row">
        <span>Entity ID</span>
        <strong class="font-monospace text-primary small">{{
          shop?.entityId ?? "—"
        }}</strong>
      </div>
      <div class="summary-row">
        <span>Plan</span>
        <strong>{{ selectedPlanData?.name }}</strong>
      </div>
      <div class="summary-row">
        <span>Duration</span>
        <strong>{{ selectedPlanData?.months ?? "—" }} Month(s)</strong>
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
        <strong class="text-primary"
          >₹{{ selectedPlanData?.price?.toLocaleString("en-IN") ?? "0" }}</strong
        >
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
        Subscription activated for <strong>{{ shop?.shopName ?? "—" }}</strong
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
const paymentMode = ref("cash"); // default to cash
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
      shop_module: props.shop._connection, // ← DB connection name
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
    // ✅ Redirect to login on 401
    if (err?.response?.status === 401) {
      router.push({ name: "AgentLogin" }); // adjust route name as needed
    }
    // submitError shown in template for other errors
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

/* Status Banner */
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

/* Plans */
.plans-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}
.plan-card {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 16px 12px;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
  text-align: center;
  background: #fafafa;
}
.plan-card:hover {
  border-color: #86b7fe;
  background: #f8f9ff;
}
.plan-card.selected {
  border-color: #0d6efd;
  background: #eef2ff;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}
.plan-card.popular {
  border-color: #f59e0b;
}
.popular-badge {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  background: #f59e0b;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 2px 10px;
  border-radius: 20px;
  white-space: nowrap;
}
.plan-icon {
  font-size: 1.3rem;
  color: #0d6efd;
  margin-bottom: 6px;
}
.plan-name {
  font-weight: 700;
  font-size: 0.88rem;
  color: #212529;
  margin-bottom: 4px;
}
.plan-price {
  margin-bottom: 10px;
}
.currency {
  font-size: 0.8rem;
  color: #6c757d;
  vertical-align: top;
  margin-top: 4px;
  display: inline-block;
}
.amount {
  font-size: 1.4rem;
  font-weight: 800;
  color: #212529;
}
.period {
  font-size: 0.75rem;
  color: #adb5bd;
}
.plan-features {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
}
.plan-features li {
  font-size: 0.75rem;
  color: #495057;
  margin-bottom: 3px;
}
.plan-check {
  position: absolute;
  top: 8px;
  right: 10px;
  color: #0d6efd;
  font-size: 1rem;
}

/* Payment Mode */
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

/* Summary */
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

/* Actions */
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

/* Toast */
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

@media (max-width: 576px) {
  .plans-grid {
    grid-template-columns: 1fr;
  }
  .payment-options {
    flex-direction: column;
  }
}
</style>
