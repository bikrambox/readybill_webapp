<script setup>
import { computed, onMounted } from "vue";
import { useSubscriptionStore } from "@/modules/GroceryIndia/stores/subscriptionStore";
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";
import PlanCard from "./PlanCard.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";

const subscriptionStore = useSubscriptionStore();
const userStore = useUserDetailsStore();

const currentPlanData = computed(() => userStore.currentPlanData || null);
const currentSubscription = computed(() => userStore.shop_subscription || {});

const currentSubscriptionId = computed(() =>
  Number(currentSubscription.value?.subscription_id || 0)
);

const subscriptionExpiryDate = computed(() => {
  return currentSubscription.value?.end_date || null;
});

const isFreePlan = computed(() => {
  return (
    Number(currentSubscription.value?.isFreeSubscriptionPlan || 0) === 1 ||
    currentSubscriptionId.value === 1
  );
});

const formattedExpiryDate = computed(() => {
  if (!subscriptionExpiryDate.value || isFreePlan.value) return null;

  try {
    const date = new Date(subscriptionExpiryDate.value);
    if (isNaN(date.getTime())) return subscriptionExpiryDate.value;

    return date.toLocaleDateString("en-IN", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  } catch {
    return subscriptionExpiryDate.value;
  }
});

const isSubscriptionExpired = computed(() => {
  if (!subscriptionExpiryDate.value || isFreePlan.value) return false;

  if (Number(currentSubscription.value?.isSubscriptionExpired || 0) === 1) {
    return true;
  }

  try {
    const expiryDate = new Date(subscriptionExpiryDate.value);
    if (isNaN(expiryDate.getTime())) return false;

    const today = new Date();
    expiryDate.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);

    return expiryDate < today;
  } catch {
    return false;
  }
});

const currentPlanName = computed(() => {
  if (isFreePlan.value) return "Free Plan";
  return currentPlanData.value?.plan_name || "Plan no longer available";
});

const selectedPlanId = computed({
  get: () => subscriptionStore.selectedPlanId,
  set: (value) => subscriptionStore.selectPlan(value),
});

const cardColors = [
  { bgColor: "#eef7f3", borderColor: "#5EBE9B" },
  { bgColor: "#d4f4ea", borderColor: "#5EBE9B" },
  { bgColor: "#fff3e0", borderColor: "#ff9800" },
  { bgColor: "#e8f0fe", borderColor: "#4285f4" },
  { bgColor: "#fce8e6", borderColor: "#ea4335" },
];

const plans = computed(() => {
  const activePlans = Array.isArray(subscriptionStore.activePlans)
    ? subscriptionStore.activePlans
    : [];

  return activePlans.map((plan, index) => {
    const isCurrentPlan =
      !isFreePlan.value &&
      Number(plan.subscription_id) === Number(currentSubscriptionId.value);

    const colors = cardColors[index % cardColors.length];

    return {
      id: Number(plan.subscription_id),
      name: plan.plan_name || "",
      months: plan.months || plan.duration || 0,
      price: plan.price || 0,
      heading: plan.heading || `Upgrade to ${plan.plan_name} for`,
      subheading:
        plan.subheading || `Unlock all features with the ${plan.plan_name} plan`,
      description: plan.description || null,
      isPresent: isCurrentPlan,
      isBestValue: Number(plan.is_best_value || plan.best_value) === 1,
      bgColor: colors.bgColor,
      borderColor: colors.borderColor,
    };
  });
});

const loading = computed(() => subscriptionStore.loading);
const error = computed(() => subscriptionStore.error);

const selectPlan = (planId) => {
  selectedPlanId.value = planId;
};

const handleSubscribe = () => {
  const selectedPlan = plans.value.find(
    (p) => Number(p.id) === Number(selectedPlanId.value)
  );

  if (!selectedPlan) {
    subscriptionStore.error = "Selected plan is no longer available.";
    return;
  }

  alert(`Subscribing to ${selectedPlan.name}...`);
};

