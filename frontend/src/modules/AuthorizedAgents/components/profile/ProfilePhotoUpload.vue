<template>
  <div
    class="upload-zone"
    :class="{ 'has-file': !!preview, 'drag-over': isDragOver }"
    @dragover.prevent="isDragOver = true"
    @dragleave="isDragOver = false"
    @drop.prevent="onDrop"
    @click="triggerInput"
  >
    <input
      ref="inputRef"
      type="file"
      :accept="accept"
      class="d-none"
      @change="onFileChange"
    />

    <!-- Preview state -->
    <div v-if="preview" class="preview-state">
      <img :src="preview" alt="preview" class="preview-img" />
      <button type="button" class="remove-btn" @click.stop="removeFile">
        <i class="bi bi-x"></i>
      </button>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <div class="upload-icon">
        <i :class="`bi ${icon}`"></i>
      </div>
      <div class="upload-label">{{ label }}</div>
      <div class="upload-hint">{{ hint || "Click or drag to upload" }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
  label: { type: String, required: true },
  icon: { type: String, default: "bi-upload" },
  accept: { type: String, default: "image/*" },
  preview: { type: String, default: null },
  hint: { type: String, default: null },
});
const emit = defineEmits(["fileSelected"]);

const inputRef = ref(null);
const isDragOver = ref(false);

const triggerInput = () => inputRef.value?.click();

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) emit("fileSelected", file);
};

const onDrop = (e) => {
  isDragOver.value = false;
  const file = e.dataTransfer.files[0];
  if (file) emit("fileSelected", file);
};

const removeFile = () => {
  if (inputRef.value) inputRef.value.value = "";
  emit("fileSelected", null);
};
</script>

<style scoped>
.upload-zone {
  border: 2px dashed #dee2e6;
  border-radius: 12px;
  background: #fafafa;
  cursor: pointer;
  transition: all 0.2s ease;
  aspect-ratio: 1 / 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

.upload-zone:hover,
.upload-zone.drag-over {
  border-color: #0d6efd;
  background: #f0f5ff;
}

.upload-zone.has-file {
  border-style: solid;
  border-color: #86b7fe;
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 12px;
}

.upload-icon {
  font-size: 2rem;
  color: #ced4da;
  margin-bottom: 8px;
  line-height: 1;
}

.upload-zone:hover .upload-icon,
.upload-zone.drag-over .upload-icon {
  color: #0d6efd;
}

.upload-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 3px;
}

.upload-hint {
  font-size: 0.68rem;
  color: #adb5bd;
}

/* Preview state */
.preview-state {
  width: 100%;
  height: 100%;
  position: relative;
}

.preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.remove-btn {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  border: none;
  background: rgba(0, 0, 0, 0.55);
  color: #fff;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
}

.remove-btn:hover {
  background: #dc3545;
}
</style>
