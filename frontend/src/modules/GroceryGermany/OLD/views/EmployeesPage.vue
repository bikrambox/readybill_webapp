<template>
  <div class="update-inventory-page">
    <div class="container-fluid px-3 px-lg-4">
      <!-- Page Header -->
      <div class="row mb-3 mb-lg-4">
        <div class="col-12">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-2">
              <li class="breadcrumb-item">
                <a href="#" class="text-decoration-none">Home</a>
              </li>
              <li class="breadcrumb-item active">
                All Employees
              </li>
            </ol>
          </nav>
          <h1 class="h3 h2-lg fw-bold mb-0">All Employees</h1>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="employeeStore.error" class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ employeeStore.error }}
        <button type="button" class="btn-close" @click="employeeStore.clearError()"></button>
      </div>

      <!-- Filters Component -->
      <EmployeesFilters
        :filter-options="filterOptions"
        :selected-filter-type="selectedFilterType"
        :selected-filter-value="selectedFilterValue"
        :search-query="searchQuery"
        :selected-count="selectedCount"
        @update:filter-type="handleFilterTypeUpdate"
        @update:filter-value="handleFilterValueUpdate"
        @update:search-query="handleSearchUpdate"
        @add-employee="openAddEmployeeModal"
      />

      <!-- Desktop Table Component -->
      <div class="d-none d-lg-block">
        <EmployeesTable
          ref="tableComponent"
          :data="employeeStore.formattedEmployees"
          :is-loading="employeeStore.initialLoading || employeeStore.loading"
          :total-records="employeeStore.totalRecords"
          :filtered-records="employeeStore.filteredRecords"
          @row-click="openViewModal"
          @delete-item="handleDeleteSingle"
          @table-ready="onTableReady"
          @page-change="handlePageChange"
          @length-change="handleLengthChange"
        />
      </div>

      <!-- Mobile List Component -->
      <div class="d-lg-none">
        <EmployeesMobileList
          :display-data="mobileDisplayData"
          :is-loading="employeeStore.initialLoading || employeeStore.loading"
          :page-info="mobilePageInfo"
          @row-click="openViewModal"
          @delete-item="handleDeleteSingle"
          @prev-page="mobilePrevPage"
          @next-page="mobileNextPage"
        />
      </div>
    </div>

    <!-- Footer -->
    <Footer />

    <!-- Employee View/Edit Modal -->
    <EmployeeViewModal
      ref="employeeModalComponent"
      :employee="selectedEmployee"
      @save="saveEmployee"
      @close="closeModal"
    />

    <!-- Delete Confirmation Modal -->
    <DeleteConfirmModal
      ref="deleteConfirmModal"
      :item-name="deleteItemName"
      @confirm="confirmDelete"
    />
    
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useEmployeeStore } from '@/modules/GroceryGermany/stores/employeeStore'
import EmployeesFilters from '../components/employee/EmployeesFilters.vue'
import EmployeesTable from '../components/employee/EmployeesTable.vue'
import EmployeesMobileList from '../components/employee/EmployeesMobileList.vue'
import EmployeeViewModal from '../components/employee/EmployeeViewModal.vue'
import DeleteConfirmModal from '@/modules/Core/components/DeleteConfirmModal.vue'
import Footer from '@/modules/GroceryGermany/components/Footer.vue'

const employeeStore = useEmployeeStore()

const tableComponent = ref(null)
const employeeModalComponent = ref(null)
const deleteConfirmModal = ref(null)
let dataTable = null

const selectedFilterType = ref('')
const selectedFilterValue = ref('')
const searchQuery = ref('')
const selectedEmployee = ref(null)
const mobileCurrentPage = ref(0)
const mobilePageSize = ref(10)
const deleteTargetId = ref(null)

// Fetch employees on mount
onMounted(async () => {
  try {
    await employeeStore.fetchEmployees({ length: 10 })
  } catch (error) {
    console.error('Failed to load employees:', error)
  }
})

const selectedCount = computed(() => 0)

const filterOptions = computed(() => {
  return {
    name: employeeStore.uniqueNames,
    email: employeeStore.uniqueEmails,
    contact: employeeStore.uniqueContacts
  }
})

const deleteItemName = computed(() => {
  if (!deleteTargetId.value) return ''
  const employee = employeeStore.formattedEmployees.find(emp => emp.id === deleteTargetId.value)
  return employee?.name || 'this employee'
})

// Filter handlers
const handleFilterTypeUpdate = (type) => {
  selectedFilterType.value = type
  selectedFilterValue.value = ''
  mobileCurrentPage.value = 0
}