onMounted(async () => {
  try {
    await Promise.all([
      subscriptionStore.fetchSubscriptionPlans(),
      userStore.fetchCurrentPlanData(),
    ]);
  } catch (err) {
    console.error("Error fetching subscription data:", err);
  }
});
</script>

<template>
  <div class="subscriptions-container">
    <div class="container-fluid py-4">
      <div class="mb-4">
        <h2 class="fw-bold mb-1">Choose Your Plan</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="#" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
          </ol>
        </nav>
      </div>

      <div v-if="currentSubscriptionId" class="current-plan-alert mb-3">
        <div class="mb-1">
          <span v-if="isFreePlan">
            You are currently on the <strong>{{ currentPlanName }}</strong
            >. Upgrade to Premium for full access to all features and benefits.
          </span>
          <span v-else>
            You are currently on the <strong>{{ currentPlanName }}</strong
            >.
          </span>
        </div>

        <div
          v-if="formattedExpiryDate"
          class="expiry-text"
          :class="{ 'text-danger': isSubscriptionExpired }"
        >
          {{ isSubscriptionExpired ? "Expired" : "Expires on" }}:
          <strong>{{ formattedExpiryDate }}</strong>
        </div>
      </div>

      <FormErrorBox v-if="error" :error="error" @close="subscriptionStore.clearError()" />

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading subscription plans...</p>
      </div>

      <div v-else-if="plans.length > 0" class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
          <div
            v-for="(plan, index) in plans"
            :key="plan.id"
            :class="{ 'mb-3': index < plans.length - 1 }"
          >
            <PlanCard
              :heading="plan.heading"
              :subheading="plan.subheading"
              :description="plan.description"
              :price="plan.price"
              :months="plan.months"
              :bg-color="plan.bgColor"
              :border-color="plan.borderColor"
              :is-present="plan.isPresent"
              :is-best-value="plan.isBestValue"
              :is-selected="selectedPlanId === plan.id"
              @select="selectPlan(plan.id)"
            />
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <div class="d-flex justify-content-center">
                <button
                  class="btn btn-primary subscribe-btn"
                  @click="handleSubscribe"
                  :disabled="!selectedPlanId || plans.length === 0"
                >
                  Subscribe Now
                </button>
              </div>

              <div class="col-12 mt-2">
                <p class="text-center text-muted small mb-0">
                  By subscribing, you accept the
                  <a href="#" class="text-decoration-none">Terms</a> and
                  <a href="#" class="text-decoration-none">Privacy</a>.
                </p>
                <p class="text-center text-muted small mb-0">
                  Auto-renews unless cancelled 24 hours before renewal.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4 text-center">
          <p class="text-muted mb-0">No subscription plans available at the moment.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.subscriptions-container {
  margin: 0 auto;
}

.current-plan-alert {
  background-color: #f8f9fa;
  border-left: 4px solid #0d6efd;
  padding: 1rem 1.25rem;
  border-radius: 4px;
  color: #212529;
  line-height: 1.6;
}

.current-plan-alert .expiry-text {
  color: #6c757d;
  font-size: 0.95rem;
  margin-top: 0.25rem;
}

.current-plan-alert .expiry-text.text-danger {
  color: #dc3545 !important;
  font-weight: 500;
}

.subscribe-btn {
  background-color: #0d6efd;
  border-color: #0d6efd;
  padding: 0.5rem 2rem;
  font-weight: 500;
}

.subscribe-btn:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.subscribe-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (min-width: 768px) {
  .subscribe-btn {
    width: auto;
  }
}

@media (max-width: 767.98px) {
  .subscriptions-container {
    max-width: 100%;
  }

  .subscribe-btn {
    width: 100%;
  }

  .current-plan-alert {
    padding: 0.875rem 1rem;
    font-size: 0.9rem;
  }

  .current-plan-alert .expiry-text {
    font-size: 0.875rem;
  }
}

@media (max-width: 991.98px) {
  .subscriptions-container {
    max-width: 100%;
  }
}
</style>
