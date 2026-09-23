<template>
  <div class="add-inventory-page">
    <div class="page-content">

      <!-- Page Header -->
      <div class="page-header">
        <div class="breadcrumb-section">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <router-link to="sell">{{ $t('common.Home') }}</router-link>
              </li>
              <li class="breadcrumb-item active">{{ $t('common.Scan Product') }}</li>
            </ol>
          </nav>
          <h1 class="page-title">{{ $t('common.Scan Product') }}</h1>
        </div>
      </div>

      <!-- Main Card -->
      <div class="inventory-card">

        <!-- Error Alert -->
        <div v-if="errorMsg" class="alert alert-danger d-flex align-items-start gap-2">
          <i class="bi bi-exclamation-triangle-fill mt-1"></i>
          <div>
            {{ errorMsg }}
            <div class="mt-2">
              <button class="btn btn-sm btn-outline-danger" @click="handleRetry">
                <i class="bi bi-arrow-clockwise me-1"></i>{{ $t('common.Try Again') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Step 1: Idle -->
        <div v-if="state === 'idle'" class="text-center py-5">
          <i class="bi bi-upc-scan text-primary" style="font-size: 56px;"></i>
          <h5 class="mt-3 mb-2">{{ $t('common.Scan a Product Barcode') }}</h5>
          <p class="text-muted small mb-4">
            {{ $t('common.Point your camera at a product barcode to fetch its details') }}
          </p>

          <div class="d-flex justify-content-center gap-2 flex-wrap">
            <!-- Live camera -->
            <button class="btn btn-primary px-4" @click="startScanner">
              <i class="bi bi-camera-video-fill me-2"></i>
              {{ $t('common.Open Scanner') }}
            </button>

            <!-- Scan from photo -->
            <label class="btn btn-outline-primary px-4 mb-0" style="cursor: pointer;">
              <i class="bi bi-image me-2"></i>
              {{ $t('common.Scan from Photo') }}
              <input
                type="file"
                accept="image/*"
                class="d-none"
                ref="fileInputRef"
                @change="scanFromPhoto"
              />
            </label>
          </div>
        </div>

        <!-- Step 2: Scanning (live camera) -->
        <div v-if="state === 'scanning'">

          <!-- html5-qrcode mounts itself here -->
          <div id="qr-reader" class="mx-auto" style="max-width: 480px;"></div>

          <!-- Scanned barcode shown immediately on read -->
          <div v-if="scannedBarcode" class="text-center mt-3">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
              <i class="bi bi-upc-scan me-1"></i>
              <code>{{ scannedBarcode }}</code>
            </span>
          </div>

          <div class="text-center mt-3">
            <button class="btn btn-outline-secondary" @click="stopScanner">
              <i class="bi bi-x-circle me-1"></i>{{ $t('common.Cancel') }}
            </button>
          </div>
        </div>

        <!-- Step 3: Loading -->
        <div v-if="state === 'loading'" class="text-center py-5">
          <div class="spinner-border text-primary mb-3" role="status"></div>
          <p class="fw-medium mb-1">{{ $t('common.Loading') }}...</p>
          <p class="text-muted small">
            {{ $t('common.Fetching details for barcode') }}:
            <code>{{ scannedBarcode }}</code>
          </p>
        </div>

        <!-- Step 4: Result -->
        <div v-if="state === 'result' && product">

          <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
              <i class="bi bi-check-circle-fill me-1"></i>
              {{ $t('common.Product Found') }}
            </span>
            <code class="text-muted small">{{ scannedBarcode }}</code>
          </div>

          <h5 class="fw-bold mb-3">{{ product.item_name }}</h5>

          <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
              <div class="bg-light rounded p-3">
                <div class="text-muted info-label">{{ $t('inventory_page.MRP') }}</div>
                <div class="fw-semibold mt-1">{{ currency }}{{ product.mrp ?? '—' }}</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-light rounded p-3">
                <div class="text-muted info-label">{{ $t('inventory_page.Rate') }}</div>
                <div class="fw-semibold mt-1">{{ currency }}{{ product.rate ?? '—' }}</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-light rounded p-3">
                <div class="text-muted info-label">{{ $t('inventory_page.Stock') }}</div>
                <div class="fw-semibold mt-1">{{ product.stock_quantity ?? '—' }} {{ product.short_unit }}</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-light rounded p-3">
                <div class="text-muted info-label">{{ $t('inventory_page.Unit') }}</div>
                <div class="fw-semibold mt-1">{{ product.full_unit ?? '—' }}</div>
              </div>
            </div>
          </div>

          <div v-if="product.tax_rates?.length" class="mb-3">
            <div class="text-muted mb-2 info-label">{{ $t('inventory_page.Tax') }}</div>
            <div class="d-flex flex-wrap gap-2">
              <span v-for="(t, i) in product.tax_rates" :key="i" class="badge bg-light text-dark border">
                {{ t.tax }} — {{ t.rate }}%
              </span>
            </div>
          </div>

          <div v-if="product.hsn_code" class="mb-3">
            <span class="text-muted small fw-semibold">{{ $t('inventory_page.HSN/ SAC Code') }}: </span>
            <code>{{ product.hsn_code }}</code>
          </div>

          <div class="d-flex gap-2 flex-wrap mt-4">
            <button class="btn btn-primary" @click="$emit('view', product)">
              <i class="bi bi-eye me-1"></i>{{ $t('inventory_page.View Details') }}
            </button>
            <button class="btn btn-outline-secondary" @click="handleRetry">
              <i class="bi bi-upc-scan me-1"></i>{{ $t('inventory_page.Scan Again') }}
            </button>
          </div>
        </div>

      </div><!-- /inventory-card -->

      <!-- Hidden div required by html5-qrcode scanFile() -->
      <div id="qr-reader-offscreen" style="display: none;"></div>

    </div><!-- /page-content -->

    <Footer />
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount, nextTick, getCurrentInstance } from "vue";
import { Html5Qrcode } from "html5-qrcode";
import { useInventoryStore } from "@/modules/GroceryIndia/stores/inventory";


const inventoryStore = useInventoryStore();
const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;

// ── State machine: idle | scanning | loading | result ─────────────────────────
const state          = ref("idle");
const scannedBarcode = ref("");
const product        = ref(null);
const errorMsg       = ref("");
const fileInputRef   = ref(null);

let html5QrCode = null;

onBeforeUnmount(() => stopScanner());

// ── Start live camera scanner ─────────────────────────────────────────────────
const startScanner = async () => {
  errorMsg.value       = "";
  product.value        = null;
  scannedBarcode.value = "";
  state.value          = "scanning";

  await nextTick();

  try {
    html5QrCode = new Html5Qrcode("qr-reader");

    await html5QrCode.start(
      { facingMode: "environment" },
      {
        fps: 10,
        qrbox: { width: 280, height: 160 },
        aspectRatio: 1.7,
        supportedScanTypes: [],
      },
      async (decodedText) => {
        scannedBarcode.value = decodedText;
        await stopScanner();
        await fetchProduct(decodedText);
      },
      () => {
        // Per-frame errors — ignore silently
      }
    );
  } catch (e) {
    state.value    = "idle";
    errorMsg.value = "Could not start camera: " + (e?.message ?? e);
  }
};

// ── Stop live camera scanner ──────────────────────────────────────────────────
const stopScanner = async () => {
  if (html5QrCode) {
    try {
      const scanState = html5QrCode.getState();
      if (scanState === 2 || scanState === 3) {
        await html5QrCode.stop();
      }
    } catch (e) {
      console.warn("html5-qrcode stop error:", e);
    }
    html5QrCode = null;
  }
  if (state.value === "scanning") {
    state.value = "idle";
  }
};

// ── Scan from uploaded photo ──────────────────────────────────────────────────
const scanFromPhoto = async (event) => {
  const file = event.target.files?.[0];

  // Reset file input so same file can be re-selected if needed
  if (fileInputRef.value) fileInputRef.value.value = "";

  if (!file) return;

  errorMsg.value       = "";
  product.value        = null;
  scannedBarcode.value = "";
  state.value          = "loading";

  try {
    const scanner = new Html5Qrcode("qr-reader-offscreen");
    const result  = await scanner.scanFile(file, /* showImage */ false);
    scannedBarcode.value = result;
    await fetchProduct(result);
  } catch (e) {
    errorMsg.value = "No barcode found in this photo. Try a clearer, closer image.";
    state.value    = "idle";
  }
};

// ── Fetch product from API ────────────────────────────────────────────────────
const fetchProduct = async (barcode) => {

  console.log('fetchProduct',barcode);

  state.value = "loading";
  try {
    const result = await inventoryStore.getProductByBarcode(barcode);
     
    console.log('fetchProduct',result);

    if (result.success) {
      product.value = result.data;
      state.value   = "result";
    } else {
      errorMsg.value = result.message ?? "No product found for this barcode.";
      state.value    = "idle";
    }
  } catch (e) {
    errorMsg.value = "Failed to fetch product details.";
    state.value    = "idle";
  }
};

// ── Retry ─────────────────────────────────────────────────────────────────────
const handleRetry = () => {
  product.value        = null;
  errorMsg.value       = "";
  scannedBarcode.value = "";
  state.value          = "idle";
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
}
.breadcrumb {
  margin-bottom: 10px;
  background: transparent;
  padding: 0;
  font-size: 14px;
}
.breadcrumb-item a { color: #6c757d; text-decoration: none; }
.breadcrumb-item.active { color: #333; }
.page-title { font-size: 28px; font-weight: 600; color: #333; margin: 0; }
.inventory-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}
.info-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .5px;
}

/* Clean up html5-qrcode default UI */
#qr-reader {
  border: none !important;
}
#qr-reader__scan_region {
  border-radius: 8px;
  overflow: hidden;
}
#qr-reader__dashboard {
  padding: 8px 0 0 0 !important;
}
#qr-reader__dashboard_section_csr button {
  background: #0d6efd;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 6px 16px;
  cursor: pointer;
}

@media (max-width: 768px) {
  .inventory-card { padding: 20px; }
  .page-title { font-size: 22px; }
}
</style>
