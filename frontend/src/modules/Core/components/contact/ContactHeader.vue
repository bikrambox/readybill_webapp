<template>
  <div class="contact-header">
    <!-- <h1 class="mb-4">Contact Us</h1> -->
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-3">
      <p class="mb-0">{{ $t('common.Get in touch with us') }}</p>
      <div class="contact-phones">
        <i class="bi bi-telephone-fill me-2"></i>
        <span class="phone-numbers">+91 9531227130, +91 9864081806, +91 7002871610</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>
.contact-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
}

.contact-phones {
  display: flex;
  align-items: center;
  font-size: 0.95rem;
}

.phone-numbers {
  color: #495057;
}

@media (max-width: 768px) {
  .contact-header h1 {
    font-size: 1.75rem;
  }
  
  .contact-phones {
    font-size: 0.85rem;
  }
  
  .phone-numbers {
    word-break: break-word;
  }
}
</style>
