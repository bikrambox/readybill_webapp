<template>
  <div class="quick-sell-page">
    <div class="page-content">
      <!-- Page Header -->
      <div class="page-header">
        <div class="breadcrumb-section">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="sell">{{ $t("common.Home") }}</a>
              </li>
              <li class="breadcrumb-item active">
                {{ $t("common.Dashboard") }}
              </li>
            </ol>
          </nav>
          <h1 class="page-title">{{ $t("common.Dashboard") }}</h1>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import Footer from "@/modules/AuthorizedAgents/components/Footer.vue";
</script>

<style scoped>
.quick-sell-page {
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
  margin-bottom: 25px;
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

.mb-3 {
  margin-bottom: 1rem;
}

.item-list-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.list-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.total-display {
  font-size: 16px;
  font-weight: 500;
  color: #333;
}

.total-amount {
  font-size: 20px;
  font-weight: 700;
  color: #0066cc;
  margin-left: 8px;
}

.total-negative {
  color: #dc3545;
}

.desktop-table {
  display: block;
}

.mobile-list {
  display: none;
}

@media (max-width: 768px) {
  .page-title {
    font-size: 24px;
  }

  .item-list-card {
    padding: 20px;
  }

  .list-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .list-title {
    font-size: 16px;
  }

  .desktop-table {
    display: none;
  }

  .mobile-list {
    display: block;
  }
}

@media (max-width: 480px) {
  .item-list-card {
    padding: 15px;
  }
}
</style>
