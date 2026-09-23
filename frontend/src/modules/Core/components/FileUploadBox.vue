<template>
  <div
    class="upload-box"
    :class="{ 'has-file': !!previewUrl || !!fileName, 'drag-over': isDragging }"
    @dragover.prevent="isDragging = true"
    @dragleave.prevent="isDragging = false"
    @drop.prevent="onDrop"
    @click="triggerInput"
  >
    <input
      ref="inputRef"
      type="file"
      :accept="accept"
      class="upload-input"
      @change="onChange"
    />

    <!-- Preview State -->
    <template v-if="previewUrl">
      <img :src="previewUrl" class="upload-preview-img" :alt="label" />
      <button type="button" class="remove-btn" @click.stop="emit('remove')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </template>

    <!-- PDF / Non-image File State -->
    <template v-else-if="fileName">
      <div class="file-info">
        <div class="file-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="1.5">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
          </svg>
        </div>
        <span class="file-name">{{ fileName }}</span>
        <button type="button" class="remove-btn-inline" @click.stop="emit('remove')">
          {{ $t('common.Remove') }}
        </button>
      </div>
    </template>

    <!-- Empty State -->
    <template v-else>
      <div class="upload-placeholder">
        <!-- Photo Icon -->
        <svg v-if="icon === 'photo'" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="1.5">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
        <!-- Document Icon -->
        <svg v-else-if="icon === 'document'" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="1.5">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="16" y1="13" x2="8" y2="13"></line>
          <line x1="16" y1="17" x2="8" y2="17"></line>
          <polyline points="10 9 9 9 8 9"></polyline>
        </svg>
        <!-- QR Icon -->
        <svg v-else-if="icon === 'qr'" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="1.5">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
          <rect x="14" y="14" width="3" height="3"></rect>
          <rect x="18" y="14" width="3" height="3"></rect>
          <rect x="14" y="18" width="3" height="3"></rect>
          <rect x="18" y="18" width="3" height="3"></rect>
        </svg>

        <p class="upload-label">{{ label }}</p>
        <p class="upload-hint">{{ hint }}</p>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  label: { type: String, default: 'Upload File' },
  hint: { type: String, default: '' },
  accept: { type: String, default: 'image/*' },
  icon: { type: String, default: 'photo' }, // 'photo' | 'document' | 'qr'
  previewUrl: { type: String, default: null },
})

const emit = defineEmits(['change', 'remove'])

const inputRef = ref(null)
const isDragging = ref(false)
const fileName = ref('')

const triggerInput = () => inputRef.value?.click()

const processFile = (file) => {
  if (!file) return
  fileName.value = file.name
  emit('change', file)
}

const onChange = (e) => {
  processFile(e.target.files[0])
  // Reset input so same file can be re-selected
  e.target.value = ''
}

const onDrop = (e) => {
  isDragging.value = false
  processFile(e.dataTransfer.files[0])
}

// Clear fileName when file is removed externally
const clearFileName = () => { fileName.value = '' }
defineExpose({ clearFileName })
</script>

<style scoped>
.upload-box {
  border: 2px dashed #ddd;
  border-radius: 10px;
  padding: 24px 16px;
  text-align: center;
  cursor: pointer;
  transition: all 0.25s ease;
  background: #fafafa;
  position: relative;
  min-height: 110px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-box:hover,
.upload-box.drag-over {
  border-color: #007bff;
  background: #f0f7ff;
}

.upload-box.has-file {
  border-color: #28a745;
  background: #f6fff8;
  border-style: solid;
}

.upload-input {
  display: none;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.upload-label {
  font-size: 14px;
  font-weight: 500;
  color: #555;
  margin: 0;
}

.upload-hint {
  font-size: 12px;
  color: #999;
  margin: 0;
}

.upload-preview-img {
  max-height: 140px;
  max-width: 100%;
  border-radius: 6px;
  object-fit: contain;
}

.remove-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  background: #dc3545;
  border: none;
  border-radius: 50%;
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #fff;
  padding: 0;
  transition: background 0.2s;
}

.remove-btn:hover { background: #b02a37; }

.file-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.file-icon { color: #007bff; }

.file-name {
  font-size: 13px;
  color: #333;
  word-break: break-all;
  max-width: 220px;
}

.remove-btn-inline {
  background: none;
  border: none;
  color: #dc3545;
  font-size: 13px;
  cursor: pointer;
  font-weight: 500;
  padding: 0;
}

.remove-btn-inline:hover { text-decoration: underline; }
</style>
