<template>
  <section class="founder-section py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-12 col-md-4 text-center mb-4 mb-md-0">
          <div class="founder-image-wrapper">
            <img src="@/assets/images/founder.jpg" alt="Jay J. Das" class="founder-image" />
          </div>
        </div>
        <div class="col-12 col-md-8">
          <div class="founder-content">
            <h3 class="founder-name">Jay J. Das</h3>
            <p class="founder-role">{{ $t('about_page.founder') }}</p>
            <blockquote class="founder-quote">
              "{{ $t('common.founder_note') }}"
            </blockquote>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>
.founder-section {
  background-color: #ffffff;
  padding: 3rem 0;
}

.founder-image-wrapper {
  max-width: 280px;
  margin: 0 auto;
}

.founder-image {
  width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.founder-content {
  padding-left: 2rem;
}

.founder-name {
  font-size: 1.75rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 0.25rem;
}

.founder-role {
  font-size: 1rem;
  color: #4169E1;
  margin-bottom: 1.5rem;
}

.founder-quote {
  font-size: 1rem;
  line-height: 1.7;
  color: #555;
  font-style: italic;
  border-left: 4px solid #4169E1;
  padding-left: 1.5rem;
  margin: 0;
}

@media (max-width: 768px) {
  .founder-content {
    padding-left: 0;
    text-align: center;
  }
  
  .founder-name {
    font-size: 1.5rem;
  }
  
  .founder-role {
    font-size: 0.9rem;
  }
  
  .founder-quote {
    font-size: 0.9rem;
    text-align: left;
    padding-left: 1rem;
    border-left-width: 3px;
  }
  
  .founder-image-wrapper {
    max-width: 220px;
  }
}
</style>
