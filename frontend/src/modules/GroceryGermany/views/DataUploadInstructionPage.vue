<template>
  <div class="upload-instructions">
    <!-- Page Title -->
    <div class="pagetitle">
      <h1 v-html="$t('upload_instruction_page.title')"></h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="sell">{{ $t("common.Home") }}</a>
          </li>
          <li
            class="breadcrumb-item active"
            v-html="$t('upload_instruction_page.title')"
          ></li>
        </ol>
      </nav>
    </div>

    <!-- Main Section -->
    <section class="section pb-4">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body mt-4">
              <!-- Header -->
              <div class="row mb-4">
                <div class="col-12" @click="handleDelegatedClick">
                  <h4 class="m-0" v-html="$t('upload_instruction_page.subtitle')"></h4>
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h3 v-html="$t('upload_instruction_page.section1.heading')"></h3>
                </div>
              </div>

              <!-- Section 1 Content -->
              <div class="row">
                <div class="col-12">
                  <h5 v-html="$t('upload_instruction_page.section1.sub_heading')"></h5>
                </div>

                <div class="col-12 mb-4">
                  <p
                    class="text-break"
                    v-html="$t('upload_instruction_page.section1.para.1')"
                  ></p>
                  <p
                    class="text-break"
                    v-html="$t('upload_instruction_page.section1.para.2')"
                  ></p>

                  <!-- Main Columns Table -->
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th
                            v-for="(col, index) in mainColumns"
                            :key="index"
                            scope="col"
                          >
                            {{ col.toUpperCase() }}
                          </th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>

                <!-- Instructions List -->
                <div class="col-12 mb-4">
                  <ul>
                    <li v-html="$t('upload_instruction_page.section1.para.3')"></li>
                    <li v-html="$t('upload_instruction_page.section1.para.4')"></li>
                    <li v-html="$t('upload_instruction_page.section1.para.5')"></li>
                    <li v-html="$t('upload_instruction_page.section1.para.6')"></li>
                    <li v-html="$t('upload_instruction_page.section1.para.7')"></li>
                    <li v-html="$t('upload_instruction_page.section1.para.8')"></li>
                  </ul>
                </div>

                <!-- Units Table -->
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">{{ $t("common.Units") }}</th>
                          <th scope="col">{{ $t("common.Short Form") }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="unit in unitsArray" :key="unit.value">
                          <td>{{ unit.fullUnit }}</td>
                          <td>{{ unit.shortUnit }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Additional Instructions -->
                <div class="col-12">
                  <ul>
                    <!-- <li
                      v-html="$t('upload_instruction_page.section1.para.9')"
                    ></li> -->
                    <!-- <li
                      v-html="$t('upload_instruction_page.section1.para.10')"
                    ></li> -->
                    <!-- <li
                      v-html="$t('upload_instruction_page.section1.para.11')"
                    ></li> -->

                    <li v-html="$t('upload_instruction_page.section1.para.18')"></li>
                  </ul>
                </div>

                <!-- <p
                  class="text-break"
                  v-html="$t('upload_instruction_page.section1.para.12')"
                ></p> -->

                <!-- Upload Format Image -->
                <div class="col-12 text-center mb-3">
                  <img
                    :src="uploadFormatImage"
                    class="img-thumbnail hover-image"
                    alt="Upload format example"
                    @click="openModal"
                    role="button"
                    tabindex="0"
                    @keypress.enter="openModal"
                  />
                </div>

                <div class="col-12">
                  <p
                    class="text-break"
                    v-html="$t('upload_instruction_page.section1.para.13')"
                  ></p>
                  <p
                    class="text-break"
                    v-html="$t('upload_instruction_page.section1.para.14')"
                  ></p>

                  <!-- Required Columns Table -->
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th
                            v-for="(col, index) in requiredColumns"
                            :key="index"
                            scope="col"
                          >
                            {{ col.name.toUpperCase() }}
                            <span v-if="col.required" class="text-danger"> *</span>
                          </th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>

                <div class="col-12 mb-3">
                  <p
                    class="text-break"
                    v-html="$t('upload_instruction_page.section1.para.15')"
                  ></p>
                  <p
                    class="text-break"
                    v-html="$t('upload_instruction_page.section1.para.16')"
                  ></p>
                </div>

                <!-- Section 2 -->
                <div class="col-12">
                  <h5 v-html="$t('upload_instruction_page.section2.heading')"></h5>
                  <p
                    class="text-break"
                    v-html="$t('upload_instruction_page.section2.para.1')"
                  ></p>

                  <ol>
                    <li v-html="$t('upload_instruction_page.section2.para.2')"></li>
                    <li v-html="$t('upload_instruction_page.section2.para.3')"></li>
                    <li v-html="$t('upload_instruction_page.section2.para.4')"></li>
                    <li v-html="$t('upload_instruction_page.section2.para.5')"></li>
                    <li v-html="$t('upload_instruction_page.section2.para.6')"></li>
                    <li v-html="$t('upload_instruction_page.section2.para.7')"></li>
                  </ol>

                  <p
                    class="text-break"
                    v-html="$t('upload_instruction_page.section2.para.8')"
                  ></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Image Modal -->
    <Teleport to="body">
      <div
        class="modal fade"
        :class="{ show: showModal }"
        :style="{ display: showModal ? 'block' : 'none' }"
        tabindex="-1"
        role="dialog"
        aria-labelledby="imageModalLabel"
        aria-hidden="true"
        @click.self="closeModal"
      >
        <div class="modal-dialog modal-dialog-centered modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <button
                type="button"
                class="btn-close"
                @click="closeModal"
                aria-label="Close"
              ></button>
            </div>
            <div class="modal-body">
              <img
                :src="modalImageSrc"
                id="modalImage"
                class="img-fluid"
                alt="Full size upload format"
              />
            </div>
          </div>
        </div>
      </div>
      <div v-if="showModal" class="modal-backdrop fade show" @click="closeModal"></div>
    </Teleport>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, watch, getCurrentInstance, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/config/api";
import { useConfigStore } from "@/stores/config";
import { storeToRefs } from "pinia";
import uploadFormatImage from "@/assets/images/germany-upload-format.png";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";

const { t } = useI18n();

// Modal state
const showModal = ref(false);
const modalImageSrc = ref("");

const loading = ref(false);
const errors = ref([]);
const successMessage = ref("");

const configStore = useConfigStore();

const { unitsArray, loading: configLoading } = storeToRefs(configStore);

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

// Image path
// const uploadFormatImage = '@/assets/images/upload-format.png';

// Main columns data
const mainColumns = computed(() => [
  t("common.Item Name"),
  t("common.Quantity"),
  t("common.Minimum Stock Alert"),
  t("common.MRP"),
  t("common.Sale Price"),
  t("common.Unit"),
  // t("common.HSN"),
  t("common.Barcode"),
  t("common.VAT"),
  // t("common.GST"),
  // t("common.CESS"),
]);

// Required columns with asterisk
const requiredColumns = computed(() => [
  { name: t("common.Item Name"), required: true },
  { name: t("common.Quantity"), required: false },
  { name: t("common.Minimum Stock Alert"), required: false },
  { name: t("common.MRP"), required: false },
  { name: t("common.Sale Price"), required: true },
  { name: t("common.Unit"), required: true },
  // { name: t("common.HSN"), required: false },
  { name: t("common.Barcode"), required: false },
  { name: t("common.VAT"), required: false },
  // { name: t("common.GST"), required: true },
  // { name: t("common.CESS"), required: true },
]);

// // Units data
// const units = ref([
//   { name: "Bag", short: "BAG" },
//   { name: "Bottle", short: "BTL" },
//   { name: "Box", short: "BOX" },
//   { name: "Bundle", short: "BDL" },
//   { name: "Can", short: "CAN" },
//   { name: "Cartoon", short: "CTN" },
//   { name: "Gram", short: "GM" },
//   { name: "Kilogram", short: "KG" },
//   { name: "Litre", short: "LTR" },
//   { name: "Meter", short: "MTR" },
//   { name: "Millimeter", short: "ML" },
//   { name: "Number", short: "NUM" },
//   { name: "Pack", short: "PCK" },
//   { name: "Pair", short: "PRS" },
//   { name: "Piece", short: "PCS" },
//   { name: "Roll", short: "ROL" },
//   { name: "Square Feet", short: "SQF" },
//   { name: "Square Meter", short: "SQM" },
// ]);

// Modal functions
const openModal = () => {
  modalImageSrc.value = uploadFormatImage;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

// Body class management for modal
watch(showModal, (newVal) => {
  if (newVal) {
    document.body.classList.add("modal-open");
  } else {
    document.body.classList.remove("modal-open");
  }
});

const handleDelegatedClick = (event) => {
  if (event.target.classList.contains("downloadDataset")) {
    downloadDataset();
  }
};

// // Download dataset function (if needed)
// const downloadDataset = async () => {
//   try {
//     const response = await fetch(
//       window.grocery_geramny_api_url + "download/sample-dataset",
//       {
//         method: "GET",
//         headers: {
//           Authorization: "Bearer " + localStorage.getItem("token"),
//         },
//       }
//     );

//     const data = await response.json();

//     if (data.status === 1) {
//       window.location.href = window.base_url + data.file;
//       // Show success toast notification
//     } else {
//       // Show error toast notification
//       console.error(data.message);
//     }
//   } catch (error) {
//     console.error("Download error:", error);
//   }
// };

const downloadDataset = async () => {
  loading.value = true;
  errors.value = [];

  const btn = document.querySelector(".downloadDataset");
  if (btn) btn.disabled = true;

  try {
    const { data } = await api.get(
      `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}download/sample-dataset`
    );

    if (data.status == 1 || data.status === "1") {
      successMessage.value = data.message || t("common.Export successful");

      const fileUrl = `${import.meta.env.VITE_MEDIA_URL}${data.file}`;
      window.location.href = fileUrl;

      return { success: true, message: data.message };
    } else {
      errors.value = [data.message || t("common.Export failed")];
      return { success: false, errors: errors.value };
    }
  } catch (error) {
    console.error("Download error:", error);

    if (error.response?.data?.message) {
      errors.value = [error.response.data.message];
    } else {
      errors.value = [t("common.Failed to export data. Please try again.")];
    }

    return { success: false, errors: errors.value };
  } finally {
    loading.value = false;
    if (btn) btn.disabled = false;
  }
};

// Load config data and preferences on mount
onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(false, countryName.toLowerCase()),
    // Switch to indian locale
    configStore.switchLocale(countryName.toLowerCase()),
  ]);
});

// Expose downloadDataset if you want to call it from parent or template
defineExpose({
  downloadDataset,
});
</script>

<style scoped>
/* Hover effect for thumbnail image */
.hover-image {
  width: 80%;
  max-width: 100%;
  transition: transform 0.3s ease;
  cursor: pointer;
}

.hover-image:hover {
  transform: scale(1.05);
}

/* Modal image styling */
#modalImage {
  max-width: 100%;
  max-height: 100vh;
  object-fit: contain;
}

/* Modal display */
.modal.show {
  opacity: 1;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .hover-image {
    width: 100%;
  }

  /* Improve table readability on mobile */
  .table-responsive table {
    font-size: 0.875rem;
  }
}

@media (max-width: 576px) {
  .pagetitle h1 {
    font-size: 1.5rem;
  }

  .card-body {
    padding: 1rem;
  }

  /* Stack tables better on small screens */
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}
</style>
