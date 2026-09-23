<script setup>
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>

<template>
  <div class="pagetitle">
    <h1>{{ t('support_page.title') }}</h1>
    <h6 class="text-primary">{{ t('support_page.subtitle') }}</h6>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">{{ t('common.Home') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ t('support_page.title') }}</li>
      </ol>
    </nav>
  </div>
</template>

<style scoped>
.text-primary {
  color: #012970;
}
</style>
