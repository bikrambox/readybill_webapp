<script setup>
import { ref, computed, onMounted } from "vue";
import { useSubscriptionStore } from "@/modules/GroceryGermany/stores/subscriptionStore";
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";
import PlanCard from "./PlanCard.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";

const subscriptionStore = useSubscriptionStore();
const userStore = useUserDetailsStore();
const subscription = computed(() => userStore.shop_subscription);

console.log("shop_subscription", subscription.value);

// Get current subscription ID from user store
const currentSubscriptionId = computed(() => subscription.value?.subscription_id);

// Get expiry date from user store
const subscriptionExpiryDate = computed(() => subscription.value?.end_date);

// Check if user is on free plan (subscription_id === 1)
const isFreePlan = computed(() => {
  return currentSubscriptionId.value === 1;
});

// Format expiry date
const formattedExpiryDate = computed(() => {
  if (!subscriptionExpiryDate.value || isFreePlan.value) return null;

  try {
    const date = new Date(subscriptionExpiryDate.value);
    return date.toLocaleDateString("en-IN", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  } catch (error) {
    return subscriptionExpiryDate.value;
  }
});

// Check if subscription is expired
const isSubscriptionExpired = computed(() => {
  if (!subscriptionExpiryDate.value || isFreePlan.value) return false;

  try {
    const expiryDate = new Date(subscriptionExpiryDate.value);
    const today = new Date();

    // Set both dates to midnight for accurate day comparison
    expiryDate.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);

    return expiryDate < today;
  } catch (error) {
    return false;
  }
});

// Get current plan name
const currentPlanName = computed(() => {
  if (isFreePlan.value) {
    return "Free Plan";
  }

  const currentPlan = subscriptionStore.activePlans.find(
    (plan) => plan.subscription_id === currentSubscriptionId.value
  );

  return currentPlan ? currentPlan.plan_name : "Unknown Plan";
});

const selectedPlanId = computed({
  get: () => subscriptionStore.selectedPlanId,
  set: (value) => subscriptionStore.selectPlan(value),
});

const plans = computed(() => {
  return subscriptionStore.activePlans.map((plan, index) => {
    // Check if this plan is the user's current subscription
    // Don't mark any plan as present if on free plan (subscription_id === 1)
    const isCurrentPlan =
      !isFreePlan.value && plan.subscription_id === currentSubscriptionId.value;

    return {
      id: plan.subscription_id,
      name: plan.plan_name,
      isPresent: isCurrentPlan, // Only mark as present if NOT on free plan
      isBestValue: index === 2, // Mark middle plan as best value
      pricing: `₹${plan.price} for ${plan.months} month${plan.months > 1 ? "s" : ""}`,
      price: plan.price,
      months: plan.months,
      bgColor: index === 0 ? "#e8f5e9" : index === 1 ? "#d4f4ea" : "#fff3e0",
      borderColor: index === 0 ? "#5EBE9B" : index === 1 ? "#5EBE9B" : "#ff9800",
    };
  });
});

const loading = computed(() => subscriptionStore.loading);
const error = computed(() => subscriptionStore.error);

const selectPlan = (planId) => {
  selectedPlanId.value = planId;
};

const handleSubscribe = () => {
  const selectedPlan = plans.value.find((p) => p.id === selectedPlanId.value);
  if (selectedPlan) {
    alert(`Subscribing to ${selectedPlan.name}...`);
    // Add your subscription logic here
  }
};

onMounted(async () => {
  try {
    await subscriptionStore.fetchSubscriptionPlans();
  } catch (err) {
    console.error("Error fetching subscription plans:", err);
  }
});
</script>

<template>
  <div class="subscriptions-container">
    <div class="container-fluid py-4">
      <!-- Breadcrumb and Title -->
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

      <!-- Current Plan Info -->
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

      <!-- Error Display -->
      <FormErrorBox v-if="error" :error="error" @close="subscriptionStore.clearError()" />

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading subscription plans...</p>
      </div>

      <!-- Plan Cards -->
      <div v-else-if="plans.length > 0" class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
          <div
            v-for="(plan, index) in plans"
            :key="plan.id"
            :class="{ 'mb-3': index < plans.length - 1 }"
          >
            <!-- <PlanCard
              :name="plan.name"
              :isPresent="plan.isPresent"
              :isBestValue="plan.isBestValue"
              :pricing="plan.pricing"
              :bgColor="plan.bgColor"
              :borderColor="plan.borderColor"
              :isSelected="selectedPlanId === plan.id"
              @select="selectPlan(plan.id)"
            /> -->

            <PlanCard
              name="Upgrade to premium for"
              price="$100"
              price-suffix="/year"
              subtitle="Unlock all features with the premium plan"
              description="Take your billing experience to the next level with Premium. Add employees to manage your store efficiently, save and access up to 3 years of transaction history anytime, and enjoy unlimited support via phone and email whenever you need assistance."
              pricing="$100/year"
              :bg-color="'#eef7f3'"
              :border-color="'#5EBE9B'"
              :is-selected="true"
            />
          </div>

          <!-- Subscribe Button -->
          <div class="row mt-3">
            <div class="col-12">
              <div class="d-flex justify-content-center justify-content-md-center">
                <button
                  class="btn btn-primary subscribe-btn"
                  @click="handleSubscribe"
                  :disabled="!selectedPlanId"
                >
                  Subscribe Now
                </button>
              </div>

              <!-- Terms and Privacy -->
              <div class="col-12">
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

      <!-- No Plans Available -->
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

/* Current Plan Alert */
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
  cursor: pointer;
}

.subscribe-btn:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.subscribe-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Desktop specific */
@media (min-width: 768px) {
  .subscribe-btn {
    width: auto;
  }
}

/* Mobile specific */
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
