<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  transaction: {
    type: Object,
    required: true
  },
  showMarkPaid: {
    type: Boolean,
    default: true
  },
  isAdmin: { type: Boolean, default: false }
})

const emit = defineEmits(['mark-paid', 'card-click'])

const handleMarkPaid = (event) => {
  event.stopPropagation()
  emit('mark-paid', props.transaction.id)
}

const handleCardClick = () => {
  emit('card-click', props.transaction)
}
</script>

<template>
  <div 
    class="card mb-3 shadow-sm clickable-card"
    @click="handleCardClick"
  >
    <div class="card-body">
      <!-- Transaction Header -->
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h6 class="text-primary fw-bold mb-1">{{ transaction.id }}</h6>
          <p class="mb-0 fw-semibold">{{ transaction.customer }}</p>
          <small class="text-muted">{{ transaction.date }}</small>
        </div>
        <div class="text-end">
          <h5 class="fw-bold mb-0">₹{{ transaction.total.toFixed(2) }}</h5>
        </div>
      </div>

      <!-- Transaction Details -->
      <div class="row g-3 mb-3">
        <div class="col-6">
          <div class="small text-muted mb-1">QUANTITY</div>
          <div class="fw-medium">{{ transaction.quantity }} {{ transaction.quantityUnit }}</div>
        </div>
        <div class="col-6">
          <div class="small text-muted mb-1">USER</div>
          <div class="fw-medium">{{ transaction.user }}</div>
        </div>
      </div>

      <!-- Mark Paid Button -->
      <button 
        v-if="showMarkPaid && isAdmin"
        class="btn btn-success w-100 py-2 fw-semibold"
        @click="handleMarkPaid"
      >
        Mark Paid
      </button>
      <!-- <div 
        v-else
        class="badge bg-success w-100 py-2"
      >
        <i class="bi bi-check-circle me-1"></i>Marked Paid
      </div> -->

    </div>
  </div>
</template>

<style scoped>
.card {
  border-radius: 12px;
  border: 1px solid #e8e8e8;
}

.small {
  font-size: 0.75rem;
}

.clickable-card {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.clickable-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
