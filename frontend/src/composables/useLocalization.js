import { useRoute } from 'vue-router'
import { computed } from 'vue'

export function useLocalization() {
    const route = useRoute()

    const getLocalizedPath = (path) => {
        return `/${route.params.country}/${route.params.lang}/${path}`
    }

    const currentCountry = computed(() => route.params.country)
    const currentLang = computed(() => route.params.lang)

    return {
        getLocalizedPath,
        currentCountry,
        currentLang
    }
}
