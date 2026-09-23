<template>
  <div 
    ref="modalElement" 
    class="modal fade" 
    tabindex="-1" 
    aria-labelledby="transactionModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="transactionModalLabel">
            Transaction Details
            <span v-if="transactionData" class="text-muted ms-2">
              #{{ transactionData.invoice_number }}
            </span>
          </h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        
        <div class="modal-body">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>

          <div v-else-if="transactionData" class="table-responsive">
            <table class="table table-sm">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Item Name</th>
                  <th>Quantity</th>
                  <th>Rate</th>
                  <th>Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(item, index) in itemList" 
                  :key="index"
                  :class="{ 'table-danger': item.isRefund === 1 }"
                >
                  <td>{{ index + 1 }}</td>
                  <td>{{ item.itemName }}</td>
                  <td>{{ item.quantity }} {{ item.selectedUnit }}</td>
                  <td>₹{{ item.rate }}</td>
                  <td>
                    <span v-if="item.isRefund === 1">
                      − ₹{{ Math.abs(item.amount).toFixed(2) }}
                    </span>
                    <span v-else>
                      ₹{{ item.amount.toFixed(2) }}
                    </span>
                  </td>
                  <td>
                    <span 
                      class="badge" 
                      :class="item.isRefund === 1 ? 'bg-danger' : 'bg-info text-dark'"
                    >
                      {{ item.isRefund === 1 ? 'Refund' : 'Sold' }}
                    </span>
                  </td>
                </tr>
                <tr class="fw-bold table-light">
                  <td colspan="4" class="text-end">Grand Total</td>
                  <td colspan="2">
                    <span v-if="grandTotal < 0">
                      − ₹{{ Math.abs(grandTotal).toFixed(2) }}
                    </span>
                    <span v-else>
                      ₹{{ grandTotal.toFixed(2) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="modal-footer">
          <button 
            type="button" 
            class="btn btn-secondary" 
            data-bs-dismiss="modal"
          >
            Close
          </button>
          <button 
            v-if="transactionData"
            type="button" 
            class="btn btn-primary"
            @click="generateInvoice"
          >
            <i class="bi bi-printer me-1"></i>
            Generate Invoice
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import { Modal } from 'bootstrap';
import axios from 'axios';

const props = defineProps({
  transactionId: {
    type: Number,
    default: null
  }
});

const emit = defineEmits(['save']);

const modalElement = ref(null);
let modalInstance = null;

const loading = ref(false);
const transactionData = ref(null);
const itemList = ref([]);

const grandTotal = computed(() => {
  return itemList.value.reduce((sum, item) => sum + parseFloat(item.amount), 0);
});

const fetchTransactionData = async () => {
  if (!props.transactionId) return;

  loading.value = true;
  
  try {
    const response = await axios.get(
      `${window.grocery_india_api_url}transaction/${props.transactionId}`,
      {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      }
    );

    transactionData.value = response.data.data;
    itemList.value = JSON.parse(response.data.data.item_list);
  } catch (error) {
    console.error('Error fetching transaction:', error);
  } finally {
    loading.value = false;
  }
};

const generateInvoice = () => {
  if (transactionData.value) {
    const url = `/invoice/${transactionData.value.id}/`;
    window.open(url, '_blank');
  }
};

const show = () => {
  if (modalInstance) {
    fetchTransactionData();
    modalInstance.show();
  }
};

const hide = () => {
  if (modalInstance) {
    modalInstance.hide();
  }
};

watch(() => props.transactionId, () => {
  if (props.transactionId && modalInstance) {
    fetchTransactionData();
  }
});

onMounted(() => {
  if (modalElement.value) {
    modalInstance = new Modal(modalElement.value);
  }
});

onUnmounted(() => {
  if (modalInstance) {
    modalInstance.dispose();
  }
});

defineExpose({
  show,
  hide
});
</script>

<style scoped>
.modal-dialog {
  max-width: 800px;
}

.table th {
  font-size: 13px;
  font-weight: 600;
}

.table td {
  font-size: 13px;
}
</style>
