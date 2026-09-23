<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const showModal = ref(false);    // Controls whether modal/backdrop exists in DOM
const isVisible = ref(false);    // Controls modal content animation state

const title = ref('');
const description = ref('');
const attachment = ref(null);
const attachmentPreview = ref('');
const errors = ref({ title: '', description: '', attachment: '' });
const isLoading = ref(false);

const allowedTypes = [
  'image/jpeg',
  'image/jpg',
  'image/png',
  'image/gif',
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/rtf',
];
const maxSize = 20 * 1024 * 1024; // 20 MB

function open() {
  showModal.value = true;
  requestAnimationFrame(() => {
    isVisible.value = true;
  });
  resetForm();
}

function close() {
  isVisible.value = false; // start close animation
}

function onAfterLeave() {
  showModal.value = false;  // remove from DOM after animation
}

function resetForm() {
  title.value = '';
  description.value = '';
  attachment.value = null;
  attachmentPreview.value = '';
  errors.value = { title: '', description: '', attachment: '' };
}

function onFileChange(e) {
  const file = e.target.files[0];
  errors.value.attachment = '';

  if (file) {
    if (!allowedTypes.includes(file.type)) {
      errors.value.attachment = t('support_page.invalid_file_type');
      attachment.value = null;
      attachmentPreview.value = '';
      return;
    }
    if (file.size > maxSize) {
      errors.value.attachment = t('support_page.file_too_large');
      attachment.value = null;
      attachmentPreview.value = '';
      return;
    }
    attachment.value = file;

    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = (e) => {
        attachmentPreview.value = e.target.result;
      };
      reader.readAsDataURL(file);
    } else {
      attachmentPreview.value = 'assets/img/file.png';
    }
  } else {
    attachmentPreview.value = '';
  }
}

function clearAttachment() {
  attachment.value = null;
  attachmentPreview.value = '';
  const input = document.getElementById('attachment');
  if (input) input.value = '';
}

async function submit() {
  errors.value.title = '';
  errors.value.description = '';

  if (!title.value.trim()) {
    errors.value.title = t('support_page.enter_title');
  }
  if (!description.value.trim()) {
    errors.value.description = t('support_page.enter_description');
  }

  if (errors.value.title || errors.value.description) return;

  isLoading.value = true;

  try {
    // Simulate HTTP request delay
    await new Promise((r) => setTimeout(r, 1200));
    alert(t('support_page.thank_you'));
    close();
  } catch {
    alert(t('support_page.error_occurred'));
  } finally {
    isLoading.value = false;
  }
}

// Expose open to parent via ref
defineExpose({ open });
</script>

<template>
  <transition name="fade-scale" @after-leave="onAfterLeave">
    <div
      v-if="showModal"
      class="modal-backdrop-custom d-flex align-items-center justify-content-center"
      @click.self="close"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="modal-dialog modal-sm-custom rounded-4 shadow-lg bg-white"
        :class="{ 'show-visible': isVisible }"
      >
        <div class="modal-content border-0 rounded-4 overflow-hidden">

          <header class="modal-header bg-gradient-primary text-white py-3 px-4">
            <h5 class="modal-title fw-bold mb-0">{{ t('support_page.ask_your_questions') }}</h5>
            <button type="button" class="btn-close btn-close-white btn-close-lg" aria-label="Close" @click="close" />
          </header>

          <form @submit.prevent="submit" novalidate>
            <div class="modal-body p-4">
              <div class="mb-4">
                <label for="title" class="form-label fw-semibold small">{{ t('support_page.title') }}</label>
                <input
                  id="title"
                  v-model="title"
                  type="text"
                  class="form-control form-control-lg rounded-pill shadow-sm"
                  :class="{ 'is-invalid': errors.title }"
                  autocomplete="off"
                />
                <div class="invalid-feedback small">{{ errors.title }}</div>
              </div>

              <div class="mb-4">
                <label for="description" class="form-label fw-semibold small">{{ t('support_page.description') }}</label>
                <textarea
                  id="description"
                  v-model="description"
                  rows="4"
                  class="form-control form-control-lg rounded-3 shadow-sm"
                  :class="{ 'is-invalid': errors.description }"
                />
                <div class="invalid-feedback small">{{ errors.description }}</div>
              </div>

              <div>
                <label for="attachment" class="form-label fw-semibold small">{{ t('support_page.attachment') }}</label>
                <input
                  id="attachment"
                  type="file"
                  class="form-control form-control-lg rounded-pill shadow-sm"
                  @change="onFileChange"
                />
                <div v-if="attachmentPreview" class="mt-3 position-relative d-inline-block">
                  <img
                    :src="attachmentPreview"
                    alt="Attachment preview"
                    class="img-thumbnail rounded-3"
                    style="max-width: 170px; max-height: 90px;"
                  />
                  <button
                    type="button"
                    class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle"
                    aria-label="Clear attachment"
                    @click="clearAttachment"
                    style="transform: translate(25%, -25%);"
                  >
                    ×
                  </button>
                </div>
                <div class="text-danger small mt-2">{{ errors.attachment }}</div>
              </div>
            </div>

            <footer class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-3">
              <button
                type="button"
                class="btn btn-lg btn-outline-light rounded-pill px-4"
                @click="close"
                :disabled="isLoading"
              >
                {{ t('common.Cancel') }}
              </button>
              <button
                type="submit"
                class="btn btn-lg btn-light rounded-pill px-5 shadow-sm"
                :disabled="isLoading"
              >
                {{ t('common.Submit') }}
              </button>
            </footer>
          </form>

        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
:root {
  --bs-primary-start: #4f46e5;
  --bs-primary-end: #3b82f6;
}

.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(6px);
  z-index: 1050;
}

.modal-sm-custom {
  max-width: 460px;
  margin: 0 1rem;
}

.modal-dialog {
  opacity: 0;
  transform: scale(0.85);
  transition: all 0.3s ease-out;
}

.show-visible {
  opacity: 1;
  transform: scale(1);
  transition: all 0.3s ease-in;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, var(--bs-primary-start), var(--bs-primary-end));
}

.form-control:focus,
.form-control-lg:focus {
  box-shadow: 0 0 8px rgb(59 130 246 / 0.7);
  border-color: #3b82f6;
}

.btn-light {
  color: #1e293b;
  background-color: #f8fafc;
  border: none;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.btn-light:hover:not(:disabled) {
  background-color: #e2e8f0;
}

.btn-outline-light {
  color: #f8fafc;
  border-color: #f8fafc;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.btn-outline-light:hover:not(:disabled) {
  color: #475569;
  background-color: #f8fafc;
  border-color: #f8fafc;
}

.btn-close-white {
  filter: invert(100%);
  opacity: 0.7;
}

.btn-close-white:hover {
  opacity: 1;
}

.invalid-feedback {
  font-size: 0.8rem;
}

.form-control-lg.rounded-pill {
  padding-left: 1.1rem;
  padding-right: 1.1rem;
}

.btn-lg.rounded-pill {
  border-radius: 50px;
}

.img-thumbnail {
  border: none;
  box-shadow: 0 2px 6px rgb(0 0 0 / 0.10);
}
</style>
