<template>
  <form @submit.prevent="handleSubmit" class="row g-3">
    <div class="col-12">
      <div
        ref="dropArea"
        class="border border-primary rounded p-4 text-center"
        :class="{ 'bg-light border-2': isDragging }"
        @dragover.prevent="isDragging = true"
        @dragenter.prevent="isDragging = true"
        @dragleave="isDragging = false"
        @dragend="isDragging = false"
        @drop.prevent="handleDrop"
      >
        <i class="bi bi-cloud-upload fs-1 text-primary mb-3 d-block"></i>
        <p class="mb-2">
          {{ $t('upload_data_page.drag_n_drop_note') }}
          <label for="file" class="text-primary fw-bold" style="cursor: pointer">
            {{ $t('common.browse') }}
          </label>
        </p>
        <input
          ref="fileInput"
          type="file"
          id="file"
          name="file"
          class="form-control d-none"
          accept=".xls, .xlsx"
          @change="handleFileSelect"
        />
        <p v-if="fileName" class="text-muted mt-3 mb-0">
          <i class="bi bi-file-earmark-excel text-success me-2"></i>
          {{ fileName }}
        </p>
        <button
          v-if="fileName"
          type="button"
          class="btn btn-sm btn-danger mt-3"
          @click="clearFile"
        >
          <i class="bi bi-x-circle me-1"></i>
          {{ $t('common.Clear') }}
        </button>
      </div>
    </div>
    <div class="col-12 text-end">
      <button type="submit" class="btn btn-primary" :disabled="!selectedFile">
        <i class="bi bi-upload me-2"></i>
        {{ $t('common.Upload') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['upload'])

const dropArea = ref(null)
const fileInput = ref(null)
const selectedFile = ref(null)
const fileName = ref('')
const isDragging = ref(false)

const handleFileSelect = (event) => {
  const files = event.target.files
  if (files.length > 0) {
    selectedFile.value = files[0]
    fileName.value = files[0].name
  }
}

const handleDrop = (event) => {
  isDragging.value = false
  const files = event.dataTransfer.files
  if (files.length > 0) {
    selectedFile.value = files[0]
    fileName.value = files[0].name
    fileInput.value.files = files
  }
}

const clearFile = () => {
  selectedFile.value = null
  fileName.value = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleSubmit = () => {
  if (selectedFile.value) {
    emit('upload', selectedFile.value)
    clearFile()
  }
}
</script>
