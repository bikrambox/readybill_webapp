<script setup>
import { onMounted } from 'vue';
import { useLocale } from '@/composables/useLocale';
import { useLocaleStore } from '@/stores/localeStore';
import { useI18n } from 'vue-i18n';
import LanguageDropdown from '../components/LanguageDropdown.vue';


const { t, language } = useLocale();
const localeStore = useLocaleStore();
const i18n = useI18n();

onMounted(() => {
  console.log('=== Translation Debug ===');
  console.log('Current language:', language.value);
  console.log('Store translations:', localeStore.translations);
  console.log('i18n messages:', i18n.messages.value);
  console.log('i18n locale:', i18n.locale.value);
  console.log('Test translation:', t('common.home'));
  console.log('========================');
});
</script>

<template>
  <div class="container py-5">

    <LanguageDropdown />

    <h1>{{ t('common.Home') }}</h1>
    <p>Language: {{ language }}</p>
    
    <!-- Debug info -->
    <div class="mt-4 p-3 bg-light border rounded">
      <h5>Debug Info:</h5>
      <p><strong>Current locale:</strong> {{ language }}</p>
      <p><strong>Translation test:</strong> {{ t('common.Home') }}</p>
      <p><strong>About:</strong> {{ t('common.About') }}</p>
      <p><strong>Contact:</strong> {{ t('common.Contact') }}</p>
    </div>
  </div>
</template>
