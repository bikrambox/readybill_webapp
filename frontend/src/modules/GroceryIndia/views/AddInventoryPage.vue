<template>
  <div class="add-inventory-page">
    <div class="page-content">
      <div class="page-header">
        <div class="breadcrumb-section">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <router-link to="sell">{{ $t("common.Home") }}</router-link>
              </li>
              <li class="breadcrumb-item active">{{ $t("common.Inventory") }}</li>
            </ol>
          </nav>
          <h1 class="page-title">{{ $t("common.Add Inventory") }}</h1>
        </div>

        <!-- Desktop Only -->
        <div class="header-actions">
          <InfoTooltip
            :title="$t('common.upload_info_title')"
            :message="$t('common.upload_info_message')"
            placement="left"
            trigger="hover"
          >
            <button
              class="btn btn-icon-info"
              type="button"
              :aria-label="$t('common.Information')"
              @click="handleHowToUpload"
            >
              <i class="bi bi-info-circle"></i>
            </button>
          </InfoTooltip>

          <InfoTooltip
            :title="$t('common.upload_title')"
            :message="$t('common.upload_message')"
            placement="bottom"
            trigger="both"
          >
            <button class="btn btn-outline-primary" @click="handleUploadData">
              <i class="bi bi-upload"></i>
              <span class="btn-text">{{ $t("inventory_page.Upload Data") }}</span>
            </button>
          </InfoTooltip>

          <InfoTooltip
            :title="$t('common.download_title')"
            :message="$t('common.download_message')"
            placement="bottom"
            trigger="both"
          >
            <button class="btn btn-primary" @click="handleDownloadData">
              <i class="bi bi-download"></i>
              <span class="btn-text">{{ $t("inventory_page.Download Data") }}</span>
            </button>
          </InfoTooltip>
        </div>

        <div class="header-actions">
          <a class="btn btn-success" href="Inventory" rel="noopener">
            <i class="bi bi-pencil-square"></i>
            {{ $t("common.Inventory") }}
          </a>
        </div>
      </div>

      <div class="inventory-card">
        <InventoryForm @submit="handleSubmit" @cancel="handleCancel" />
      </div>

      <!-- Mobile Only: Data Management Section -->
      <div class="data-management-section mobile-only">
        <div class="section-header">
          <h5 class="section-title">{{ $t("common.Data Management") }}</h5>
          <InfoTooltip
            :title="$t('common.upload_info_title')"
            :message="$t('common.upload_info_message')"
            placement="left"
            trigger="click"
          >
            <button
              class="btn-info-mobile"
              type="button"
              :aria-label="$t('common.Information')"
              @click="handleHowToUpload"
            >
              <i class="bi bi-info-circle"></i>
            </button>
          </InfoTooltip>
        </div>

        <!-- Upload + Download row -->
        <div class="management-actions">
          <InfoTooltip
            :title="$t('common.upload_title')"
            :message="$t('common.upload_message')"
            placement="top"
            trigger="click"
          >
            <button class="btn btn-outline-primary action-btn" @click="handleUploadData">
              <i class="bi bi-upload"></i>
              {{ $t("inventory_page.Upload Data") }}
            </button>
          </InfoTooltip>

          <InfoTooltip
            :title="$t('common.download_title')"
            :message="$t('common.download_message')"
            placement="top"
            trigger="click"
          >
            <button
              class="btn btn-outline-primary action-btn"
              @click="handleDownloadData"
            >
              <i class="bi bi-download"></i>
              {{ $t("inventory_page.Download Data") }}
            </button>
          </InfoTooltip>
        </div>

        <!-- Inventory button row -->
        <div class="management-actions mt-2">
          <a class="btn btn-success action-btn" href="Inventory" rel="noopener">
            <i class="bi bi-pencil-square"></i>
            {{ $t("common.Inventory") }}
          </a>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import InventoryForm from "../components/InventoryForm.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";
import { useInventoryStore } from "@/modules/GroceryIndia/stores/inventory";
import { useI18n } from "vue-i18n";
import InfoTooltip from "@/modules/core/components/InfoTooltip.vue";

const { t } = useI18n();
const inventoryStore = useInventoryStore();
const router = useRouter();
const isDownloading = ref(false);

const handleSubmit = (formData) => console.log("Form submitted:", formData);
const handleCancel = () => console.log("Form cancelled");
const handleHowToUpload = () => router.push({ name: "DataUploadInstruction" });
const handleUploadData = () => router.push({ name: "UploadData" });

const handleDownloadData = async () => {
  if (isDownloading.value) return;
  isDownloading.value = true;
  try {
    const result = await inventoryStore.exportData();
    if (!result.success) {
      // toast.error(inventoryStore.errors.join(', ') || 'Export failed')
    }
  } catch (error) {
    console.error("Download error:", error);
  } finally {
    isDownloading.value = false;
  }
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

/* ── Page Header ── */
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

/* ── Header Actions ── */
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

/* ── Inventory Card ── */
.inventory-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

/* ── Mobile Data Management ── */
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

/* ── Management Actions ──
   KEY FIX: overflow visible so tooltip isn't clipped
   flex-wrap so buttons stack naturally on narrow screens         */
.management-actions {
  display: flex;
  gap: 12px;
  flex-direction: row;
  flex-wrap: wrap; /* ← wrap on very small screens */
  overflow: visible; /* ← tooltip must not be clipped */
  position: relative; /* ← stacking context for tooltip z-index */
}

/* action-btn fills available space equally */
.action-btn {
  flex: 1;
  min-width: 120px; /* ← prevents buttons from being too tiny */
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  white-space: nowrap;
}

/* ── Responsive ── */
@media (min-width: 769px) {
  .header-actions {
    display: flex;
  }
  .data-management-section.mobile-only {
    display: none;
  }
}

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
    gap: 8px;
  }

  .action-btn {
    padding: 10px 12px;
    font-size: 13px;
    gap: 6px;
    min-width: 100px;
  }
  .action-btn i {
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
