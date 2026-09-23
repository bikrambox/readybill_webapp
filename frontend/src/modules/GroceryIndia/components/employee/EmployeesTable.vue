<template>
  <div class="card border-0 shadow-sm position-relative">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table
          ref="tableRef"
          :key="`table-refresh-${tableKey}`"
          class="table table-hover align-middle mb-0"
          style="width: 100%"
        >
          <thead class="table-light">
            <tr>
              <th class="ps-4">{{ $t("common.Employee Name") }}</th>
              <!-- <th>{{ $t("common.Email") }}</th> -->
              <th>{{ $t("common.Contact Number") }}</th>
              <th>{{ $t("common.Photo") }}</th>
              <th>{{ $t("common.Action") }}</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="!isLoading && data.length > 0">
              <tr
                v-for="item in data"
                :key="`row-${item.id}-${tableKey}`"
                @click="handleRowClick(item.staff_id)"
                style="cursor: pointer"
              >
                <td class="ps-4">
                  <span class="fw-semibold">{{ item.name || "-" }}</span>
                </td>
                <!-- <td>{{ item.email || "-" }}</td> -->
                <td>{{ item.contact || "-" }}</td>
                <td>
                  <img
                    v-if="item.photo"
                    :src="item.photo"
                    alt="Employee"
                    class="employee-photo"
                  />
                  <div v-else class="employee-photo-placeholder">
                    <i class="bi bi-person-circle"></i>
                  </div>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-outline-danger"
                    @click.stop="handleDelete(item.staff_id)"
                    :disabled="isLoading"
                    title="Delete"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            </template>
            <template v-else-if="!isLoading && data.length === 0">
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem"></i>
                  {{ $t("common.No employees found") }}
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                  <div
                    class="spinner-border spinner-border-sm text-primary"
                    role="status"
                  >
                    <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
                  </div>
                  <span class="ms-2">{{ $t("common.Loading data") }}...</span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Custom Footer with Pagination -->
    <div class="card-footer bg-white border-top">
      <div class="d-flex justify-content-between align-items-center">
        <!-- Left: Rows per page -->
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small fw-medium"
            >{{ $t("common.Rows per page") }}:</span
          >
          <select
            class="form-select form-select-sm"
            style="width: auto"
            :value="pageLength"
            @change="handlePageLengthChange"
            :disabled="isLoading"
          >
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>

        <!-- Right: Info and Arrows -->
        <div class="d-flex align-items-center gap-3">
          <span class="text-muted small fw-medium">{{ paginationInfo }}</span>
          <div class="btn-group" role="group">
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary"
              @click="handlePrevPage"
              :disabled="!canGoPrev || isLoading"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary"
              @click="handleNextPage"
              :disabled="!canGoNext || isLoading"
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref } from "vue";

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  currentPage: {
    type: Number,
    default: 0,
  },
  pageLength: {
    type: Number,
    default: 10,
  },
  totalRecords: {
    type: Number,
    default: 0,
  },
  filteredRecords: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits([
  "row-click",
  "delete-item",
  "page-change",
  "page-length-change",
  "table-ready",
]);

const tableKey = ref(0);

watch(
  () => props.data,
  (newData, oldData) => {
    console.log("🔄 Table data changed:", {
      oldCount: oldData?.length || 0,
      newCount: newData.length,
    });
    tableKey.value++;
  },
  { deep: true }
);

watch(
  () => props.filteredRecords,
  (newVal, oldVal) => {
    console.log("📊 RecordsFiltered changed:", { old: oldVal, new: newVal });
    if (newVal !== oldVal) {
      tableKey.value++;
    }
  }
);

const paginationInfo = computed(() => {
  if (props.filteredRecords === 0) {
    return "0-0 of 0";
  }
  const start = props.currentPage * props.pageLength + 1;
  const end = Math.min((props.currentPage + 1) * props.pageLength, props.filteredRecords);
  return `${start}-${end} of ${props.filteredRecords}`;
});

const totalPages = computed(() => {
  return Math.ceil(props.filteredRecords / props.pageLength) || 1;
});

const canGoPrev = computed(() => props.currentPage > 0);
const canGoNext = computed(() => props.currentPage < totalPages.value - 1);

const handleRowClick = (id) => {
  if (!props.isLoading) {
    emit("row-click", id);
  }
};

const handleDelete = (id) => {
  if (!props.isLoading) {
    emit("delete-item", id);
  }
};

const handlePageLengthChange = (event) => {
  if (props.isLoading) return;
  const newLength = parseInt(event.target.value);
  emit("page-length-change", newLength);
};

const handlePrevPage = () => {
  if (canGoPrev.value && !props.isLoading) {
    emit("page-change", props.currentPage - 1);
  }
};

const handleNextPage = () => {
  if (canGoNext.value && !props.isLoading) {
    emit("page-change", props.currentPage + 1);
  }
};
</script>

<style scoped>
/* Employee Photo */
.employee-photo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e9ecef;
}

.employee-photo-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #e9ecef;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #adb5bd;
  font-size: 24px;
}

/* Table styling */
.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  padding: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #6c757d;
  white-space: nowrap;
}

.table tbody td {
  padding: 14px 12px;
  border-bottom: 1px solid #dee2e6;
  font-size: 14px;
  color: #212529;
  vertical-align: middle;
}

.table tbody tr {
  transition: background-color 0.15s ease-in-out;
}

.table tbody tr:hover {
  background-color: #f5f5f5;
}

/* Buttons */
.btn-outline-danger:hover:not(:disabled) {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Footer */
.card-footer {
  padding: 16px 20px;
}

/* Mobile */
@media (max-width: 768px) {
  .card-footer {
    padding: 12px 15px;
  }

  .d-flex.gap-3 {
    gap: 0.5rem !important;
  }

  .d-flex.gap-2 {
    gap: 0.5rem !important;
  }

  .employee-photo,
  .employee-photo-placeholder {
    width: 36px;
    height: 36px;
  }

  .employee-photo-placeholder {
    font-size: 20px;
  }
}
</style>
