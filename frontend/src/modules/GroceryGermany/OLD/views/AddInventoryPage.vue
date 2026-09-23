<template>
  <div class="add-inventory-page">
    <div class="page-content">
      <div class="page-header">
        <div class="breadcrumb-section">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <router-link to="/">Home</router-link>
              </li>
              <li class="breadcrumb-item active">Inventory</li>
            </ol>
          </nav>
          <h1 class="page-title">Add Inventory</h1>
        </div>

        <!-- Desktop Only: Upload/Download Buttons with Info Icon -->
        <div class="header-actions">
          <button
            class="btn btn-icon-info"
            @click="showInfo"
            title="Information"
          >
            <i class="bi bi-info-circle"></i>
          </button>
          <button class="btn btn-outline-primary" @click="handleUploadData">
            <i class="bi bi-upload"></i>
            <span class="btn-text">Upload Data</span>
          </button>
          <button class="btn btn-primary" @click="handleDownloadData">
            <i class="bi bi-download"></i>
            <span class="btn-text">Download Data</span>
          </button>
        </div>
      </div>

      <div class="inventory-card">
        <InventoryForm @submit="handleSubmit" @cancel="handleCancel" />
      </div>

      <!-- Mobile Only: Data Management Section -->
      <div class="data-management-section mobile-only">
        <div class="section-header">
          <h5 class="section-title">Data Management</h5>
          <button class="btn-info-mobile" @click="showInfo" title="Information">
            <i class="bi bi-info-circle"></i>
          </button>
        </div>
        <div class="management-actions">
          <button class="btn btn-outline-primary" @click="handleUploadData">
            <i class="bi bi-upload"></i>
            Upload Data
          </button>
          <button class="btn btn-primary" @click="handleDownloadData">
            <i class="bi bi-download"></i>
            Download Data
          </button>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { ref } from "vue";
import InventoryForm from "../components/InventoryForm.vue";
import Footer from "@/modules/GroceryGermany/components/Footer.vue";
import { useInventoryStore } from "@/modules/GroceryGermany/stores/inventory";

const inventoryStore = useInventoryStore();
const isDownloading = ref(false);

const handleSubmit = (formData) => {
  console.log("Form submitted:", formData);
};

const handleCancel = () => {
  console.log("Form cancelled");
};

const handleUploadData = () => {
  console.log("Upload data clicked");
};

const handleDownloadData = async () => {
  if (isDownloading.value) return
  
  isDownloading.value = true
  
  try {
    const result = await inventoryStore.exportData()
    
    if (result.success) {
      // Show success toast
      // toast.success(result.message || 'Export successful')
    } else {
      // Show error toast
      // toast.error(inventoryStore.errors.join(', ') || 'Export failed')
    }
  } catch (error) {
    console.error('Download error:', error)
    toast.error('Failed to download file')
  } finally {
    isDownloading.value = false
  }
};

const showInfo = () => {
  console.log("Info clicked");
  // Add your info modal/tooltip logic here
  alert("Data Management Information");
};
</script>

<style scoped>
.add-inventory-page {
  display: flex;
  flex-direction: column;
  min-height: 100%;
}

.page-content {
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
  flex: 1;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
  gap: 20px;
  flex-wrap: wrap;
}

.breadcrumb-section {
  flex: 1;
}

.breadcrumb {
  margin-bottom: 10px;
  background: transparent;
  padding: 0;
  font-size: 14px;
}

.breadcrumb-item a {
  color: #6c757d;
  text-decoration: none;
}

.breadcrumb-item.active {
  color: #333;
}

.page-title {
  font-size: 28px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.header-actions .btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
}

.btn-icon-info {
  background-color: transparent;
  border: 1px solid #dee2e6;
  color: #6c757d;
  padding: 10px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon-info:hover {
  background-color: #f8f9fa;
  color: #0066cc;
  border-color: #0066cc;
}

.btn-icon-info i {
  font-size: 18px;
}

.inventory-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

/* Data Management Section - Mobile Only */
.data-management-section {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
  display: none;
}

.data-management-section.mobile-only {
  display: none;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.btn-info-mobile {
  background-color: transparent;
  border: 1px solid #dee2e6;
  color: #6c757d;
  padding: 8px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-info-mobile:hover {
  background-color: #f8f9fa;
  color: #0066cc;
  border-color: #0066cc;
}

.btn-info-mobile i {
  font-size: 16px;
}

.management-actions {
  display: flex;
  gap: 12px;
  flex-direction: row;
}

.management-actions .btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  flex: 1;
}

/* Desktop: Show header buttons, hide data management section */
@media (min-width: 769px) {
  .header-actions {
    display: flex;
  }

  .data-management-section.mobile-only {
    display: none;
  }
}

/* Mobile: Hide header buttons, show data management section */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }

  .header-actions {
    display: none;
  }

  .data-management-section.mobile-only {
    display: block;
  }

  .inventory-card {
    padding: 20px;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 24px;
  }

  .inventory-card {
    padding: 15px;
    border-radius: 8px;
  }

  .data-management-section {
    padding: 20px;
  }

  .section-title {
    font-size: 16px;
  }

  .management-actions {
    gap: 10px;
  }

  .management-actions .btn {
    padding: 10px 12px;
    font-size: 13px;
    gap: 6px;
  }

  .management-actions .btn i {
    font-size: 16px;
  }

  .btn-info-mobile {
    width: 32px;
    height: 32px;
    padding: 6px;
  }

  .btn-info-mobile i {
    font-size: 14px;
  }
}
</style>