const handleFilterValueUpdate = async (value) => {
  selectedFilterValue.value = value
  mobileCurrentPage.value = 0
  
  try {
    if (value && selectedFilterType.value) {
      await employeeStore.filterByType(selectedFilterType.value, value)
    } else {
      await employeeStore.fetchEmployees({ length: 10 })
    }
    filterTable()
  } catch (error) {
    console.error('Filter error:', error)
  }
}

const handleSearchUpdate = async (value) => {
  searchQuery.value = value
  mobileCurrentPage.value = 0
  
  try {
    await employeeStore.searchEmployees(value)
    searchTable()
  } catch (error) {
    console.error('Search error:', error)
  }
}

const filterTable = () => {
  if (dataTable) {
    tableComponent.value?.refreshTable()
  }
}

const searchTable = () => {
  if (dataTable) {
    tableComponent.value?.refreshTable()
  }
}

// Mobile pagination
const filteredData = computed(() => {
  return employeeStore.formattedEmployees
})

const mobileDisplayData = computed(() => {
  const start = mobileCurrentPage.value * mobilePageSize.value
  const end = start + mobilePageSize.value
  return filteredData.value.slice(start, end)
})

const mobilePageInfo = computed(() => {
  const total = filteredData.value.length
  const start = total > 0 ? mobileCurrentPage.value * mobilePageSize.value + 1 : 0
  const end = Math.min(start + mobilePageSize.value - 1, total)
  const totalPages = Math.ceil(total / mobilePageSize.value)
  
  return {
    start,
    end,
    total,
    hasPrev: mobileCurrentPage.value > 0,
    hasNext: mobileCurrentPage.value < totalPages - 1
  }
})

const onTableReady = (table) => {
  dataTable = table
}

const handlePageChange = async (pageInfo) => {
  try {
    await employeeStore.fetchEmployees({
      start: pageInfo.start,
      length: pageInfo.length
    })
  } catch (error) {
    console.error('Page change error:', error)
  }
}

const handleLengthChange = async (length) => {
  try {
    await employeeStore.fetchEmployees({
      start: 0,
      length: length
    })
  } catch (error) {
    console.error('Length change error:', error)
  }
}

// Modal handlers
const openViewModal = async (id) => {
  try {
    const employee = await employeeStore.fetchEmployeeById(id)

    if (employee) {
      selectedEmployee.value = { ...employee }
      nextTick(() => {
        employeeModalComponent.value?.show()
      })
    }
  } catch (error) {
    console.error('Failed to fetch employee details:', error)
    alert('Failed to load employee details')
  }
}

const closeModal = () => {
  selectedEmployee.value = null
}

const saveEmployee = async (updatedEmployee) => {
  try {
    const formData = new FormData()
    formData.append('user_id', updatedEmployee.id)
    formData.append('name', updatedEmployee.name)
    formData.append('email', updatedEmployee.email !== '-' ? updatedEmployee.email : 'NA')
    formData.append('mobile', updatedEmployee.contact)
    formData.append('address', updatedEmployee.address)
    
    if (updatedEmployee.photoFile) {
      formData.append('photo', updatedEmployee.photoFile)
    }
    
    const result = await employeeStore.updateEmployee(formData)
    
    if (result.success) {
      alert(result.message)
      tableComponent.value?.refreshTable()
      closeModal()
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert('Failed to update employee. Please try again.')
  }
}

// Delete handlers
const handleDeleteSingle = (id) => {
  deleteTargetId.value = id
  deleteConfirmModal.value?.show()
}

const confirmDelete = async () => {
  if (!deleteTargetId.value) return
  
  try {
    const result = await employeeStore.deleteEmployees([deleteTargetId.value])
    
    if (result.success) {
      alert(result.message)
      deleteTargetId.value = null
      tableComponent.value?.refreshTable()
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert('Failed to delete employee. Please try again.')
  }
}

const openAddEmployeeModal = () => {
  // TODO: Implement add employee modal
  alert('Add Employee feature coming soon!')
}

const mobilePrevPage = () => {
  if (mobileCurrentPage.value > 0) {
    mobileCurrentPage.value--
  }
}

const mobileNextPage = () => {
  const totalPages = Math.ceil(filteredData.value.length / mobilePageSize.value)
  if (mobileCurrentPage.value < totalPages - 1) {
    mobileCurrentPage.value++
  }
}
</script>

<style scoped>
.update-inventory-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f8f9fa;
}

.container-fluid {
  flex: 1;
  padding-top: 1.5rem;
  padding-bottom: 2rem;
}

.breadcrumb-item a {
  color: #6c757d;
}

.breadcrumb-item.active {
  color: #333;
}

@media (max-width: 991px) {
  .container-fluid {
    padding-top: 1rem;
  }
}
</style>
