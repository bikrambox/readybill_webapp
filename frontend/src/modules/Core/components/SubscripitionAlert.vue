<template>
  <div class="d-flex align-items-center justify-content-center p-3 p-md-4">
    <div class="card shadow-sm border-danger">
      <div class="card-body p-4 p-md-5">
        <!-- Icon -->
        <div
          class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4"
          style="width: 64px; height: 64px"
        >
          <i class="bi bi-exclamation-triangle-fill fs-2 text-danger"></i>
        </div>

        <!-- Title -->
        <h3 class="card-title fw-semibold mb-3 fs-4 text-danger">
          {{ $t("common.Premium Feature") }}
        </h3>

        <!-- Description -->
        <p class="card-text text-muted mb-4 lh-base">
          {{
            $t("common.Your free plan does not allow access to add employees")
          }}.
          {{ $t("common.Please upgrade to premium for extended data access") }}.
        </p>

        <!-- Actions -->
        <div
          class="d-flex flex-column flex-md-row gap-2 gap-md-3 justify-content-center"
        >
          <router-link
            to="subscription"
            class="btn btn-danger d-flex align-items-center justify-content-center gap-2"
          >
            <i class="bi bi-star-fill"></i>
            {{ $t("common.Upgrade to Premium") }}
          </router-link>

          <router-link
            to="sell"
            class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2"
          >
            <i class="bi bi-arrow-left"></i>
            {{ $t("common.Back") }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
const { t } = useI18n();
</script>