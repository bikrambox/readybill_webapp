<template>
  <div class="google-map-wrapper py-4 py-md-0">
    <div class="mb-3">
      <iframe
        :src="mapUrl"
        width="100%"
        height="400"
        style="border:0; border-radius: 10px;"
        allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const mapUrl = ref(
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3581.189836259248!2d91.68229927519711!3d26.157946277105086!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x375a5ba41ba15c79%3A0xc7110f7bf45c6a2f!2sAlegra%20Labs!5e0!3m2!1sen!2sin!4v1737445562197!5m2!1sen!2sin'
)
</script>

<style scoped>
.google-map-wrapper {
  margin-bottom: 1.5rem;
}

iframe {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  iframe {
    height: 300px;
  }
}
</style>
