<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  searchQuery: {
    type: String,
    default: ''
  },
  pageSize: {
    type: Number,
    default: 100
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:searchQuery', 'update:pageSize']);

const pageSizeOptions = [10, 25, 50, 100, 250, 500];

const updateSearch = (event) => {
  emit('update:searchQuery', event.target.value);
};

const updatePageSize = (event) => {
  emit('update:pageSize', parseInt(event.target.value));
};
</script>

<template>
  <div class="transaction-filters mb-3">
    <div class="row g-3 align-items-center">
      <!-- Rows per page -->
      <!-- <div class="col-auto">
        <label class="form-label mb-0 me-2 small fw-semibold">
          {{ t('common.Rows per page') }}:
        </label>
      </div>
      <div class="col-auto">
        <select
          :value="pageSize"
          @change="updatePageSize"
          :disabled="loading"
          class="form-select form-select-sm"
          style="width: auto; min-width: 80px;"
        >
          <option
            v-for="size in pageSizeOptions"
            :key="size"
            :value="size"
          >
            {{ size }}
          </option>
        </select>
      </div> -->

      <!-- Search -->
      <div class="col-md-4 col-12 ms-auto">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white">
            <i class="bi bi-search"></i>
          </span>
          <input
            type="text"
            class="form-control"
            :placeholder="t('common.Search transactions') + '...'"
            :value="searchQuery"
            @input="updateSearch"
            :disabled="loading"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.transaction-filters {
  background-color: #f8f9fa;
  padding: 1rem;
  border-radius: 0.5rem;
}

.form-select-sm,
.input-group-sm .form-control {
  font-size: 0.875rem;
}

.form-select:disabled,
.form-control:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

@media (max-width: 768px) {
  .transaction-filters .row {
    flex-direction: column;
  }
  
  .col-auto {
    width: 100%;
  }
  
  .ms-auto {
    margin-left: 0 !important;
  }
}
</style>
