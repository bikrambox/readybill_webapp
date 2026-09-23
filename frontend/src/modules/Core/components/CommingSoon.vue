<template>
  <div
    class="d-flex flex-column justify-content-center align-items-center bg-light py-5 px-3 min-h-screen"
  >
    <h1 class="display-3 fw-bold text-primary mb-5 text-center">
      {{ $t("common.Coming Soon") }}
    </h1>
    <a href="/" class="btn btn-outline-primary btn-lg px-5">
      <i class="bi bi-house-door me-2"></i>
      {{ $t("common.Back to Home") }}
    </a>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
const { t } = useI18n();
</script>
