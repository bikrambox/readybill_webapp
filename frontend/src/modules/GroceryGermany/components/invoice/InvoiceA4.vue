<template>
  <div class="invoice-container" v-if="invoiceData">
    <div class="container">
      <div class="row">
        <!-- Header Section -->
        <div class="col-12 text-center">
          <img 
            v-if="invoiceData.isLogo === 1 && showLogo" 
            class="img-fluid logo-img" 
            :src="invoiceData.logo" 
            alt="Business Logo" 
          />
          <h3 v-else class="fw-bold">{{ invoiceData.business_name }}</h3>
          <h5 class="pt-2">{{ $t('invoice_page.Address') }}: {{ invoiceData.address }}</h5>
          <h5 v-if="showGSTIN">{{ $t('invoice_page.gst_no') }}: {{ invoiceData.gstin }}</h5>
        </div>

        <!-- Invoice Info -->
        <div class="col-12">
          <table class="w-100 invoice-info-table">
            <tr>
              <td class="text-start">
                <span>{{ $t('invoice_page.Invoice No.') }}: {{ invoiceData.invoice_number }}</span>
              </td>
              <td class="text-end">
                <span>{{ $t('invoice_page.Date') }}: {{ invoiceData.invoice_date }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <span>{{ $t('invoice_page.User') }}: {{ invoiceData.user_name }}</span>
              </td>
            </tr>
          </table>
        </div>

        <div class="col-12 dotted-border"></div>

        <!-- Items Table -->
        <div class="col-12">
          <table class="table table-borderless">
            <thead>
              <tr v-if="showMRP && showHSN">
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.HSN') }}</th>
                <th scope="col" width="30%" class="vertical-spacing">{{ $t('invoice_page.Item') }}</th>
                <th scope="col" width="8%" class="vertical-spacing">{{ $t('invoice_page.Qty') }}</th>
                <th scope="col" width="9%" class="vertical-spacing">{{ $t('invoice_page.MRP') }}</th>
                <th scope="col" width="9%" class="vertical-spacing">{{ $t('invoice_page.Rate') }}</th>
                <th scope="col" width="12%" class="vertical-spacing">{{ $t('invoice_page.Amount') }}</th>
              </tr>
              <tr v-else-if="showHSN">
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.HSN') }}</th>
                <th scope="col" width="30%" class="vertical-spacing">{{ $t('invoice_page.Item') }}</th>
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.Qty') }}</th>
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.Rate') }}</th>
                <th scope="col" width="12%" class="vertical-spacing">{{ $t('invoice_page.Amount') }}</th>
              </tr>
              <tr v-else-if="showMRP">
                <th scope="col" width="30%" class="vertical-spacing">{{ $t('invoice_page.Item') }}</th>
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.Qty') }}</th>
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.MRP') }}</th>
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.Rate') }}</th>
                <th scope="col" width="12%" class="vertical-spacing">{{ $t('invoice_page.Amount') }}</th>
              </tr>
              <tr v-else>
                <th scope="col" width="40%" class="vertical-spacing">{{ $t('invoice_page.Item') }}</th>
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.Qty') }}</th>
                <th scope="col" width="10%" class="vertical-spacing">{{ $t('invoice_page.Rate') }}</th>
                <th scope="col" width="12%" class="vertical-spacing">{{ $t('invoice_page.Amount') }}</th>
              </tr>
            </thead>
            <tbody class="dotted-border">
              <tr v-for="(item, index) in invoiceData.item_list" :key="index">
                <template v-if="showMRP && showHSN">
                  <td>{{ item.hsn }}</td>
                  <td>{{ item.itemName }}</td>
                  <td>{{ parseFloat(item.quantity).toFixed(2) }} {{ item.selectedUnit }}</td>
                  <td>{{ invoiceData.currency }} {{ formatPrice(item.mrp) }}</td>
                  <td>{{ invoiceData.currency }} {{ formatPrice(item.rate) }}</td>
                  <td>
                    <span v-if="item.isRefund == '1'">– {{ invoiceData.currency }} {{ formatPrice(Math.abs(item.amount)) }}</span>
                    <span v-else>{{ invoiceData.currency }} {{ formatPrice(item.amount) }}</span>
                  </td>
                </template>
                <template v-else-if="showMRP">
                  <td>{{ item.itemName }}</td>
                  <td>{{ parseFloat(item.quantity).toFixed(2) }} {{ item.selectedUnit }}</td>
                  <td>{{ invoiceData.currency }} {{ formatPrice(item.mrp) }}</td>
                  <td>{{ invoiceData.currency }} {{ formatPrice(item.rate) }}</td>
                  <td>
                    <span v-if="item.isRefund == '1'">– {{ invoiceData.currency }} {{ formatPrice(Math.abs(item.amount)) }}</span>
                    <span v-else>{{ invoiceData.currency }} {{ formatPrice(item.amount) }}</span>
                  </td>
                </template>
                <template v-else-if="showHSN">
                  <td>{{ item.hsn }}</td>
                  <td>{{ item.itemName }}</td>
                  <td>{{ parseFloat(item.quantity).toFixed(2) }} {{ item.selectedUnit }}</td>
                  <td>{{ invoiceData.currency }} {{ formatPrice(item.rate) }}</td>
                  <td>
                    <span v-if="item.isRefund == '1'">– {{ invoiceData.currency }} {{ formatPrice(Math.abs(item.amount)) }}</span>
                    <span v-else>{{ invoiceData.currency }} {{ formatPrice(item.amount) }}</span>
                  </td>
                </template>
                <template v-else>
                  <td>{{ item.itemName }}</td>
                  <td>{{ parseFloat(item.quantity).toFixed(2) }} {{ item.selectedUnit }}</td>
                  <td>{{ invoiceData.currency }} {{ formatPrice(item.rate) }}</td>
                  <td>
                    <span v-if="item.isRefund == '1'">– {{ invoiceData.currency }} {{ formatPrice(Math.abs(item.amount)) }}</span>
                    <span v-else>{{ invoiceData.currency }} {{ formatPrice(item.amount) }}</span>
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
          
          <div class="col-12 dotted-border"></div>

          <!-- Totals Section -->
          <table class="w-100">
            <tbody>
              <tr v-if="showMRP">
                <td></td>
                <td></td>
                <td></td>
                <td class="text-start" :width="showMRP && showHSN ? '18%' : '18%'">
                  {{ $t('invoice_page.Total MRP') }}:
                </td>
                <td :width="showMRP && showHSN ? '14%' : '16%'" style="white-space: nowrap;">
                  {{ invoiceData.currency }} {{ formatPrice(calculations.mrp_total) }}
                </td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-start" :width="showMRP && showHSN ? '18%' : '18%'">
                  {{ $t('invoice_page.Sub Total') }}:
                </td>
                <td :width="showMRP && showHSN ? '14%' : '16%'" style="white-space: nowrap;">
                  <span v-if="calculations.sub_total < 0">
                    – {{ invoiceData.currency }} {{ formatPrice(Math.abs(calculations.sub_total)) }}
                  </span>
                  <span v-else>
                    {{ invoiceData.currency }} {{ formatPrice(calculations.sub_total) }}
                  </span>
                </td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-start" :width="showMRP && showHSN ? '18%' : '18%'">
                  {{ $t('invoice_page.Grand Total') }}:
                </td>
                <td :width="showMRP && showHSN ? '14%' : '16%'" style="white-space: nowrap;">
                  <span v-if="parseFloat(invoiceData.grand_total) < 0">
                    – {{ invoiceData.currency }} {{ formatPrice(Math.abs(invoiceData.grand_total)) }}
                  </span>
                  <span v-else>
                    {{ invoiceData.currency }} {{ formatPrice(invoiceData.grand_total) }}
                  </span>
                </td>
              </tr>
              <tr v-if="showMRP && calculations.diff_mrp_n_rate !== 0 && !calculations.isRefund">
                <td></td>
                <td></td>
                <td></td>
                <td class="text-start" :width="showMRP && showHSN ? '18%' : '18%'">
                  {{ $t('invoice_page.You have saved') }}
                </td>
                <td :width="showMRP && showHSN ? '14%' : '16%'">
                  {{ invoiceData.currency }} {{ formatPrice(calculations.diff_mrp_n_rate) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Tax Details -->
        <div class="col-12"><span>{{ $t('invoice_page.Tax Details') }}</span></div>
        <div class="col-12 dotted-border"></div>
        <div class="col-12">
          <table class="w-100">
            <tr>
              <td class="text-start">{{ $t('invoice_page.Total Taxable Value') }}</td>
              <td width="12%" class="text-start">
                {{ invoiceData.currency }} {{ invoiceData.totalTaxAmount }}
              </td>
            </tr>
            <template v-if="invoiceData.taxGroups && Object.keys(invoiceData.taxGroups).length > 0">
              <template v-for="(taxDetails, taxType) in invoiceData.taxGroups" :key="taxType">
                <tr v-for="(tax, percent) in taxDetails" :key="percent">
                  <td class="text-start">{{ tax.taxName }} ({{ percent }}%)</td>
                  <td width="12%" class="text-start">
                    {{ invoiceData.currency }} {{ formatTax(tax.totalTax) }}
                  </td>
                </tr>
              </template>
            </template>
          </table>
        </div>

        <div class="col-12 dotted-border"></div>
        
        <!-- Footer -->
        <div class="col-12 text-center">
          <span>{{ $t('invoice_page.Contact Number') }}: {{ invoiceData.mobile_number }}</span><br>
          <span>{{ $t('invoice_page.It is a computer generated invoice') }}</span>
        </div>
      </div>
    </div>
  </div>
  <div v-else-if="loading" class="text-center p-5">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
  <div v-else-if="error" class="text-center p-5">
    <div class="alert alert-danger" role="alert">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useInvoiceStore } from '@/modules/GroceryGermany/stores/invoiceStore'

const { t } = useI18n()
const route = useRoute()
const invoiceStore = useInvoiceStore()

// Clean text function
const ct = (key) => {
  return t(key).replace(/\s+/g, ' ').trim()
}

// Get bill_id from route params
const billId = computed(() => route.params.bill_id)

// Get invoice data from store
const invoiceData = computed(() => invoiceStore.invoiceData)
const loading = computed(() => invoiceStore.loading)
const error = computed(() => invoiceStore.error)

// Computed properties for preferences
const showMRP = computed(() => invoiceData.value?.preferences?.preference_mrp_invoice === 1)
const showHSN = computed(() => invoiceData.value?.preferences?.preference_hsn_invoice === 1)
const showLogo = computed(() => {
  const logo = invoiceData.value?.logo
  return logo && !['NA', 'na', ''].includes(logo)
})
const showGSTIN = computed(() => {
  const gstin = invoiceData.value?.gstin
  return gstin && !['NA', 'na', ''].includes(gstin)
})

// Calculations
const calculations = computed(() => {
  if (!invoiceData.value) return {}
  
  let mrp_total = 0
  let sub_total = 0
  let isRefund = false

  invoiceData.value.item_list.forEach(item => {
    const quantity = parseFloat(item.quantity)
    const rate = parseFloat(item.rate)
    
    if (item.isRefund === '1' || item.isRefund === 1) {
      sub_total += quantity * rate * -1
      isRefund = true
    } else {
      sub_total += quantity * rate
    }

    if (showMRP.value) {
      const mrp = item.mrp !== 'NA' ? parseFloat(item.mrp) : rate
      mrp_total += quantity * mrp
    }
  })

  const diff_mrp_n_rate = mrp_total - parseFloat(invoiceData.value.grand_total)

  return {
    mrp_total,
    sub_total,
    diff_mrp_n_rate,
    isRefund
  }
})

// Format price based on decimal separator
const formatPrice = (value) => {
  if (!invoiceData.value?.decimal_separator) return value
  const num = parseFloat(value).toFixed(2)
  return invoiceData.value.decimal_separator === ',' 
    ? num.replace('.', ',') 
    : num
}

// Format tax
const formatTax = (value) => {
  return parseFloat(value).toFixed(2)
}

// Load invoice data on mount
onMounted(async () => {
  try {
    await invoiceStore.fetchInvoice(billId.value)
    
    // Trigger print after data loads
    setTimeout(() => {
      window.print()
    }, 500)
  } catch (err) {
    console.error('Failed to load invoice:', err)
  }
})
</script>

<style scoped>
/* A4 page size for printing */
@page {
  size: A4;
  margin: 10mm;
}

@media print {
  body {
    width: 210mm;
    height: 297mm;
    margin: 0 auto;
    padding: 10mm;
  }
}

.invoice-container {
  width: 210mm;
  margin: 0 auto;
  padding: 10mm;
  font-family: 'Courier New', 'Arial', monospace;
  font-size: 12pt;
}

.container {
  max-width: 190mm;
}

.dotted-border {
  border-top: 2px dotted black;
  padding: 8px 0;
}

.logo-img {
  max-width: 180px;
  max-height: 80px;
}

.invoice-info-table {
  table-layout: fixed;
}

.vertical-spacing {
  padding-top: -10px !important;
  padding-bottom: 30px !important;
}

.table {
  font-size: 10pt;
}

.table th, .table td {
  padding: 4px;
  vertical-align: middle;
}

@media screen {
  .invoice-container {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin-top: 20px;
    margin-bottom: 20px;
  }
}
</style>
