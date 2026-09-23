<template>
  <div v-if="invoiceData" class="thermal-50mm">
    <div class="bill-container">
      <!-- Header Info -->
      <div class="header-info text-center">
        <img 
          v-if="invoiceData.isLogo === 1 && showLogo" 
          :src="invoiceData.logo" 
          alt="Logo" 
          class="logo" 
        />
        <p v-else class="fw-bold mb-0">{{ invoiceData.business_name }}</p>
        <p>{{ ct('invoice_page.Address') }}: {{ invoiceData.address }}</p>
        <p v-if="showGSTIN">{{ ct('invoice_page.GST No.') }}: {{ invoiceData.gstin }}</p>
        <p>{{ ct('invoice_page.Invoice No.') }}: {{ invoiceData.invoice_number }}</p>
        <p>{{ ct('invoice_page.Date') }}: {{ invoiceData.invoice_date }}</p>
        <p>{{ ct('invoice_page.User') }}: {{ invoiceData.user_name }}</p>
      </div>

      <!-- Items Table -->
      <table class="table table-bordered">
        <thead>
          <tr v-if="showMRP && showHSN">
            <th width="16%" class="item-hsn">{{ ct('invoice_page.HSN') }}</th>
            <th width="25%" class="item-name">{{ ct('invoice_page.Item') }}</th>
            <th width="15%" class="item-qty">{{ ct('invoice_page.Qty') }}</th>
            <th width="17%" class="item-mrp">{{ ct('invoice_page.MRP') }}</th>
            <th width="17%" class="item-rate">{{ ct('invoice_page.Rate') }}</th>
            <th width="20%" class="item-amount">{{ ct('invoice_page.Amount') }}</th>
          </tr>
          <tr v-else-if="showHSN">
            <th width="16%" class="item-hsn">{{ ct('invoice_page.HSN') }}</th>
            <th width="32%" class="item-name">{{ ct('invoice_page.Item') }}</th>
            <th width="15%" class="item-qty">{{ ct('invoice_page.Qty') }}</th>
            <th width="17%" class="item-rate">{{ ct('invoice_page.Rate') }}</th>
            <th width="20%" class="item-amount">{{ ct('invoice_page.Amount') }}</th>
          </tr>
          <tr v-else-if="showMRP">
            <th width="32%" class="item-name">{{ ct('invoice_page.Item') }}</th>
            <th width="15%" class="item-qty">{{ ct('invoice_page.Qty') }}</th>
            <th width="17%" class="item-mrp">{{ ct('invoice_page.MRP') }}</th>
            <th width="17%" class="item-rate">{{ ct('invoice_page.Rate') }}</th>
            <th width="20%" class="item-amount">{{ ct('invoice_page.Amount') }}</th>
          </tr>
          <tr v-else>
            <th width="40%" class="item-name">{{ ct('invoice_page.Item') }}</th>
            <th width="15%" class="item-qty">{{ ct('invoice_page.Qty') }}</th>
            <th width="17%" class="item-rate">{{ ct('invoice_page.Rate') }}</th>
            <th width="20%" class="item-amount">{{ ct('invoice_page.Amount') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in invoiceData.item_list" :key="index">
            <td v-if="showHSN" class="item-hsn">{{ item.hsn !== 'NA' ? item.hsn : '' }}</td>
            <td class="item-name">{{ item.itemName }}</td>
            <td class="item-qty text-end">{{ formatQuantity(item.quantity) }} {{ item.selectedUnit }}</td>
            <td v-if="showMRP" class="item-mrp text-end">
              <span v-if="item.mrp == 0 || item.mrp == 'NA'">N/A</span>
              <span v-else>{{ invoiceData.currency }} {{ formatPrice(item.mrp) }}</span>
            </td>
            <td class="item-rate text-end">{{ invoiceData.currency }} {{ formatPrice(item.rate) }}</td>
            <td class="item-amount text-end">
              <span v-if="item.isRefund == '1'">
                – {{ invoiceData.currency }} {{ formatPrice(Math.abs(item.amount)) }}
              </span>
              <span v-else>{{ invoiceData.currency }} {{ formatPrice(item.amount) }}</span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Totals Table -->
      <table class="totals-table">
        <tbody>
          <tr v-if="showMRP">
            <td class="text-start" width="70%">{{ ct('invoice_page.Total MRP') }}:</td>
            <td class="text-end" width="30%">
              {{ invoiceData.currency }} {{ formatPrice(calculations.mrp_total) }}
            </td>
          </tr>
          <tr>
            <td class="text-start" width="70%">{{ ct('invoice_page.Sub Total') }}:</td>
            <td class="text-end" width="30%">
              <span v-if="calculations.sub_total < 0">
                – {{ invoiceData.currency }} {{ formatPrice(Math.abs(calculations.sub_total)) }}
              </span>
              <span v-else>{{ invoiceData.currency }} {{ formatPrice(calculations.sub_total) }}</span>
            </td>
          </tr>
          <tr>
            <td class="text-start" width="70%">{{ ct('invoice_page.Grand Total') }}:</td>
            <td class="text-end" width="30%">
              <span v-if="parseFloat(invoiceData.grand_total) < 0">
                – {{ invoiceData.currency }} {{ formatPrice(Math.abs(invoiceData.grand_total)) }}
              </span>
              <span v-else>
                {{ invoiceData.currency }} {{ formatPrice(invoiceData.grand_total) }}
              </span>
            </td>
          </tr>
          <tr v-if="showMRP && calculations.diff_mrp_n_rate !== 0 && !calculations.isRefund">
            <td class="text-start" width="70%">{{ ct('invoice_page.You have saved') }}:</td>
            <td class="text-end saved-amount" width="30%">
              {{ invoiceData.currency }} {{ formatPrice(calculations.diff_mrp_n_rate) }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Tax Details -->
      <div class="tax-details">
        <p>{{ ct('invoice_page.Tax Details') }}</p>
        <table class="tax-table">
          <tbody>
            <tr>
              <th class="text-start" width="70%">{{ ct('invoice_page.Total Taxable Value') }}</th>
              <td class="text-end" width="30%">
                {{ invoiceData.currency }} {{ formatTax(invoiceData.totalTaxAmount) }}
              </td>
            </tr>
            <template v-if="invoiceData.taxGroups && Object.keys(invoiceData.taxGroups).length > 0">
              <template v-for="(taxDetails, taxType) in invoiceData.taxGroups" :key="taxType">
                <tr v-for="(tax, percent) in taxDetails" :key="percent">
                  <th class="text-start">{{ tax.taxName }} ({{ percent }}%)</th>
                  <td class="text-end">{{ invoiceData.currency }} {{ formatTax(tax.totalTax) }}</td>
                </tr>
              </template>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="footer text-center">
        <p>{{ ct('invoice_page.Contact Number') }}: {{ formatPhoneNumber(invoiceData.mobile_number) }}</p>
        <p>{{ ct('invoice_page.It is a computer generated invoice') }}</p>
      </div>
    </div>
  </div>
  <div v-else-if="loading" class="text-center p-3">
    <div class="spinner-border spinner-border-sm" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useInvoiceStore } from '@/modules/GroceryGermany/stores/invoiceStore'

const { t } = useI18n()
const invoiceStore = useInvoiceStore()

const ct = (key) => t(key).replace(/\s+/g, ' ').trim()

const invoiceData = computed(() => invoiceStore.invoiceData)
const loading = computed(() => invoiceStore.loading)

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

  return { mrp_total, sub_total, diff_mrp_n_rate, isRefund }
})

