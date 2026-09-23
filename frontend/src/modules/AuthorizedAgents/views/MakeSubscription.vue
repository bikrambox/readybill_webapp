<template>
  <div class="subscription-page">
    <!-- Page Header -->
    <div class="page-header mb-4">
      <div class="d-flex align-items-center gap-3">
        <div class="page-icon">
          <i class="bi bi-patch-check-fill"></i>
        </div>
        <div>
          <h4 class="page-title mb-0">Assign Subscription</h4>
          <p class="page-subtitle mb-0">Search a shop and activate a subscription plan</p>
        </div>
      </div>
    </div>

    <!-- Step 1: Search -->
    <ShopSearchBox @shopSelected="onShopSelected" @cleared="onCleared" />

    <!-- Plans error -->
    <div
      v-if="store.plansError && !store.isLoadingPlans"
      class="alert alert-warning mt-3 small rounded-3"
    >
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      Could not load subscription plans.
      <button
        class="btn btn-sm btn-link p-0 ms-1 text-warning fw-bold"
        @click="store.fetchPlans()"
      >
        Retry
      </button>
    </div>

    <!-- Empty state -->
    <transition name="fade-slide">
      <div v-if="!store.hasSelected" class="empty-state mt-5">
        <div class="empty-icon">
          <i class="bi bi-shop"></i>
        </div>
        <h6 class="empty-title">No Shop Selected</h6>
        <p class="empty-desc">Search for a shop by name or Entity ID to get started.</p>
      </div>
    </transition>

    <!-- Step 2 + 3: Shop selected -->
    <transition name="fade-slide">
      <div v-if="store.selectedShop && store.selectedShop.shopId" class="mt-3">
        <!-- Step indicator -->
        <div class="step-indicator mb-4">
          <div class="step done">
            <div class="step-circle"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Shop Found</span>
          </div>
          <div class="step-line"></div>
          <div
            class="step"
            :class="{ done: subscriptionStep >= 2, active: subscriptionStep === 2 }"
          >
            <div class="step-circle">2</div>
            <span class="step-label">Shop Details</span>
          </div>
          <div class="step-line"></div>
          <div
            class="step"
            :class="{ done: subscriptionStep >= 3, active: subscriptionStep === 3 }"
          >
            <div class="step-circle">3</div>
            <span class="step-label">Assign Plan</span>
          </div>
        </div>

        <!-- Step 2: Shop Details -->
        <transition name="fade-slide">
          <div v-if="subscriptionStep === 2">
            <ShopDetailsCard :shop="store.selectedShop" />
            <div class="d-flex justify-content-between mt-3">
              <button
                class="btn btn-outline-secondary btn-sm rounded-3"
                @click="onCleared"
              >
                <i class="bi bi-arrow-left me-1"></i> Change Shop
              </button>
              <button
                class="btn btn-primary btn-sm rounded-3 px-4"
                @click="subscriptionStep = 3"
              >
                Continue to Plan
                <i class="bi bi-arrow-right ms-1"></i>
              </button>
            </div>
          </div>
        </transition>

        <!-- Step 3: Subscription Manager -->
        <transition name="fade-slide">
          <div v-if="subscriptionStep === 3">
            <div class="d-flex align-items-center gap-2 mb-3">
              <button
                class="btn btn-sm btn-outline-secondary rounded-3"
                @click="subscriptionStep = 2"
              >
                <i class="bi bi-arrow-left me-1"></i> Back
              </button>
              <span class="text-muted small">
                Assigning plan for
                <strong class="text-dark">{{ store.selectedShop.shopName }}</strong>
              </span>
            </div>
            <SubscriptionManager
              :shop="store.selectedShop"
              @subscriptionUpdated="onSubscriptionUpdated"
            />
          </div>
        </transition>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import ShopSearchBox from "@/modules/AuthorizedAgents/components/subscription/ShopSearchBox.vue";
import ShopDetailsCard from "@/modules/AuthorizedAgents/components/subscription/ShopDetailsCard.vue";
import SubscriptionManager from "@/modules/AuthorizedAgents/components/subscription/SubscriptionManager.vue";
import { useAssignSubscriptionStore } from "@/modules/AuthorizedAgents/stores/useAssignSubscriptionStore";

const store = useAssignSubscriptionStore();
const subscriptionStep = ref(1); // 1=search, 2=details, 3=plan

onMounted(() => store.fetchPlans());
onUnmounted(() => store.reset());

const onShopSelected = (shop) => {
  store.selectShop(shop);
  subscriptionStep.value = 2;
};

const onCleared = () => {
  store.clearSelectedShop();
  subscriptionStep.value = 1;
};

const onSubscriptionUpdated = (update) => {
  if (store.selectedShop) {
    store.selectedShop = { ...store.selectedShop, ...update };
  }
};
</script>

<style scoped>
.subscription-page {
  padding: 8px 4px;
}

/* Header */
.page-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0d6efd1a, #0d6efd33);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  color: #0d6efd;
  flex-shrink: 0;
}
.page-title {
  font-weight: 700;
  font-size: 1.1rem;
  color: #1a1a2e;
}
.page-subtitle {
  font-size: 0.82rem;
  color: #9ca3af;
  margin-top: 2px;
}

/* Step Indicator */
.step-indicator {
  display: flex;
  align-items: center;
  gap: 0;
}
.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  flex-shrink: 0;
}
.step-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid #dee2e6;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.78rem;
  font-weight: 700;
  color: #adb5bd;
  transition: all 0.3s ease;
}
.step.active .step-circle {
  border-color: #0d6efd;
  color: #0d6efd;
  background: #eef2ff;
}
.step.done .step-circle {
  border-color: #0d6efd;
  background: #0d6efd;
  color: #fff;
}
.step-label {
  font-size: 0.72rem;
  font-weight: 600;
  color: #adb5bd;
  white-space: nowrap;
}
.step.active .step-label,
.step.done .step-label {
  color: #0d6efd;
}
.step-line {
  flex: 1;
  height: 2px;
  background: #dee2e6;
  margin: 0 8px;
  margin-bottom: 18px;
  transition: background 0.3s ease;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 60px 20px;
}
.empty-icon {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: #d1d5db;
  margin-bottom: 16px;
}
.empty-title {
  font-weight: 700;
  font-size: 1rem;
  color: #374151;
  margin-bottom: 6px;
}
.empty-desc {
  font-size: 0.85rem;
  color: #9ca3af;
  max-width: 280px;
  margin: 0 auto;
}

/* Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
