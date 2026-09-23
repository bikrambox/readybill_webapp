<template>
  <div
    class="error-page-wrapper position-fixed top-0 start-0 end-0 bottom-0 d-flex align-items-center justify-content-center"
  >
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
          <div class="card border-0 shadow-lg error-card">
            <div class="card-body p-4 p-sm-5 text-center">
              <!-- Error Icon -->
              <div class="mb-4 error-icon">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
              </div>

              <!-- Error Title -->
              <h1 class="h2 fw-bold text-dark mb-3">
                {{ userFriendlyTitle }}
              </h1>

              <!-- Error Message -->
              <p class="text-muted fs-6 mb-4 px-2">
                {{ userFriendlyMessage }}
              </p>

              <!-- Helpful Tips -->
              <div class="alert alert-light border mb-4 text-start">
                <h6 class="fw-semibold mb-3">
                  <i class="bi bi-lightbulb text-warning me-2"></i>What you can do:
                </h6>
                <ul class="mb-0 ps-3 small text-muted">
                  <li class="mb-2">Check your internet connection</li>
                  <li class="mb-2">Try refreshing the page</li>
                  <li class="mb-2">Clear your browser cache</li>
                  <li>Contact support if the problem continues</li>
                </ul>
              </div>

              <!-- Action Buttons -->
              <div
                class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4"
              >
                <button
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

              <!-- Footer -->
              <div class="border-top pt-4 mt-2">
                <p class="text-muted small mb-2">
                  <i class="bi bi-headset me-1"></i>
                  Need help?
                  <a href="/contact" class="text-decoration-none fw-semibold"
                    >Contact Our Support Team</a
                  >
                </p>
                <p class="text-secondary small mb-0">Reference: {{ errorId }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

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
    default:
      "We encountered an unexpected error. Please try again or contact support if the problem persists.",
  },
  errorDetails: {
    type: String,
    default: null,
  },
  showRetry: {
    type: Boolean,
    default: true,
  },
  homeUrl: {
    type: String,
    default: "/",
  },
});

const emit = defineEmits(["retry"]);

const errorId = computed(() => {
  const timestamp = Date.now();
  return `ERR-${timestamp.toString().slice(-8)}`;
});

// Convert technical errors to user-friendly messages
const userFriendlyTitle = computed(() => {
  const code = String(props.errorCode).toLowerCase();

  if (code.includes("timeout")) {
    return "Taking Longer Than Expected";
  } else if (code.includes("network") || code.includes("connection")) {
    return "Connection Issue";
  } else if (code.includes("init") || code.includes("load")) {
    return "Unable to Load";
  } else if (code.includes("404") || code.includes("not_found")) {
    return "Page Not Found";
  } else if (code.includes("500") || code.includes("server")) {
    return "Server Issue";
  }

  return "Oops! Something Went Wrong";
});

const userFriendlyMessage = computed(() => {
  const code = String(props.errorCode).toLowerCase();

  if (code.includes("timeout")) {
    return "The page is taking too long to load. This might be due to a slow internet connection or the server being busy. Please try again.";
  } else if (code.includes("network") || code.includes("connection")) {
    return "We're having trouble connecting to the server. Please check your internet connection and try again.";
  } else if (code.includes("init") || code.includes("load")) {
    return "We couldn't start the application properly. This is usually a temporary issue. Please refresh the page to try again.";
  } else if (code.includes("404") || code.includes("not_found")) {
    return "The page you're looking for doesn't exist or has been moved. Please check the URL or return to the home page.";
  } else if (code.includes("500") || code.includes("server")) {
    return "Our server is experiencing issues right now. Our team has been notified and is working on it. Please try again in a few moments.";
  }

  return "Something unexpected happened. Don't worry, this is usually temporary. Please try refreshing the page or come back in a few minutes.";
});

const handleRetry = () => {
  emit("retry");
  window.location.reload();
};
</script>

<style scoped>
.error-page-wrapper {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  z-index: 9999;
  overflow-y: auto;
  animation: fadeIn 0.3s ease-out;
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
    transform: scale(1.1);
    opacity: 0.8;
  }
}

.btn {
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
}

/* Responsive adjustments */
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

/* Landscape mobile optimization */
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
