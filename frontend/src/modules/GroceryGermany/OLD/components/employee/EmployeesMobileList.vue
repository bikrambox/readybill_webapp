<template>
  <div id="mobileEmployeeList">
    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="text-muted mt-2 small">Loading employees...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="displayData.length === 0" class="text-center text-muted py-5">
      <i class="bi bi-inbox d-block mb-2" style="font-size: 3rem; color: #dee2e6;"></i>
      <p class="mb-0">No employees found</p>
    </div>

    <!-- Employee Cards -->
    <div v-else>
      <div 
        v-for="item in displayData" 
        :key="item.id" 
        class="card mb-3 shadow-sm border-0"
      >
        <div 
          class="card-body p-3" 
          @click="$emit('row-click', item.id)"
          style="cursor: pointer;"
        >
          <div class="d-flex align-items-start gap-3">
            <!-- Photo -->
            <div class="flex-shrink-0">
              <img 
                v-if="item.photo" 
                :src="item.photo" 
                alt="Employee" 
                class="employee-photo"
              />
              <div v-else class="employee-photo-placeholder">
                <i class="bi bi-person-circle"></i>
              </div>
            </div>
            
            <!-- Employee Info -->
            <div class="flex-grow-1">
              <h6 class="fw-semibold mb-2">{{ item.name }}</h6>
              <div class="d-flex flex-column gap-1">
                <div class="d-flex align-items-center gap-2 text-muted small">
                  <i class="bi bi-envelope"></i>
                  <span>{{ item.email }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 text-muted small">
                  <i class="bi bi-telephone"></i>
                  <span>{{ item.contact || '-' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="card-footer bg-light border-top p-2">
          <div class="d-flex gap-2">
            <button 
              class="btn btn-sm btn-light flex-grow-1"
              @click="$emit('row-click', item.staff_id)"
            >
              <i class="bi bi-eye"></i> View
            </button>
            <button 
              class="btn btn-sm btn-outline-danger"
              @click.stop="$emit('delete-item', item.staff_id)"
            >
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination Info -->
      <div class="text-center text-muted small mt-3 mb-2 fw-medium">
        {{ pageInfo.start }}-{{ pageInfo.end }} of {{ pageInfo.total }}
      </div>

      <!-- Pagination Buttons -->
      <div class="d-flex justify-content-center gap-2 mt-2 mb-4">
        <button 
          class="btn btn-sm btn-outline-secondary px-3"
          @click="$emit('prev-page')"
          :disabled="!pageInfo.hasPrev"
        >
          <i class="bi bi-chevron-left"></i> Previous
        </button>
        <button 
          class="btn btn-sm btn-outline-secondary px-3"
          @click="$emit('next-page')"
          :disabled="!pageInfo.hasNext"
        >
          Next <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  displayData: {
    type: Array,
    required: true
  },
  isLoading: {
    type: Boolean,
    default: false
  },
  pageInfo: {
    type: Object,
    default: () => ({
      start: 0,
      end: 0,
      total: 0,
      hasPrev: false,
      hasNext: false
    })
  }
})

const emit = defineEmits([
  'row-click', 
  'delete-item', 
  'prev-page', 
  'next-page'
])
</script>

<style scoped>
/* Employee Photo */
.employee-photo {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e9ecef;
}

.employee-photo-placeholder {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #adb5bd;
  font-size: 28px;
}

/* Card hover effect */
.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.card-body:active {
  background-color: #f8f9fa;
}

/* Mobile adjustments */
@media (max-width: 360px) {
  .employee-photo,
  .employee-photo-placeholder {
    width: 42px;
    height: 42px;
  }
  
  .employee-photo-placeholder {
    font-size: 24px;
  }
}
</style>
