import { createI18n } from 'vue-i18n';

// Default empty messages - will be populated from Laravel
const messages = {
    en: {},
    hi: {},
    de: {},
    es: {},
    fr: {}
};

const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages,
    globalInjection: true,
    missingWarn: false,
    fallbackWarn: false
});

export default i18n;
