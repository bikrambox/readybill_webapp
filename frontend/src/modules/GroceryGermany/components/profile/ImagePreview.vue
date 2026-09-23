<template>
  <div>
    <div v-if="previewUrl && previewUrl" class="position-relative d-inline-block mb-2">
      <img
        :src="previewUrl"
        class="img-fluid rounded"
        alt="Preview"
        style="max-width: 200px; max-height: 100px; object-fit: contain; border: 1px solid #dee2e6;"
      />
      <button
        type="button"
        class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle"
        @click="handleDelete"
        style="transform: translate(25%, -25%); width: 30px; height: 30px; padding: 0;"
        title="Delete logo"
      >
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    
    <input
      ref="fileInput"
      type="file"
      class="form-control"
      :class="{ 'border-danger': error }"
      accept="image/jpeg,image/jpg,image/png,image/gif"
      @change="handleFileChange"
    />
    
    <span v-if="error" class="text-danger d-block mt-1 small">{{ error }}</span>
    <small class="text-muted d-block mt-1">{{ $t('common.Max size: 5MB. Allowed: JPG, PNG, GIF') }}</small>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  preview: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['file-selected', 'delete'])

const fileInput = ref(null)
const localPreview = ref(props.preview)

const previewUrl = computed(() => localPreview.value)

watch(() => props.preview, (newVal) => {
  localPreview.value = newVal
})

const handleFileChange = (event) => {
  const file = event.target.files[0]
  
  if (!file) return
  
  // Validate file
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']
  const maxSize = 5 * 1024 * 1024 // 5MB
  
  if (!validTypes.includes(file.type)) {
    alert('Please upload a valid image file (JPEG, PNG, GIF)')
    if (fileInput.value) {
      fileInput.value.value = ''
    }
    return
  }
  
  if (file.size > maxSize) {
    alert(t('common.File size must be less than 5MB'))
    if (fileInput.value) {
      fileInput.value.value = ''
    }
    return
  }
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    localPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
  
  // Emit file to parent
  emit('file-selected', file)
}

const handleDelete = () => {
  localPreview.value = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  emit('delete')
}
</script>

<style scoped>
.border-danger {
  border-color: #dc3545 !important;
}

.text-danger {
  color: #dc3545;
}

.btn-danger:hover {
  opacity: 0.9;
}
</style>
