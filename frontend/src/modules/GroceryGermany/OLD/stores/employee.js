import { defineStore } from 'pinia'
import api from '@/config/api'

export const useEmployeeStore = defineStore('employee', {
    state: () => ({
        employees: [],
        loading: false,
        error: null,
        currentEmployee: null
    }),

    actions: {
        async addEmployee(employeeData) {
            this.loading = true
            this.error = null

            try {
                const formData = new FormData()

                // Append text fields
                formData.append('name', employeeData.name)
                formData.append('mobile', employeeData.mobile)
                formData.append('country_code', employeeData.countryCode)
                formData.append('password', employeeData.password)
                formData.append('password_confirmation', employeeData.passwordConfirmation)
                formData.append('address', employeeData.address)

                // Append photo if exists
                if (employeeData.photo) {
                    formData.append('photo', employeeData.photo)
                }

                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}add-new-user`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })

                if (data.status === 'success') {
                    this.employees.push(data.data)
                    return { success: true, data: data.data }
                }

                return { success: false, message: data.message }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to add employee'

                // Return validation errors if available
                if (error.response?.data?.data) {
                    return {
                        success: false,
                        errors: error.response.data.data,
                        message: error.response.data.message
                    }
                }

                return { success: false, message: this.error }
            } finally {
                this.loading = false
            }
        },

        clearError() {
            this.error = null
        }
    }
})
