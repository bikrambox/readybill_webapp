<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  transactions: {
    type: Array,
    required: true,
  },
  showMarkPaid: {
    type: Boolean,
    default: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  isAdmin: { type: Boolean, default: false }
});

const emit = defineEmits(["mark-paid", "row-click"]);

const handleMarkPaid = (event, id) => {
  event.stopPropagation();
  emit("mark-paid", id);
};

const handleRowClick = (transaction) => {
  emit("row-click", transaction);
};
</script>

<template>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Invoice</th>
          <th>Products</th>
          <th>Total</th>
          <th>User</th>
          <th>Date</th>
          <th v-if="showMarkPaid && isAdmin">Action</th>
        </tr>
      </thead>
      <tbody>
        <!-- Loading State -->
        <template v-if="loading">
          <tr>
            <td :colspan="showMarkPaid ? 6 : 5" class="text-center py-4">
              <div
                class="spinner-border spinner-border-sm text-primary"
                role="status"
              >
                <span class="visually-hidden">Loading...</span>
              </div>
              <span class="ms-2 text-muted">Loading data...</span>
            </td>
          </tr>
        </template>

        <!-- Data Rows -->
        <template v-else-if="transactions.length > 0">
          <tr
            v-for="transaction in transactions"
            :key="transaction.id"
            @click="handleRowClick(transaction)"
            class="clickable-row"
            :class="{ 'highlight-new-row': transaction.isNew }"
          >
            <td class="text-primary fw-semibold">{{ transaction.id }}</td>
            <td>{{ transaction.customer }}</td>
            <td class="fw-semibold">₹{{ transaction.total.toFixed(2) }}</td>
            <td>
              <span class="badge bg-success-subtle text-success">{{
                transaction.user
              }}</span>
            </td>
            <td>
              <small>{{ transaction.date }}</small>
            </td>
            <td v-if="showMarkPaid && isAdmin">
              <button
                class="btn btn-success btn-sm"
                @click="handleMarkPaid($event, transaction.id)"
              >
                <i class="bi bi-check-circle me-1"></i>Mark Paid
              </button>
            </td>
          </tr>
        </template>

        <!-- Empty State -->
        <template v-else>
          <tr>
            <td :colspan="showMarkPaid ? 6 : 5" class="text-center py-4 text-muted">
              No items found
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table thead th {
  font-weight: 600;
  font-size: 0.875rem;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge {
  padding: 0.375rem 0.75rem;
  font-weight: 500;
}

.clickable-row {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.clickable-row:hover {
  background-color: #f8f9fa;
}

.table tbody td {
  padding: 14px 12px;
  border-bottom: 1px solid #dee2e6;
  font-size: 14px;
  color: #212529;
  vertical-align: middle;
}


/* WebSocket Animation Styles */
.highlight-new-row {
  background-color: #d1ecf1 !important;
  animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
  0% {
    background-color: #fff3cd;
  }
  50% {
    background-color: #d1ecf1;
  }
  100% {
    background-color: #d1ecf1;
  }
}

.transition-new-row {
  animation: slideInFromTop 0.5s ease-out;
}

@keyframes slideInFromTop {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.transition-fade-out {
  animation: fadeOut 0.6s ease-out forwards;
}

@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
    transform: translateX(-20px);
  }
}

</style>
