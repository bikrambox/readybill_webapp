// stores/employeeStore.js
import { defineStore } from 'pinia'
import api from '@/config/api'

export const useEmployeeStore = defineStore('employee', {
    state: () => ({
        employees: [],
        loading: false,
        initialLoading: true,
        error: null,
        datatableParams: {
            draw: 0,
            start: 0,
            length: 10,
            search: {
                value: '',
                regex: false
            },
            order: [{
                column: 0,
                dir: 'asc'
            }],
            columns: [
                { data: 'name', searchable: true, orderable: true },
                { data: 'email', searchable: true, orderable: true },
                { data: 'mobile', searchable: true, orderable: true },
                { data: 'photo', searchable: false, orderable: false },
                { data: null, searchable: false, orderable: false }
            ]
        },
        totalRecords: 0,
        filteredRecords: 0,
        currentFilter: {
            type: 'name', // Default filter type
            value: ''
        }
    }),

    getters: {
        formattedEmployees: (state) => {
            return state.employees.map(emp => ({
                id: emp.user_id,
                name: emp.name,
                email: emp.email === 'NA' ? '-' : emp.email,
                contact: emp.mobile,
                photo: emp.photo !== 'NA' ? `${emp.photo_url}` : null,
                address: emp.address,
                staff_id: emp.staff_id,
                active: emp.active,
                created_at: emp.created_at,
                last_logged_in: emp.last_logged_in
            }))
        },

        uniqueNames: (state) => {
            return [...new Set(state.employees.map(emp => emp.name).filter(name => name))]
        },

        uniqueEmails: (state) => {
            return [...new Set(state.employees.map(emp => emp.email).filter(email => email && email !== 'NA'))]
        },

        uniqueContacts: (state) => {
            return [...new Set(state.employees.map(emp => emp.mobile).filter(mobile => mobile))]
        }
    },

    actions: {
        buildRequestParams(additionalParams = {}) {
            console.log('📦 Building FormData with params:', additionalParams);

            const formData = new FormData()

            // Add basic DataTables parameters
            formData.append('draw', this.datatableParams.draw)
            formData.append('start', additionalParams.start ?? this.datatableParams.start)
            formData.append('length', additionalParams.length ?? this.datatableParams.length)

            // Add search value
            const searchValue = additionalParams.searchValue ?? this.datatableParams.search.value
            formData.append('search[value]', searchValue)
            formData.append('search[regex]', this.datatableParams.search.regex)

            // Add columns configuration
            this.datatableParams.columns.forEach((col, index) => {
                formData.append(`columns[${index}][data]`, col.data || '')
                formData.append(`columns[${index}][name]`, '')
                formData.append(`columns[${index}][searchable]`, col.searchable)
                formData.append(`columns[${index}][orderable]`, col.orderable)
                formData.append(`columns[${index}][search][value]`, '')
                formData.append(`columns[${index}][search][regex]`, false)
            })

            // Add order configuration
            this.datatableParams.order.forEach((ord, index) => {
                formData.append(`order[${index}][column]`, ord.column)
                formData.append(`order[${index}][dir]`, ord.dir)
            })

            // **CRITICAL FIX: Add filter_option field**
            const filterOption = additionalParams.filterOption || this.currentFilter.type
            if (filterOption) {
                formData.append('filter_option', filterOption)
                console.log('✅ filter_option added:', filterOption)
            }

            // Debug: Log FormData contents
            console.log('📋 FormData contents:')
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`)
            }

            return formData
        },

        async fetchEmployees(params = {}) {
            this.loading = true
            this.error = null
            this.datatableParams.draw++

            try {
                const formData = this.buildRequestParams(params)

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}all-sub-users`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                )

                this.employees = data.data ?? []
                this.totalRecords = data.recordsTotal
                this.filteredRecords = data.recordsFiltered
                this.initialLoading = false

                console.log('✅ Fetch successful:', {
                    employees: this.employees.length,
                    totalRecords: this.totalRecords,
                    filteredRecords: this.filteredRecords,
                    currentFilter: this.currentFilter
                })

                return data
            } catch (error) {
                this.error = error.response?.data?.message || this.$t('common.Failed to fetch employees')
                this.initialLoading = false
                console.error('❌ Error fetching employees:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        async searchEmployees(searchValue) {
            console.log('🔍 Searching employees:', searchValue)

            // Update search value in state
            this.datatableParams.search.value = searchValue
            this.datatableParams.start = 0
            this.currentFilter.value = searchValue

            return await this.fetchEmployees({
                searchValue: searchValue,
                start: 0,
                filterOption: this.currentFilter.type
            })
        },

        async filterByType(filterType, value) {
            console.log('🎯 Filtering by type:', { filterType, value })

            // Update filter state
            this.currentFilter = {
                type: filterType,
                value: value
            }
            this.datatableParams.search.value = value
            this.datatableParams.start = 0

            return await this.fetchEmployees({
                filterOption: filterType,
                searchValue: value,
                start: 0
            })
        },

        async deleteEmployees(employeeIds) {
            this.loading = true
            this.error = null

            try {
                const { data } = await api.get(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}delete-sub-user/${employeeIds}`
                )

                // Refresh with current filters maintained
                await this.fetchEmployees({
                    filterOption: this.currentFilter.type,
                    searchValue: this.currentFilter.value
                })

                return {
                    success: true,
                    message: data.message || this.$t('common.Employees deleted successfully')
                }
            } catch (error) {
                this.error = error.response?.data?.message || this.$t('common.Failed to delete employees')
                console.error('Error deleting employees:', error)
                return {
                    success: false,
                    message: this.error
                }
            } finally {
                this.loading = false
            }
        },

        async updateEmployee(formData) {
            this.loading = true
            this.error = null

            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}update-sub-users`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                )

                if (data.status === 'success') {
                    // Refresh with current filters maintained
                    await this.fetchEmployees({
                        filterOption: this.currentFilter.type,
                        searchValue: this.currentFilter.value
                    })
                }

                return {
                    success: true,
                    message: data.message || this.$t('common.Employee updated successfully')
                }
            } catch (error) {
                this.error = error.response?.data?.message || this.$t('common.Failed to update employee')
                console.error('Error updating employee:', error)
                return {
                    success: false,
                    message: this.error,
                    errors: error.response?.data?.errors || {}
                }
            } finally {
                this.loading = false
            }
        },

        async fetchEmployeeById(id) {
            this.loading = true
            this.error = null

            try {
                const { data } = await api.get(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}sub-users/${id}`
                )

                console.log('fetchEmployeeById', data);

                if (data.status === 'success') {
                    const staff = data.staff
                    const user = staff.user

                    return {
                        id: user.user_id,
                        staff_id: staff.staff_id,
                        name: staff.name,
                        email: staff.email === 'NA' ? '-' : staff.email,
                        contact: user.mobile,
                        countryCode: user.country_details?.dial_code || '',
                        address: staff.address,
                        photo: staff.photo_url || null,
                        active: user.active,
                        created_at: staff.created_at,
                        last_logged_in: user.last_logged_in,
                        country: user.country_details?.name || ''
                    }
                }
                return null
            } catch (error) {
                this.error = error.response?.data?.message || this.$t('common.Failed to fetch employee details')
                console.error('Error fetching employee by ID:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        updatePagination(start, length) {
            this.datatableParams.start = start
            this.datatableParams.length = length
        },

        resetFilters() {
            this.datatableParams.search.value = ''
            this.datatableParams.start = 0
            this.currentFilter = {
                type: 'name',
                value: ''
            }
        },

        clearError() {
            this.error = null
        }
    }
})
