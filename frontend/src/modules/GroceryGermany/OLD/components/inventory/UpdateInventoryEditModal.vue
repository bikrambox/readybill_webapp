<template>
  <div 
    class="modal fade" 
    id="itemDetailsModal" 
    ref="modalElement"
    tabindex="-1" 
    aria-labelledby="itemDetailsModalLabel" 
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title" id="itemDetailsModalLabel">Item Details</h5>
          <button 
            type="button" 
            class="btn-close" 
            @click="handleClose"
            aria-label="Close"
          ></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
          <p class="text-muted small mb-4">
            Fields marked with a star (<span class="text-danger">*</span>) are mandatory
          </p>

          <form @submit.prevent="handleSubmit">
            <!-- Item Name -->
            <div class="mb-3">
              <label for="itemName" class="form-label">
                Item Name<span class="text-danger">*</span>
              </label>
              <input 
                type="text" 
                class="form-control" 
                id="itemName"
                v-model="formData.name"
                required
                :disabled="saving"
              />
            </div>

            <!-- Stock Quantity & Minimum Stock Alert -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="stockQuantity" class="form-label">Stock Quantity</label>
                <input 
                  type="number" 
                  step="0.01"
                  class="form-control" 
                  id="stockQuantity"
                  v-model="formData.stock"
                  :disabled="saving"
                />
              </div>
              <div class="col-md-6">
                <label for="minStockAlert" class="form-label">Minimum Stock Alert</label>
                <input 
                  type="number" 
                  step="0.01"
                  class="form-control" 
                  id="minStockAlert"
                  v-model="formData.minStockAlert"
                  :disabled="saving"
                />
              </div>
            </div>

            <!-- Unit -->
            <div class="mb-3">
              <label for="unit" class="form-label">
                Unit<span class="text-danger">*</span>
              </label>
              <select 
                class="form-select" 
                id="unit"
                v-model="formData.shortUnit"
                required
                :disabled="saving"
              >
                <option value="">Select Unit</option>
                <option value="PCS">Piece (PCS)</option>
                <option value="KG">Kilogram (KG)</option>
                <option value="GM">Gram (GM)</option>
                <option value="LTR">Liter (LTR)</option>
                <option value="ML">Milliliter (ML)</option>
                <option value="BTL">Bottle (BTL)</option>
                <option value="PCK">Pack (PCK)</option>
                <option value="BOX">Box (BOX)</option>
              </select>
            </div>

            <!-- HSN/SAC Code -->
            <div class="mb-3">
              <label for="hsnCode" class="form-label">HSN/ SAC Code</label>
              <input 
                type="text" 
                class="form-control" 
                id="hsnCode"
                v-model="formData.hsnCode"
                :disabled="saving"
              />
            </div>

            <!-- MRP & Rate -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="mrp" class="form-label">MRP</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input 
                    type="number" 
                    step="0.01"
                    class="form-control" 
                    id="mrp"
                    v-model="formData.mrp"
                    :disabled="saving"
                  />
                  <span class="input-group-text text-muted">per {{ formData.shortUnit || 'PCS' }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <label for="rate" class="form-label">
                  Rate<span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input 
                    type="number" 
                    step="0.01"
                    class="form-control" 
                    id="rate"
                    v-model="formData.rate"
                    required
                    :disabled="saving"
                  />
                  <span class="input-group-text text-muted">per {{ formData.shortUnit || 'PCS' }}</span>
                </div>
              </div>
            </div>

            <!-- Tax & Rate -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="tax" class="form-label">
                  Tax<span class="text-danger">*</span>
                </label>
                <select 
                  class="form-select" 
                  id="tax"
                  v-model="formData.tax1"
                  required
                  :disabled="saving"
                >
                  <option value="">Select Tax</option>
                  <option value="GST">GST</option>
                  <option value="CGST">CGST</option>
                  <option value="SGST">SGST</option>
                  <option value="IGST">IGST</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="taxRate" class="form-label">
                  Rate<span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input 
                    type="number" 
                    step="0.01"
                    class="form-control" 
                    id="taxRate"
                    v-model="formData.rate1"
                    required
                    :disabled="saving"
                  />
                  <span class="input-group-text">%</span>
                  <button 
                    class="btn btn-primary" 
                    type="button"
                    @click="toggleSecondaryTax"
                    :disabled="saving"
                  >
                    <i class="bi bi-plus"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Secondary Tax (if enabled) -->
            <div v-if="showSecondaryTax" class="row mb-3">
              <div class="col-md-6">
                <label for="tax2" class="form-label">Tax 2</label>
                <select 
                  class="form-select" 
                  id="tax2"
                  v-model="formData.tax2"
                  :disabled="saving"
                >
                  <option value="">Select Tax</option>
                  <option value="GST">GST</option>
                  <option value="CGST">CGST</option>
                  <option value="SGST">SGST</option>
                  <option value="IGST">IGST</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="taxRate2" class="form-label">Rate 2</label>
                <div class="input-group">
                  <input 
                    type="number" 
                    step="0.01"
                    class="form-control" 
                    id="taxRate2"
                    v-model="formData.rate2"
                    :disabled="saving"
                  />
                  <span class="input-group-text">%</span>
                  <button 
                    class="btn btn-outline-danger" 
                    type="button"
                    @click="removeSecondaryTax"
                    :disabled="saving"
                  >
                    <i class="bi bi-dash"></i>
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button 
            type="button" 
            class="btn btn-outline-secondary" 
            @click="handleClose"
            :disabled="saving"
          >
            Cancel
          </button>
          <button 
            type="button" 
            class="btn btn-primary" 
            @click="handleSubmit"
            :disabled="saving || !isFormValid"
          >
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <span v-if="saving">Updating...</span>
            <span v-else>Update</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Modal } from 'bootstrap';

