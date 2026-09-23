<template>
  <div class="shop-details-card">
    <div class="card-header-row">
      <div class="shop-identity">
        <div class="shop-avatar-lg">{{ shopInitial }}</div>
        <div>
          <h5 class="shop-name mb-0">{{ shopDisplayName }}</h5>
          <div class="business-name text-muted small">
            {{ shop?.businessName ?? "—" }}
          </div>
          <span class="entity-id-badge mt-1 d-inline-block">{{
            shop?.entityId ?? "—"
          }}</span>
        </div>
      </div>
      <SubscriptionBadge :status="shop?.subscriptionStatus ?? 'inactive'" />
    </div>

    <hr class="divider" />

    <div class="details-grid">
      <div class="detail-item" v-for="item in detailItems" :key="item.label">
        <div class="detail-icon">
          <i :class="item.icon"></i>
        </div>
        <div>
          <div class="detail-label">{{ item.label }}</div>
          <div class="detail-value">{{ item.value || "—" }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import SubscriptionBadge from "@/modules/AuthorizedAgents/components/subscription/SubscriptionBadge.vue";

const props = defineProps({
  shop: { type: Object, required: true },
});

const shopInitial = computed(() => {
  const name =
    props.shop?.shopName || props.shop?.businessName || props.shop?.name || "?";
  return String(name).charAt(0).toUpperCase();
});

const shopDisplayName = computed(
  () =>
    props.shop?.shopName || props.shop?.name || props.shop?.businessName || "(No Name)"
);

const detailItems = computed(() => [
  { icon: "bi bi-building", label: "Business Name", value: props.shop?.businessName },
  { icon: "bi bi-envelope-fill", label: "Email", value: props.shop?.email },
  { icon: "bi bi-envelope-fill", label: "Mobile", value: props.shop?.mobile },
  { icon: "bi bi-geo-alt-fill", label: "Address", value: props.shop?.address },
  { icon: "bi bi-receipt", label: "GSTIN", value: props.shop?.gstin },
  {
    icon: "bi bi-calendar-check-fill",
    label: "Registered On",
    value: props.shop?.registeredOn
      ? new Date(props.shop.registeredOn).toLocaleDateString("en-IN", {
          day: "2-digit",
          month: "short",
          year: "numeric",
        })
      : null,
  },
  {
    icon: "bi bi-clock-history",
    label: "Subscription",
    value: props.shop?.subscriptionExpiry
      ? `${props.shop.subscriptionStatus === "active" ? "Expires" : "Expired"} ${new Date(
          props.shop.subscriptionExpiry
        ).toLocaleDateString("en-IN", {
          day: "2-digit",
          month: "short",
          year: "numeric",
        })}`
      : "No active subscription",
  },
]);
</script>

<style scoped>
.shop-details-card {
  background: #fff;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
  border: 1px solid #f0f0f0;
}
.card-header-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}
.shop-identity {
  display: flex;
  align-items: center;
  gap: 14px;
}
.shop-avatar-lg {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0d6efd, #6610f2);
  color: #fff;
  font-weight: 800;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.shop-name {
  font-weight: 700;
  font-size: 1rem;
  color: #1a1a2e;
}
.business-name {
  font-size: 0.8rem;
  margin-top: 1px;
}
.entity-id-badge {
  font-family: monospace;
  font-size: 0.75rem;
  background: #eef2ff;
  color: #0d6efd;
  padding: 2px 10px;
  border-radius: 20px;
  font-weight: 600;
}
.divider {
  border-color: #f0f0f0;
  margin: 18px 0;
}
.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}
.detail-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #f8f9ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.82rem;
  color: #0d6efd;
  flex-shrink: 0;
  margin-top: 2px;
}
.detail-label {
  font-size: 0.7rem;
  color: #adb5bd;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}
.detail-value {
  font-size: 0.85rem;
  color: #212529;
  font-weight: 500;
  margin-top: 2px;
  word-break: break-word;
}
@media (max-width: 576px) {
  .details-grid {
    grid-template-columns: 1fr;
  }
}
</style>
