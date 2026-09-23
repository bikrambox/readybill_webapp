<script setup>
import { defineProps, defineEmits } from "vue";
import InfoTooltip from "@/modules/core/components/InfoTooltip.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
  searchQuery: String,
  selectedCount: Number,
  hasSelection: Boolean,
});
const emit = defineEmits([
  "update:search-query",
  "deselect",
  "delete-selected",
  "reset-dataset",
  "add-row",
  "download-dataset",
]);
</script>

<template>
  <div>
    <!-- Main toolbar row -->
    <div class="bg-white rounded shadow-sm px-3 py-3 mb-2">
      <div class="row g-2 align-items-center">
        <!-- Search -->
        <div class="col-12 col-md-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0">
              <i class="bi bi-search text-muted"></i>
            </span>
            <input
              type="text"
              class="form-control border-start-0 ps-0"
              placeholder="Search..."
              :value="searchQuery"
              @input="emit('update:search-query', $event.target.value)"
            />
          </div>
        </div>

        <!-- Buttons -->
        <div class="col-12 col-md-9 mt-2 mt-md-0">
          <div class="toolbar-actions">
            <!-- Selected count badge (desktop only) -->
            <span
              class="toolbar-item badge-count d-none d-md-inline-flex"
              :class="hasSelection ? 'badge-count--selected' : ''"
            >
              <i class="bi bi-check2-square me-1"></i>
              {{ selectedCount ?? 0 }} {{ $t("common.selected") }}
            </span>

            <InfoTooltip
              :title="$t('common.add_row_title')"
              :message="$t('common.add_row_message')"
              placement="top"
              trigger="hover"
            >
              <button
                class="toolbar-item toolbar-btn btn btn-sm btn-primary"
                @click="emit('add-row')"
              >
                <i class="bi bi-plus-lg me-1"></i>{{ $t("common.Add Row") }}
              </button>
            </InfoTooltip>

            <InfoTooltip
              :title="$t('common.reset_dataset_title')"
              :message="$t('common.reset_dataset_message')"
              placement="top"
              trigger="hover"
            >
              <button
                class="toolbar-item toolbar-btn btn btn-sm btn-primary"
                @click="emit('reset-dataset')"
              >
                <i class="bi bi-arrow-clockwise me-1"></i>{{ $t("common.Reset Dataset") }}
              </button>
            </InfoTooltip>

            <InfoTooltip
              :title="$t('common.delete_selected_title')"
              :message="$t('common.delete_selected_message')"
              placement="top"
              trigger="hover"
            >
              <button
                class="toolbar-item toolbar-btn btn btn-sm btn-danger"
                @click="emit('delete-selected')"
                :disabled="!hasSelection"
              >
                <i class="bi bi-trash me-1"></i>{{ $t("common.Delete Selected") }}
              </button>
            </InfoTooltip>

            <!-- Divider (desktop only) -->
            <div class="vr mx-1 d-none d-md-block"></div>

            <InfoTooltip
              :title="$t('common.dataset_download_title')"
              :message="$t('common.dataset_download_message')"
              placement="top"
              trigger="hover"
            >
              <button
                class="toolbar-item toolbar-btn btn btn-sm btn-success"
                @click="emit('download-dataset')"
              >
                <i class="bi bi-download me-1"></i>{{ $t("common.Download Dataset") }}
              </button>
            </InfoTooltip>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile: selected count + deselect row -->
    <div v-if="hasSelection" class="d-block d-md-none mt-n2 mb-2">
      <div
        class="bg-white rounded shadow-sm px-3 py-2 d-flex align-items-center justify-content-between gap-2"
      >
        <span class="badge-count badge-count--selected">
          <i class="bi bi-check2-square me-1"></i>
          {{ selectedCount }} {{ $t("common.selected") }}
        </span>
        <button
          class="btn btn-sm btn-outline-secondary toolbar-btn-mobile"
          @click="emit('deselect')"
        >
          {{ $t("common.Deselect") }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ── Search input ─────────────────────────────────────── */
.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.input-group-text {
  border-right: 0;
}
.form-control.border-start-0 {
  border-left: 0;
}
.form-control.border-start-0:focus {
  border-left: 0;
  box-shadow: none;
}
.input-group:focus-within .input-group-text {
  border-color: #86b7fe;
}

/* ── Toolbar layout ───────────────────────────────────── */
.toolbar-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-end;
  gap: 0.5rem;
}

/* ── Shared size + shape: buttons & badge ─────────────── */
.toolbar-item {
  height: 31px;
  min-width: 130px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
  padding-inline: 0.75rem;
  box-sizing: border-box;
  border-radius: 0.375rem !important; /* subtle rounded edges */
}

/* ── Button visual styles ─────────────────────────────── */
.toolbar-btn {
  /* sizing + radius handled by .toolbar-item */
}

/* ── Mobile deselect button ───────────────────────────── */
.toolbar-btn-mobile {
  border-radius: 0.375rem !important;
}

/* ── Badge visual styles ──────────────────────────────── */
.badge-count {
  font-size: 0.78rem;
  font-weight: 500;
  color: #6c757d;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  transition: all 0.2s ease;
}
.badge-count--selected {
  color: #0d6efd;
  background-color: #cfe2ff;
  border-color: #9ec5fe;
}

/* ── Mobile: 2-per-row grid ───────────────────────────── */
@media (max-width: 767.98px) {
  .toolbar-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    justify-items: stretch;
  }

  /* InfoTooltip wrapper must stretch to fill grid cell */
  .toolbar-actions :deep(> *) {
    width: 100%;
  }

  .toolbar-item {
    width: 100%;
    min-width: unset;
  }
}
</style>
