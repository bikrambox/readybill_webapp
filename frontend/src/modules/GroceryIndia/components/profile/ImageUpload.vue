<template>
  <div>
    <div v-if="previewUrl" class="position-relative d-inline-block mb-2">
      <img
        :src="previewUrl"
        class="img-fluid"
        alt="Preview"
        style="max-width: 200px; max-height: 100px; object-fit: contain"
      />
      <button
        type="button"
        class="btn btn-sm btn-danger position-absolute top-0 end-0"
        @click="handleDelete"
        style="transform: translate(25%, -25%)"
      >
        <i class="bi bi-x"></i>
      </button>
    </div>

    <input
      ref="fileInput"
      type="file"
      class="form-control"
      :class="{ 'border-danger': error }"
      accept="image/*"
      @change="handleFileChange"
    />

    <span v-if="error" class="text-danger d-block mt-1">{{ error }}</span>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  file: {
    type: File,
    default: null,
  },
  preview: {
    type: String,
    default: "",
  },
  error: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:file", "update:preview", "delete"]);

const fileInput = ref(null);
const localPreview = ref(props.preview);

const previewUrl = computed(() => localPreview.value);

watch(
  () => props.preview,
  (newVal) => {
    localPreview.value = newVal;
  }
);

const handleFileChange = (event) => {
  const file = event.target.files[0];

  if (!file) return;

  // Validate file
  const validTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
  const maxSize = 2 * 1024 * 1024; // 2MB

  if (!validTypes.includes(file.type)) {
    alert("Please upload a valid image file (JPEG, PNG, GIF)");
    return;
  }

  if (file.size > maxSize) {
    alert("File size must be less than 2MB");
    return;
  }

  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    localPreview.value = e.target.result;
    emit("update:preview", e.target.result);
  };
  reader.readAsDataURL(file);

  emit("update:file", file);
};

const handleDelete = () => {
  localPreview.value = "";
  if (fileInput.value) {
    fileInput.value.value = "";
  }
  emit("update:file", null);
  emit("update:preview", "");
  emit("delete");
};
</script>
