<template>
  <section class="why-choose-section py-5">
    <div class="container">
      <h2 class="section-title text-center mb-4">{{ $t('common.why_choose_ready_bill') }}</h2>
      <div class="row">
        <div class="col-12 col-md-6">
          <div v-for="reason in reasonsColumn1" :key="reason.id" class="reason-item">
            <span class="check-icon">✓</span>
            <span class="reason-text">{{ reason.text }}</span>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div v-for="reason in reasonsColumn2" :key="reason.id" class="reason-item">
            <span class="check-icon">✓</span>
            <span class="reason-text">{{ reason.text }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const reasonsColumn1 = ref([
  { id: 1, text: t('common.No more long billing queues') },
  { id: 2, text: t('common.Prints and records all your sales automatically') }
])

const reasonsColumn2 = ref([
  { id: 3, text: t('common.Integrates with any accounting software') },
  { id: 4, text: t('common.Government norms compliant with applicable taxes') }
])
</script>

<style scoped>
.why-choose-section {
  background-color: #f8f9fa;
  padding: 3rem 0;
}

.section-title {
  font-size: 2rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 2.5rem;
}

.reason-item {
  display: flex;
  align-items: center;
  margin-bottom: 1.25rem;
  padding: 0.5rem 0;
}

.check-icon {
  width: 28px;
  height: 28px;
  background-color: #4CAF50;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-right: 1rem;
  flex-shrink: 0;
  font-size: 0.9rem;
}

.reason-text {
  font-size: 1rem;
  color: #333;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .section-title {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .reason-item {
    margin-bottom: 1rem;
  }
  
  .check-icon {
    width: 24px;
    height: 24px;
    font-size: 0.8rem;
  }
  
  .reason-text {
    font-size: 0.9rem;
  }
}
</style>
