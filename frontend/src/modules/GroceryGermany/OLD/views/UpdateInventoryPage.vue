<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import { storeToRefs } from "pinia";
import { useInventoryManagementStore } from "@/modules/GroceryGermany/stores/inventoryManagement";
import { useUserPreferencesStore } from "@/modules/GroceryGermany/stores/userPreferences";

import UpdateInventoryFilters from "../components/inventory/UpdateInventoryFilters.vue";
import UpdateInventoryTable from "../components/inventory/UpdateInventoryTable.vue";
import UpdateInventoryMobileList from "../components/inventory/UpdateInventoryMobileList.vue";
import UpdateInventoryEditModal from "../components/inventory/UpdateInventoryEditModal.vue";
import Footer from "@/modules/GroceryGermany/components/Footer.vue";
import { useAuthStore } from "@/modules/Authentication/stores/authStore";
import ConfirmModal from "@/modules/Core/components/modals/ConfirmModal.vue";

const inventoryStore = useInventoryManagementStore();
const preferencesStore = useUserPreferencesStore();

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.isAdmin === 1);

const {
  items,
  loading,
  errors,
  successMessage,
  selectedItems,
  uniqueItemNames,
  selectedCount,
  recordsTotal,
  recordsFiltered,
  currentPage,
  pageLength,
} = storeToRefs(inventoryStore);

const editModalComponent = ref(null);
const selectAll = ref(false);
const editItem = ref(null);

const searchQuery = ref("");
const filterColumn = ref("item_name");
const confirmModal = ref(null);

const confirmData = ref({
  message: "",
  titel: "",
});

const initialLoadComplete = ref(false);

// Watch items for changes (debugging)
watch(
  items,
  (newItems) => {
    console.log("📊 Items updated, count:", newItems.length);
  },
  { deep: true }
);

// Load data on mount - AFTER table renders
onMounted(async () => {
  console.log("🚀 Component mounted");
  
  // Wait for next tick to ensure table structure renders first
  await nextTick();
  
  // Now fetch data
  await Promise.all([
    inventoryStore.fetchItems(0, 10, "", "item_name"),
    preferencesStore.fetchUserPreferences(),
  ]);
  
  initialLoadComplete.value = true;
  console.log("✅ Initial data loaded");
});

// Filter handlers
const handleSearchUpdate = (value) => {
  searchQuery.value = value;
  performSearch();
};

const handleFilterColumnUpdate = (value) => {
  filterColumn.value = value;
  searchQuery.value = ""; // Clear search when changing filter
  performSearch();
};

const performSearch = async () => {
  console.log("🔍 Searching...", {
    searchQuery: searchQuery.value,
    filterColumn: filterColumn.value,
  });
  await inventoryStore.performSearch(searchQuery.value, filterColumn.value);
};

// Pagination handlers
const handlePageChange = async (page) => {
  console.log("📄 Changing page to:", page);
  await inventoryStore.changePage(page);
  selectAll.value = false;
};

const handlePageLengthChange = async (length) => {
  console.log("📏 Changing page length to:", length);
  await inventoryStore.changePageLength(length);
  selectAll.value = false;
};

// Selection handlers
const selectRow = (id) => {
  inventoryStore.selectItem(id);
  console.log("✅ Selected item:", id);
};

const deselectRow = (id) => {
  inventoryStore.deselectItem(id);
  selectAll.value = false;
  console.log("❌ Deselected item:", id);
};

const toggleSelectAll = (checked) => {
  selectAll.value = checked;

  if (checked) {
    const ids = items.value.map((item) => item.id);
    inventoryStore.selectAllItems(ids);
    console.log("✅ Selected all items:", ids);
  } else {
    items.value.forEach((item) => {
      inventoryStore.deselectItem(item.id);
    });
    console.log("❌ Deselected all items");
  }
};

const deselectAll = () => {
  inventoryStore.clearSelection();
  selectAll.value = false;
  console.log("🔄 Cleared all selections");
};

const handleCancel = async () => {
  const itemCount = selectedItems.value.size;
  if (!itemCount) {
    return;
  }
  const confirmMessage =
    itemCount === 1
      ? "Are you sure you want to delete this item?"
      : `Are you sure you want to delete ${itemCount} items?`;

  confirmData.value = {
    title: "Confirmation",
    message: confirmMessage,
  };
  confirmModal.value.show();
};

const deleteSelected = async () => {
  if (selectedItems.value.size === 0) {
    alert("Please select items to delete");
    return;
  }

  const itemCount = selectedItems.value.size;

  console.log("🗑️ Deleting items:", Array.from(selectedItems.value));
  const result = await inventoryStore.deleteItems(selectedItems.value);
  console.log("🏁 Delete result:", result);

  if (result.success) {
    selectAll.value = false;

    // Wait for Vue to update DOM
    await nextTick();

    console.log("✅ Items deleted successfully");
    console.log("📊 Updated items count:", items.value.length);
    console.log("📈 Updated total:", recordsFiltered.value);

    // Auto-hide success message after 5 seconds
    setTimeout(() => {
      inventoryStore.clearSuccessMessage();
    }, 5000);
  } else {
    console.error("❌ Delete failed:", result.errors);
  }

  confirmModal.value.hide();
};

const openEditModal = (id) => {
  const item = items.value.find((i) => i.id === id);
  if (item) {
    editItem.value = { ...item };
    editModalComponent.value?.show();
  }
};

const closeEditModal = () => {
  editItem.value = null;
};