const props = defineProps({
  item: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['save', 'close']);

const modalElement = ref(null);
let modalInstance = null;
const saving = ref(false);
const showSecondaryTax = ref(false);

// Form data
const formData = ref({
  id: null,
  name: '',
  stock: 0,
  minStockAlert: 0,
  shortUnit: 'PCS',
  fullUnit: 'Piece',
  hsnCode: '',
  mrp: 0,
  rate: 0,
  tax1: 'GST',
  rate1: 0,
  tax2: '',
  rate2: 0,
  tags: []
});

// Watch for item changes
watch(() => props.item, (newItem) => {
  if (newItem) {
    console.log('📝 Editing item:', newItem);
    formData.value = {
      id: newItem.id,
      name: newItem.name || '',
      stock: newItem.stock || 0,
      minStockAlert: newItem.minStockAlert || 0,
      shortUnit: newItem.shortUnit || 'PCS',
      fullUnit: newItem.fullUnit || 'Piece',
      hsnCode: newItem.hsnCode || '',
      mrp: newItem.mrp || 0,
      rate: newItem.rate || 0,
      tax1: newItem.tax1 || 'GST',
      rate1: newItem.rate1 || 0,
      tax2: newItem.tax2 || '',
      rate2: newItem.rate2 || 0,
      tags: newItem.tags || []
    };
    
    // Show secondary tax if it exists
    showSecondaryTax.value = !!(newItem.tax2 && newItem.rate2);
  }
}, { immediate: true, deep: true });

// Form validation
const isFormValid = computed(() => {
  return formData.value.name && 
         formData.value.shortUnit && 
         formData.value.rate > 0 &&
         formData.value.tax1 &&
         formData.value.rate1 >= 0;
});

// Toggle secondary tax
const toggleSecondaryTax = () => {
  showSecondaryTax.value = !showSecondaryTax.value;
  if (!showSecondaryTax.value) {
    formData.value.tax2 = '';
    formData.value.rate2 = 0;
  }
};

// Remove secondary tax
const removeSecondaryTax = () => {
  showSecondaryTax.value = false;
  formData.value.tax2 = '';
  formData.value.rate2 = 0;
};

// Handle submit
const handleSubmit = async () => {
  if (!isFormValid.value || saving.value) return;
  
  console.log('💾 Saving item data:', formData.value);
  saving.value = true;
  
  try {
    emit('save', { ...formData.value });
    // Modal will be hidden by parent component after successful save
  } catch (error) {
    console.error('❌ Error saving item:', error);
  } finally {
    saving.value = false;
  }
};

// Handle close
const handleClose = () => {
  if (saving.value) return;
  
  hide();
  emit('close');
};

// Show modal
const show = () => {
  if (modalInstance) {
    modalInstance.show();
  }
};

// Hide modal
const hide = () => {
  if (modalInstance) {
    modalInstance.hide();
  }
};

// Initialize modal
onMounted(() => {
  if (modalElement.value) {
    modalInstance = new Modal(modalElement.value, {
      backdrop: 'static',
      keyboard: false
    });
    
    // Listen for modal hidden event
    modalElement.value.addEventListener('hidden.bs.modal', () => {
      emit('close');
    });
  }
});

// Cleanup
onUnmounted(() => {
  if (modalInstance) {
    modalInstance.dispose();
  }
});

// Expose methods
defineExpose({
  show,
  hide
});
</script>

<style scoped>
.modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
}

.modal-body {
  padding: 1.5rem;
  max-height: calc(100vh - 200px);
  overflow-y: auto;
}

.form-label {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.5rem;
}

.text-danger {
  color: #dc3545 !important;
}

.form-control:focus,
.form-select:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.input-group-text {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  color: #6c757d;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
}

.modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
}

/* Scrollbar styling */
.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-dialog {
    margin: 0.5rem;
  }
  
  .modal-body {
    padding: 1rem;
    max-height: calc(100vh - 150px);
  }
}
</style>