const formatPrice = (value) => {
  if (!invoiceData.value?.decimal_separator) return value
  const num = parseFloat(value).toFixed(2)
  return invoiceData.value.decimal_separator === ',' ? num.replace('.', ',') : num
}

const formatTax = (value) => parseFloat(value).toFixed(2)

const formatQuantity = (value) => parseFloat(value).toFixed(0)

const formatPhoneNumber = (number) => {
  return number.replace('+91-', '+91 ').replace(/(\d{5})(\d{5})/, '$1 $2')
}

onMounted(() => {
  setTimeout(() => window.print(), 500)
})
</script>

<style scoped>
.thermal-50mm {
  width: 100%;
  min-height: 100vh;
  margin: 0;
  padding: 0;
  font-family: 'Courier New', 'Arial', monospace;
  font-size: 7pt;
  font-weight: 600;
  color: #000000;
  line-height: 1.1;
  display: flex;
  justify-content: center;
  align-items: center;
}

.bill-container {
  width: 50mm;
  padding: 0;
  margin: 0 auto;
}

.logo {
  width: 60px !important;
  height: 30px !important;
  display: block;
  margin: 0 auto 0.5mm auto;
}

.header-info p {
  margin-bottom: 0.5mm;
}

.table-bordered,
.totals-table,
.tax-table {
  border: 1px solid #000 !important;
  width: 50mm !important;
  margin: 0 0 1mm 0;
  table-layout: fixed;
}

.table-bordered th,
.table-bordered td,
.tax-table th,
.tax-table td {
  border: 1px solid #000 !important;
  padding: 0.1mm 0.2mm;
  vertical-align: top;
  overflow-wrap: break-word;
}

.totals-table td {
  border: none !important;
  padding: 0.1mm 0.2mm;
}

.item-name {
  white-space: normal;
  font-size: 6.5pt;
}

.item-qty {
  white-space: nowrap;
}

.item-rate {
  white-space: normal;
  font-size: 6pt;
}

.item-amount {
  white-space: normal;
  font-size: 6pt;
}

.item-mrp {
  white-space: normal;
  font-size: 6pt;
}

.item-hsn {
  white-space: normal;
  font-size: 5.5pt;
}

.tax-details p {
  font-weight: bold;
  font-size: 8pt;
  margin-bottom: 0.5mm;
}

.footer p {
  margin-bottom: 0.5mm;
}

.saved-amount {
  font-weight: bold;
}

@media print {
  @page {
    margin: 0;
    size: 50mm auto;
  }

  .thermal-50mm {
    width: 50mm;
    min-height: 0;
    display: block;
    margin: 0;
    padding: 0;
  }

  .bill-container {
    width: 50mm;
    margin: 0;
  }

  .table-bordered,
  .totals-table,
  .tax-table {
    width: 50mm !important;
  }
}
</style>
