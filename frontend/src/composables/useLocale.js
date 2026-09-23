import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useLocaleStore } from '@/stores/localeStore';
import { useRouter, useRoute } from 'vue-router';

export function useLocale() {
    const { locale, t, setLocaleMessage, messages } = useI18n();
    const localeStore = useLocaleStore();
    const router = useRouter();
    const route = useRoute();

    // Define the helper function FIRST
    const loadAndSetLanguage = async (lang) => {
        console.log('Loading and setting language:', lang);

        // Check if translations already loaded in i18n
        if (!messages.value[lang] || Object.keys(messages.value[lang]).length === 0) {
            console.log('Translations not in i18n, loading...');

            // Check if in store
            let translations = localeStore.translations[lang];

            // If not in store, fetch from API
            if (!translations) {
                translations = await localeStore.loadTranslations(lang);
            }

            if (translations) {
                console.log('Setting translations in i18n for', lang);
                setLocaleMessage(lang, translations);
            }
        }

        // Update locale
        locale.value = lang;
        console.log('Current i18n locale:', locale.value);
    };

    // Now use the function in watchers
    watch(
        () => route.params.lang,
        async (newLang) => {
            if (newLang && newLang !== locale.value) {
                console.log('Route language changed to:', newLang);
                await loadAndSetLanguage(newLang);
            }
        },
        { immediate: true }
    );

    watch(
        () => localeStore.language,
        async (newLang) => {
            if (newLang && newLang !== locale.value) {
                console.log('Store language changed to:', newLang);
                await loadAndSetLanguage(newLang);
            }
        },
        { immediate: true }
    );

    // Change language
    const changeLanguage = async (newLanguage) => {
        const newCountry = localeStore.country;

        console.log('Changing language to:', newLanguage);

        // Load translations
        const translations = await localeStore.loadTranslations(newLanguage);

        if (translations) {
            setLocaleMessage(newLanguage, translations);
            locale.value = newLanguage;
        }

        // Update store
        await localeStore.setLocale(newCountry, newLanguage);

        // Navigate to new route
        const currentPath = route.path.replace(`/${route.params.country}/${route.params.lang}`, '') || '/';
        router.push(`/${newCountry}/${newLanguage}${currentPath}`);
    };

    return {
        t,
        locale: computed(() => locale.value),
        country: computed(() => localeStore.country),
        language: computed(() => localeStore.language),
        currentLocale: computed(() => localeStore.currentLocale),
        availableLanguages: computed(() => localeStore.availableLanguages),
        availableCountries: computed(() => localeStore.availableCountries),
        isLoading: computed(() => localeStore.isLoading),
        changeLanguage
    };
}