const saveEdit = async (updatedItem) => {
  console.log("💾 Saving item:", updatedItem);

  const result = await inventoryStore.updateItem(updatedItem);

  console.log("💾 Update result:", result);

  if (result.success) {
    // Close modal on success
    editModalComponent.value?.hide();

    await nextTick();

    console.log("✅ Item updated successfully");

    // Auto-hide success message after 5 seconds
    setTimeout(() => {
      inventoryStore.clearSuccessMessage();
    }, 5000);
  } else {
    console.error("❌ Update failed:", result.errors);
  }
};

const clearErrors = () => {
  inventoryStore.clearErrors();
};

const clearSuccessMessage = () => {
  inventoryStore.clearSuccessMessage();
};
</script>

<template>
  <div class="update-inventory-page">
    <div class="container-fluid px-3 px-lg-4">
      <!-- Page Header -->
      <div class="row mb-3 mb-lg-4">
        <div class="col-12">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-2">
              <li class="breadcrumb-item">
                <a href="#" class="text-decoration-none">Home</a>
              </li>
              <li class="breadcrumb-item">
                <a href="#" class="text-decoration-none"
                  >View & Update Inventory</a
                >
              </li>
            </ol>
          </nav>
          <h1 class="h3 h2-lg fw-bold mb-0">Update Inventory</h1>
        </div>
      </div>

      <!-- Error Alert -->
      <div
        v-if="errors.length > 0"
        class="alert alert-danger alert-dismissible fade show mb-3"
        role="alert"
      >
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Error:</strong>
        <ul class="mb-0 mt-2">
          <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
        </ul>
        <button type="button" class="btn-close" @click="clearErrors"></button>
      </div>

      <!-- Success Alert -->
      <div
        v-if="successMessage"
        class="alert alert-success alert-dismissible fade show mb-3"
        role="alert"
      >
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Success:</strong> {{ successMessage }}
        <button
          type="button"
          class="btn-close"
          @click="clearSuccessMessage"
        ></button>
      </div>

      <!-- Filters Component -->
      <UpdateInventoryFilters
        :item-names="uniqueItemNames"
        :search-query="searchQuery"
        :filter-column="filterColumn"
        :selected-count="selectedCount"
        @update:search-query="handleSearchUpdate"
        @update:filter-column="handleFilterColumnUpdate"
        @deselect="deselectAll"
        @delete="handleCancel"
        :is-admin="isAdmin"
      />

      <!-- Desktop Table Component -->
      <div class="d-none d-lg-block">
        <UpdateInventoryTable
          :key="`table-${recordsFiltered}-${currentPage}`"
          :data="items"
          :selected-items="selectedItems"
          :select-all="selectAll"
          :current-page="currentPage"
          :page-length="pageLength"
          :records-total="recordsTotal"
          :records-filtered="recordsFiltered"
          :loading="loading"
          @row-selected="selectRow"
          @row-deselected="deselectRow"
          @view-item="openEditModal"
          @toggle-select-all="toggleSelectAll"
          @page-change="handlePageChange"
          @page-length-change="handlePageLengthChange"
        />
      </div>

      <!-- Mobile List Component -->
      <div class="d-lg-none">
        <UpdateInventoryMobileList
          :key="`mobile-${recordsFiltered}-${currentPage}`"
          :display-data="items"
          :selected-items="selectedItems"
          :loading="loading"
          :page-info="{
            start: currentPage * pageLength + 1,
            end: Math.min((currentPage + 1) * pageLength, recordsFiltered),
            total: recordsFiltered,
            hasPrev: currentPage > 0,
            hasNext:
              currentPage < Math.ceil(recordsFiltered / pageLength) - 1,
          }"
          @row-selected="selectRow"
          @row-deselected="deselectRow"
          @view-item="openEditModal"
          @edit-item="openEditModal"
          @prev-page="handlePageChange(currentPage - 1)"
          @next-page="handlePageChange(currentPage + 1)"
          @deselect-all="deselectAll"
          @delete-selected="handleCancel"
          :is-admin="isAdmin"
        />
      </div>
    </div>

    <!-- Footer -->
    <Footer />

    <!-- Edit Modal Component -->
    <UpdateInventoryEditModal
      ref="editModalComponent"
      :item="editItem"
      @save="saveEdit"
      @close="closeEditModal"
    />

    <!-- Single modal instance -->
    <ConfirmModal
      ref="confirmModal"
      :message="confirmData.message"
      :title="confirmData.title"
      @confirm="deleteSelected"
    />
  </div>
</template>

<style scoped>
.update-inventory-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f8f9fa;
  position: relative;
}

.container-fluid {
  flex: 1;
  padding-top: 1.5rem;
  padding-bottom: 2rem;
}

.breadcrumb-item a {
  color: #6c757d;
}

.breadcrumb-item.active {
  color: #333;
}

/* Enhanced Alerts */
.alert {
  border-radius: 8px;
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  animation: slideInDown 0.3s ease-out;
}

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert-success {
  background-color: #d1e7dd;
  color: #0f5132;
  border-left: 4px solid #198754;
}

.alert-danger {
  background-color: #f8d7da;
  color: #842029;
  border-left: 4px solid #dc3545;
}

.alert ul {
  padding-left: 1.5rem;
  margin-bottom: 0;
}

.alert i {
  font-size: 1.1rem;
}

.alert-dismissible .btn-close {
  padding: 1rem;
}

@media (max-width: 991px) {
  .container-fluid {
    padding-top: 1rem;
  }
}
</style>
