<template>
  <component :is="invoiceComponent" />
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useInvoiceStore } from '@/modules/GroceryGermany/stores/invoiceStore'
import InvoiceA4 from '@/modules/GroceryGermany/components/invoice/InvoiceA4.vue'
import Invoice80mm from '@/modules/GroceryGermany/components/invoice/Invoice80mm.vue'
import Invoice50mm from '@/modules/GroceryGermany/components/invoice/Invoice50mm.vue'

const route = useRoute()
const invoiceStore = useInvoiceStore()

// Get bill_id from route params
const billId = computed(() => route.params.bill_id)

// Get invoice data from store
const invoiceData = computed(() => invoiceStore.invoiceData)

// Dynamic component selection based on preference_invoice_format
const invoiceComponent = computed(() => {
  if (!invoiceData.value?.preferences) return null
  
  const format = invoiceData.value.preferences.preference_invoice_format
  
  switch (format) {
    case 0:
      return InvoiceA4
    case 1:
      return Invoice80mm
    case 2:
      return Invoice50mm
    default:
      return InvoiceA4
  }
})

// Load invoice data on mount
onMounted(async () => {
  try {
    await invoiceStore.fetchInvoice(billId.value)
  } catch (err) {
    console.error('Failed to load invoice:', err)
  }
})
</script>
