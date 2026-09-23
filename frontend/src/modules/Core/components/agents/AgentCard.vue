<template>
  <div class="card agent-card mb-3">
    <div class="row g-0">
      <!-- Image -->
      <div class="col-12 col-md-4 d-flex justify-content-center agent-image-col">
        <div class="square-box w-100">
          <img
            :src="agent.photo ?? fallbackImage"
            class="img-fluid w-100 h-100"
            :alt="agent.name"
            @error="onImageError"
          />
        </div>
      </div>

      <!-- Details -->
      <div class="col-12 col-md-8 agent-details-col">
        <div class="card-body">
          <div class="row agent-info">
            <div class="col-12 col-sm-7 text-sm-end text-start fw-bold label-col">
              ID:
            </div>
            <div class="col-12 col-sm-5 text-start value-col">
              {{ agent.user_id ?? "-" }}
            </div>

            <div class="col-12 col-sm-7 text-sm-end text-start fw-bold label-col">
              Name:
            </div>
            <div class="col-12 col-sm-5 text-start value-col">
              {{ agent.name ?? "-" }}
            </div>

            <div class="col-12 col-sm-7 text-sm-end text-start fw-bold label-col">
              Address:
            </div>
            <div class="col-12 col-sm-5 text-start value-col">
              {{ agent.address ?? "-" }}
            </div>

            <div class="col-12 col-sm-7 text-sm-end text-start fw-bold label-col">
              Mobile:
            </div>
            <div class="col-12 col-sm-5 text-start value-col">
              {{ agent.mobile ?? "-" }}
            </div>

            <div class="col-12 col-sm-7 text-sm-end text-start fw-bold label-col">
              Email:
            </div>
            <div class="col-12 col-sm-5 text-start value-col">
              {{ agent.email ?? "-" }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import fallbackImage from "@/assets/images/user-profile.jpg";

defineProps({
  agent: {
    type: Object,
    required: true,
  },
});

function onImageError(e) {
  e.target.src = fallbackImage;
}
</script>

<style scoped>
.agent-card {
  border: 1px solid rgba(0, 0, 0, 0.125);
  border-radius: 8px;
  overflow: hidden;
  background-color: #ffffff;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.agent-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}
.agent-image-col {
  padding: 30px;
  background-color: #f8f9fa;
}
.square-box {
  aspect-ratio: 1 / 1;
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 200px;
}
.square-box img {
  object-fit: contain;
}
.agent-details-col {
  padding: 30px;
}
.agent-info {
  font-size: 20px;
}
.label-col {
  color: #333;
  margin-bottom: 0.75rem;
}
.value-col {
  color: #555;
  margin-bottom: 0.75rem;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .agent-image-col {
    padding: 20px;
    background-color: transparent;
  }
  .square-box {
    max-width: 150px;
    margin: 0 auto;
  }
  .agent-details-col {
    padding: 20px;
  }
  .agent-info {
    font-size: 16px;
  }
  .label-col {
    font-weight: 600 !important;
    margin-bottom: 0.25rem;
  }
  .value-col {
    margin-bottom: 1rem;
  }
}
@media (max-width: 576px) {
  .agent-image-col {
    padding: 15px;
  }
  .square-box {
    max-width: 130px;
  }
  .agent-details-col {
    padding: 15px;
  }
  .agent-info {
    font-size: 14px;
  }
  .label-col {
    margin-bottom: 0.2rem;
  }
  .value-col {
    margin-bottom: 0.8rem;
  }
}
</style>
