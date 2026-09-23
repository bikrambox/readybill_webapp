<template>
  <div
    class="error-page-wrapper position-fixed top-0 start-0 end-0 bottom-0 d-flex align-items-center justify-content-center"
  >
    <div class="container py-4">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
          <div class="card border-0 shadow-lg error-card">
            <div class="card-body p-4 p-sm-5 text-center">
              <div class="mb-4 error-icon">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
              </div>

              <h1 class="h2 fw-bold text-dark mb-3">
                {{ errorTitle }}
              </h1>

              <p class="text-muted fs-6 mb-4 px-2">
                {{ errorMessage }}
              </p>

              <div
                v-if="instructionList.length"
                class="alert alert-light border mb-4 text-start"
              >
                <h6 class="fw-semibold mb-3">
                  <i class="bi bi-lightbulb text-warning me-2"></i>
                  What you can do:
                </h6>

                <ul class="mb-0 ps-3 small text-muted">
                  <li
                    v-for="(item, index) in instructionList"
                    :key="index"
                    :class="{ 'mb-2': index !== instructionList.length - 1 }"
                  >
                    {{ item }}
                  </li>
                </ul>
              </div>

              <div
                class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4"
              >
                <button
                  v-if="showRetry"
                  class="btn btn-primary btn-lg rounded-pill px-4"
                  @click="handleRetry"
                >
                  <i class="bi bi-arrow-clockwise me-2"></i>Refresh Page
                </button>

                <a
                  :href="homeUrl"
                  class="btn btn-outline-primary btn-lg rounded-pill px-4"
                >
                  <i class="bi bi-house-door me-2"></i>Go to Home
                </a>
              </div>

              <div class="border-top pt-4 mt-2">
                <p class="text-muted small mb-2">
                  <i class="bi bi-headset me-1"></i>
                  Need help?
                  <a :href="contactUrl" class="text-decoration-none fw-semibold">
                    Contact Our Support Team
                  </a>
                </p>
                <!-- <p class="text-secondary small mb-0">Reference: {{ errorId }}</p> -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="error-bg-shape error-bg-shape-1"></div>
    <div class="error-bg-shape error-bg-shape-2"></div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  errorCode: {
    type: [String, Number],
    default: "ERROR",
  },
  errorTitle: {
    type: String,
    default: "Something Went Wrong",
  },
  errorMessage: {
    type: String,
    default: "An unexpected error occurred.",
  },
  instructions: {
    type: [Array, String],
    default: () => [],
  },
  showRetry: {
    type: Boolean,
    default: true,
  },
  homeUrl: {
    type: String,
    default: "/",
  },
  onRetry: {
    type: Function,
    default: null,
  },
});

const emit = defineEmits(["retry"]);

const errorId = computed(() => {
  const timestamp = Date.now();
  return `ERR-${timestamp.toString().slice(-8)}`;
});

const instructionList = computed(() => {
  if (Array.isArray(props.instructions)) {
    return props.instructions.filter((item) => item && String(item).trim() !== "");
  }

  if (typeof props.instructions === "string" && props.instructions.trim()) {
    return [props.instructions.trim()];
  }

  return [];
});

const contactUrl = computed(() => {
  return "/in/en/contact";
});

const handleRetry = () => {
  emit("retry");

  if (typeof props.onRetry === "function") {
    props.onRetry();
    return;
  }

  window.location.reload();
};
</script>

<style scoped>
.error-page-wrapper {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  z-index: 9999;
  overflow-y: auto;
  animation: fadeIn 0.3s ease-out;
  position: relative;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.error-card {
  border-radius: 1.25rem;
  animation: slideUp 0.5s ease-out;
  position: relative;
  z-index: 2;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.error-icon i {
  font-size: 5rem;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.08);
    opacity: 0.85;
  }
}

.btn {
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
}

.error-bg-shape {
  position: absolute;
  border-radius: 50%;
  filter: blur(10px);
  opacity: 0.15;
  z-index: 1;
}

.error-bg-shape-1 {
  width: 180px;
  height: 180px;
  background: #ffffff;
  top: 10%;
  left: 8%;
}

.error-bg-shape-2 {
  width: 220px;
  height: 220px;
  background: #ffd6e7;
  bottom: 8%;
  right: 8%;
}

@media (max-width: 575.98px) {
  .error-icon i {
    font-size: 3.5rem;
  }

  .error-card {
    border-radius: 1rem;
  }
}

@media (max-width: 375px) {
  .error-icon i {
    font-size: 3rem;
  }

  .alert ul {
    font-size: 0.875rem;
  }
}

@media (max-height: 600px) and (orientation: landscape) {
  .error-page-wrapper {
    padding: 1rem 0;
  }

  .error-card .card-body {
    padding: 1.5rem !important;
  }

  .error-icon i {
    font-size: 3rem;
  }

  .mb-4 {
    margin-bottom: 1rem !important;
  }
}
</style>
