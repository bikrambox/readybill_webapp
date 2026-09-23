<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { storeToRefs } from "pinia";
import { useEarningsStore } from "@/modules/AuthorizedAgents/stores/earningsStore";
import { useI18n } from "vue-i18n";

import EarningsTable from "../components/earnings/EarningsTable.vue";
import EarningsMobileList from "../components/earnings/EarningsMobileList.vue";
import EarningDetailModal from "../components/earnings/EarningDetailModal.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";

const { t } = useI18n();
const earningsStore = useEarningsStore();
const showDetailModal = ref(false);

const {
  items,
  loading,
  errors,
  successMessage,
  recordsTotal,
  recordsFiltered,
  currentPage,
  pageLength,
} = storeToRefs(earningsStore);

// ── Filters ──────────────────────────────────────────
const searchQuery = ref("");
const filterColumn = ref("shop_name");
const statusFilter = ref("all");

// ── Debounce ──────────────────────────────────────────
let searchTimer = null;
const debounce = (fn, delay = 400) => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(fn, delay);
};

onBeforeUnmount(() => clearTimeout(searchTimer));

// ── Lifecycle ─────────────────────────────────────────
onMounted(async () => {
  await earningsStore.fetchEarnings(0, 10, "", "shop_name");
});

// ── Search / Filter ───────────────────────────────────
const handleSearchUpdate = (value) => {
  searchQuery.value = value;
  debounce(() => performSearch());
};

const handleFilterColumnUpdate = (value) => {
  filterColumn.value = value;
  searchQuery.value = "";
  debounce(() => performSearch());
};

const handleStatusFilter = async (status) => {
  statusFilter.value = status;
  await earningsStore.performSearch(searchQuery.value, filterColumn.value, status);
};

const performSearch = async () => {
  await earningsStore.performSearch(searchQuery.value, filterColumn.value);
};

// ── Pagination ────────────────────────────────────────
const handlePageChange = async (page) => {
  await earningsStore.changePage(page);
};

const handlePageLengthChange = async (length) => {
  await earningsStore.changePageLength(length);
};

// ── Alerts ────────────────────────────────────────────
const clearErrors = () => earningsStore.clearErrors();
const clearSuccessMessage = () => earningsStore.clearSuccessMessage();

const openDetail = async (item) => {
  showDetailModal.value = true;
  await earningsStore.fetchCommissionDetail(item.id);
};

const closeDetail = () => {
  showDetailModal.value = false;
  earningsStore.clearCommissionDetail();
};
</script>

<template>
  <div class="earnings-page">
    <div class="container-fluid px-3 px-lg-4">
      <!-- Breadcrumb + Title -->
      <div class="row mb-3 mb-lg-4">
        <div class="col-12">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-2">
              <li class="breadcrumb-item">
                <a href="sell" class="text-decoration-none">{{ $t("common.Home") }}</a>
              </li>
              <li class="breadcrumb-item active">{{ $t("common.My Earnings") }}</li>
            </ol>
          </nav>
          <h1 class="h3 fw-bold mb-0">{{ $t("common.My Earnings") }}</h1>
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
        <strong>{{ $t("common.Success") }}:</strong> {{ successMessage }}
        <button type="button" class="btn-close" @click="clearSuccessMessage"></button>
      </div>

      <!-- ── Summary Cards ── -->
      <div class="row g-3 mb-4">
        <!-- Total Earnings (Paid) -->
        <div class="col-12 col-sm-6">
          <div class="summary-card summary-paid">
            <div class="summary-icon">
              <i class="bi bi-wallet2-fill"></i>
            </div>
            <div>
              <div class="summary-label">{{ $t("common.Total Earnings") }}</div>
              <div class="summary-value">
                {{ earningsStore.formattedTotalPaid }}
              </div>
            </div>
          </div>
        </div>

        <!-- Amount Pending (Unpaid) -->
        <div class="col-12 col-sm-6">
          <div class="summary-card summary-pending">
            <div class="summary-icon">
              <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
              <div class="summary-label">{{ $t("common.Amount Pending") }}</div>
              <div class="summary-value">
                {{ earningsStore.formattedTotalPending }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Filters Bar ── -->
      <div class="filters-bar mb-3">
        <!-- Search -->
        <div class="search-wrapper">
          <i class="bi bi-search search-icon"></i>
          <input
            type="text"
            class="form-control search-input"
            :placeholder="$t('common.Search by shop name or date')"
            :value="searchQuery"
            @input="handleSearchUpdate($event.target.value)"
          />
        </div>

        <!-- Column Select -->
        <select
          class="form-select filter-select"
          :value="filterColumn"
          @change="handleFilterColumnUpdate($event.target.value)"
        >
          <option value="shop_name">{{ $t("common.Shop Name") }}</option>
          <option value="date">{{ $t("common.Date") }}</option>
        </select>
      </div>

      <!-- ── Desktop Table ── -->
      <div class="d-none d-lg-block">
        <EarningsTable
          :key="`table-${recordsFiltered}-${currentPage}`"
          :data="items"
          :current-page="currentPage"
          :page-length="pageLength"
          :records-total="recordsTotal"
          :records-filtered="recordsFiltered"
          :loading="loading"
          @page-change="handlePageChange"
          @page-length-change="handlePageLengthChange"
          @row-click="openDetail"
        />
      </div>

      <!-- ── Mobile List ── -->
      <div class="d-lg-none">
        <EarningsMobileList
          :key="`mobile-${recordsFiltered}-${currentPage}`"
          :display-data="items"
          :loading="loading"
          :page-info="{
            start: currentPage * pageLength + 1,
            end: Math.min((currentPage + 1) * pageLength, recordsFiltered),
            total: recordsFiltered,
            hasPrev: currentPage > 0,
            hasNext: currentPage < Math.ceil(recordsFiltered / pageLength) - 1,
          }"
          @prev-page="handlePageChange(currentPage - 1)"
          @next-page="handlePageChange(currentPage + 1)"
          @card-click="openDetail"
        />
      </div>
    </div>

    <!-- Detail Modal -->
    <EarningDetailModal
      :show="showDetailModal"
      :detail="earningsStore.selectedCommission"
      :loading="earningsStore.detailLoading"
      :error="earningsStore.detailError"
      @close="closeDetail"
    />

    <Footer />
  </div>
</template>

<style scoped>
.earnings-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f8f9fa;
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

/* ── Alerts ── */
.alert {
  border-radius: 8px;
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  animation: slideInDown 0.3s ease-out;
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

/* ── Summary Cards ── */
.summary-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 20px;
  border-radius: 12px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
  background: white;
}
.summary-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}
.summary-label {
  font-size: 12px;
  color: #6c757d;
  margin-bottom: 2px;
}
.summary-value {
  font-size: 18px;
  font-weight: 700;
  color: #212529;
}

/* Total Earnings — green */
.summary-paid .summary-icon {
  background-color: #d1e7dd;
  color: #198754;
}

/* Amount Pending — amber */
.summary-pending .summary-icon {
  background-color: #fff3cd;
  color: #e6a817;
}

/* ── Filters Bar ── */
.filters-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}
.search-wrapper {
  position: relative;
  flex: 1;
  min-width: 200px;
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 14px;
}
.search-input {
  padding-left: 36px;
  border-radius: 8px;
  height: 38px;
  font-size: 14px;
}
.filter-select {
  width: auto;
  min-width: 140px;
  height: 38px;
  font-size: 14px;
  border-radius: 8px;
}

@media (max-width: 991px) {
  .container-fluid {
    padding-top: 1rem;
  }
  .filters-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .filter-select {
    width: 100%;
  }
}
</style>
