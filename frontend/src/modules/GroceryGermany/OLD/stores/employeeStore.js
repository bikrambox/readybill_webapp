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
            type: '', // 'name', 'email', 'contact'
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
            const params = new URLSearchParams()

            params.append('draw', this.datatableParams.draw)
            params.append('start', additionalParams.start ?? this.datatableParams.start)
            params.append('length', additionalParams.length ?? this.datatableParams.length)
            params.append('search[value]', additionalParams.searchValue ?? this.datatableParams.search.value)
            params.append('search[regex]', this.datatableParams.search.regex)

            this.datatableParams.columns.forEach((col, index) => {
                params.append(`columns[${index}][data]`, col.data || '')
                params.append(`columns[${index}][name]`, '')
                params.append(`columns[${index}][searchable]`, col.searchable)
                params.append(`columns[${index}][orderable]`, col.orderable)
                params.append(`columns[${index}][search][value]`, '')
                params.append(`columns[${index}][search][regex]`, false)
            })

            this.datatableParams.order.forEach((ord, index) => {
                params.append(`order[${index}][column]`, ord.column)
                params.append(`order[${index}][dir]`, ord.dir)
            })

            if (additionalParams.filterOption) {
                params.append('filter_option', additionalParams.filterOption)
            }

            return params
        },

        async fetchEmployees(params = {}) {
            this.loading = true
            this.error = null
            this.datatableParams.draw++

            try {
                const requestParams = this.buildRequestParams(params)
                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}all-sub-users`, requestParams, {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })

                this.employees = data.data
                this.totalRecords = data.recordsTotal
                this.filteredRecords = data.recordsFiltered
                this.initialLoading = false

                return data
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch employees'
                this.initialLoading = false
                console.error('Error fetching employees:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        async searchEmployees(searchValue) {
            return await this.fetchEmployees({
                searchValue,
                start: 0
            })
        },

        async filterByType(filterType, value) {
            this.currentFilter = {
                type: filterType,
                value: value
            }

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
                const { data } = await api.get(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}delete-sub-user/${employeeIds}`, {
                })

                await this.fetchEmployees()

                return {
                    success: true,
                    message: data.message || 'Employees deleted successfully'
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete employees'
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
                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}update-sub-users`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })

                if (data.status === 'success') {
                    await this.fetchEmployees()
                }

                return {
                    success: true,
                    message: data.message || 'Employee updated successfully'
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update employee'
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
                const { data } = await api.get(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}sub-users/${id}`)

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
                this.error = error.response?.data?.message || 'Failed to fetch employee details'
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
                type: '',
                value: ''
            }
        },

        clearError() {
            this.error = null
        }
    }
})
