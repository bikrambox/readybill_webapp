<template>
  <div class="row mb-4">
    <div class="col-12 text-center">
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <button
          type="button"
          class="btn btn-success btn-report"
          :disabled="disabled"
          @click="$emit('request-report', 'pdf')"
        >
          <i class="bi bi-file-pdf me-1"></i>
          {{ $t("common.Request Report") }} ({{ $t("common.PDF") }})
        </button>

        <button
          type="button"
          class="btn btn-info btn-report text-white"
          :disabled="disabled"
          @click="$emit('request-report', 'excel')"
        >
          <i class="bi bi-file-excel me-1"></i>
          {{ $t("common.Request Report") }} ({{ $t("common.Excel") }})
        </button>

        <button
          type="button"
          class="btn btn-secondary btn-report"
          :disabled="disabled"
          @click="$emit('request-report', 'csv')"
        >
          <i class="bi bi-file-text me-1"></i>
          {{ $t("common.Request Report") }} ({{ $t("common.CSV") }})
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["request-report"]);
</script>

<style scoped>
.btn-report {
  min-width: 200px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  padding: 0 1rem;
}

@media (max-width: 576px) {
  .btn-report {
    width: 100%;
    min-width: auto;
  }
}
</style>
