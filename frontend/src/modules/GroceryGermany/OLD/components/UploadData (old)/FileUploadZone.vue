<template>
  <form @submit.prevent="handleSubmit" class="mb-3">
    <div 
      ref="dropAreaRef"
      class="border border-primary rounded p-4 text-center"
      :class="{ 'bg-light': isDragging }"
      @dragover.prevent="handleDragOver"
      @dragenter.prevent="handleDragEnter"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
    >
      <p class="mb-2">
        {{ $t('upload_data_page.drag_n_drop_note') }}
        <label 
          for="fileInput" 
          class="text-primary text-decoration-underline" 
          style="cursor: pointer"
        >
          {{ $t('common.browse') }}
        </label>
      </p>
      
      <input 
        id="fileInput"
        ref="fileInputRef"
        type="file" 
        class="d-none" 
        accept=".xls, .xlsx"
        @change="handleFileChange"
      />
      
      <p v-if="fileName" class="text-muted mb-2">
        Selected File: {{ fileName }}
      </p>
      
      <button 
        v-if="fileName"
        type="button" 
        class="btn btn-sm btn-danger mt-2"
        @click="clearFile"
      >
        {{ $t('common.Clear') }}
      </button>
    </div>
    
    <div class="text-end mt-3">
      <button 
        type="submit" 
        class="btn btn-primary"
        :disabled="!selectedFile"
      >
        {{ $t('common.Upload') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['file-selected'])

const dropAreaRef = ref(null)
const fileInputRef = ref(null)
const selectedFile = ref(null)
const fileName = ref('')
const isDragging = ref(false)

const handleDragOver = (e) => {
  e.preventDefault()
  isDragging.value = true
}

const handleDragEnter = (e) => {
  e.preventDefault()
  isDragging.value = true
}

const handleDragLeave = (e) => {
  e.preventDefault()
  isDragging.value = false
}

const handleDrop = (e) => {
  e.preventDefault()
  isDragging.value = false
  
  const files = e.dataTransfer.files
  if (files.length > 0) {
    selectedFile.value = files[0]
    fileName.value = files[0].name
  }
}

const handleFileChange = (e) => {
  const files = e.target.files
  if (files.length > 0) {
    selectedFile.value = files[0]
    fileName.value = files[0].name
  }
}

const clearFile = () => {
  selectedFile.value = null
  fileName.value = ''
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const handleSubmit = () => {
  if (selectedFile.value) {
    emit('file-selected', selectedFile.value)
    clearFile()
  }
}
</script>
