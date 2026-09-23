import { defineStore } from 'pinia';
import api from '@/config/api';

export const useTransactionStore = defineStore('transaction', {
    state: () => ({
        transactions: [],
        totalSales: 0,
        totalRecords: 0,
        filteredRecords: 0,
        loading: false,
        error: null,

        // Pagination & Filters
        currentPage: 1,
        rowsPerPage: 10,
        searchQuery: '',
        selectedMonth: 'july-2025',

        // Payment Status: 0 = unpaid, 1 = paid
        paymentStatus: 0,
        dateFrom: '',
        dateTo: '',
    }),

    getters: {
        startRecord: (state) => (state.currentPage - 1) * state.rowsPerPage,

        endRecord: (state) =>
            Math.min(state.startRecord + state.rowsPerPage, state.filteredRecords),

        totalPages: (state) =>
            state.filteredRecords ? Math.ceil(state.filteredRecords / state.rowsPerPage) : 1,
    },

    actions: {
        /**
         * Fetch transactions from API with current filters and pagination
         */
        async fetchTransactions() {
            this.loading = true;
            this.error = null;

            try {
                const formData = new FormData();

                // DataTables format parameters
                formData.append('draw', '1');
                formData.append('start', this.startRecord.toString());
                formData.append('length', this.rowsPerPage.toString());
                formData.append('search[value]', this.searchQuery || '');
                formData.append('filter_option', 'invoice_number');
                formData.append('payment_status', this.paymentStatus.toString());


                // Add date range parameters
                if (this.dateFrom && this.dateTo) {
                    formData.append('date_from', this.dateFrom);
                    formData.append('date_to', this.dateTo);
                }

                // Column definitions (as per your API format)
                const columns = [
                    { data: 'invoice_number', searchable: true, orderable: true },
                    { data: '', searchable: true, orderable: true },
                    { data: '', searchable: true, orderable: true },
                    { data: 'user_name', searchable: true, orderable: true },
                    { data: '', searchable: true, orderable: true },
                    { data: '', searchable: true, orderable: false },
                ];

                columns.forEach((col, index) => {
                    formData.append(`columns[${index}][data]`, col.data);
                    formData.append(`columns[${index}][name]`, '');
                    formData.append(`columns[${index}][searchable]`, String(col.searchable));
                    formData.append(`columns[${index}][orderable]`, String(col.orderable));
                    formData.append(`columns[${index}][search][value]`, '');
                    formData.append(`columns[${index}][search][regex]`, 'false');
                });

                // Order by date descending (column 4)
                formData.append('order[0][column]', '4');
                formData.append('order[0][dir]', 'desc');
                formData.append('order[0][name]', '');

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}all-transactions`,
                    formData
                );

                // Transform API response to component format
                this.transactions = data.data.map((item) => this.transformTransaction(item));
                this.totalRecords = data.recordsTotal;
                this.filteredRecords = data.recordsFiltered;

                // Calculate total sales for current view
                this.calculateTotalSales();

            } catch (error) {
                this.error = error?.response?.data?.message || 'Failed to fetch transactions';
                console.error('Error fetching transactions:', error);
                this.transactions = [];
                this.totalRecords = 0;
                this.filteredRecords = 0;
                this.totalSales = 0;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Fetch single transaction details by ID
         */
        async fetchTransactionDetails(transactionId) {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await api.get(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}transaction/${transactionId}`
                );

                return this.transformTransaction(data.data);
            } catch (error) {
                this.error = error?.response?.data?.message || 'Failed to fetch transaction details';
                console.error('Error fetching transaction details:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Mark transaction as paid
         */
        async markAsPaid(transactionId) {
            this.loading = true;
            this.error = null;

            try {
                await api.get(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}transactions/${transactionId}/mark-paid`
                );

                // Refresh transactions list to reflect changes
                // Stay on unpaid tab after marking as paid
                await this.changeTabToUnpaid();

                return true;
            } catch (error) {
                this.error = error?.response?.data?.message || 'Failed to mark transaction as paid';
                console.error('Error marking transaction as paid:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Transform API data to component format
         */
        transformTransaction(apiData) {

            const itemList = JSON.parse(apiData.item_list || '[]');

            // Calculate totals
            const subtotal = itemList.reduce((sum, item) => {
                return sum + parseFloat(item.amount || 0);
            }, 0);

            const tax = itemList.reduce((sum, item) => {
                const tax1 = parseFloat(item.tax1?.amount || 0);
                const tax2 = parseFloat(item.tax2?.amount || 0);
                return sum + tax1 + tax2;
            }, 0);

            // Get first item name as customer (or you can use a different field)
            const customer = itemList[0]?.itemName || 'N/A';

            // Calculate total quantity
            const quantity = itemList.reduce((sum, item) => {
                return sum + parseFloat(item.quantity || 0);
            }, 0);

            const quantityUnit = itemList[0]?.selectedUnit || 'ITEMS';


            return {
                id: apiData.invoice_number,           // Display ID (e.g., "RB2025/182")
                rawId: apiData.id,                    // Database ID for API calls
                customer: customer,
                date: this.formatDate(apiData.created_at),
                quantity: quantity,
                quantityUnit: quantityUnit,
                user: apiData.user_name,
                total: parseFloat(apiData.total_price || 0),
                status: apiData.payment_status === 1 ? 'paid' : 'unpaid',
                items: itemList.map((item) => ({
                    name: item.itemName,
                    quantity: parseFloat(item.quantity || 0),
                    unit: item.selectedUnit,
                    price: parseFloat(item.rate || 0),
                    total: parseFloat(item.amount || 0),
                    hsn: item.hsn,
                    tax1: item.tax1,
                    tax2: item.tax2,
                })),
                subtotal: subtotal,
                tax: tax,
                discount: 0, // Add if available in your API
            };
        },

        /**
         * Format date string to DD/MM/YYYY HH:MM AM/PM
         */
        formatDate(dateString) {
            const date = new Date(dateString);

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            const hours24 = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const ampm = hours24 >= 12 ? 'PM' : 'AM';
            const hours12 = hours24 % 12 || 12;

            return `${day}/${month}/${year} ${String(hours12).padStart(2, '0')}:${minutes} ${ampm}`;
        },

        /**
         * Calculate total sales from current transactions
         */
        calculateTotalSales() {
            this.totalSales = this.transactions.reduce((sum, transaction) => {
                return sum + transaction.total;
            }, 0);
        },

        /**
         * Change to unpaid tab and fetch data
         */
        async changeTabToUnpaid() {
            this.paymentStatus = 0;
            this.currentPage = 1;
            await this.fetchTransactions();
        },

        /**
         * Change to paid tab and fetch data
         */
        async changeTabToPaid() {
            this.paymentStatus = 1;
            this.currentPage = 1;
            await this.fetchTransactions();
        },

        /**
         * Update rows per page and refetch
         */
        async setRowsPerPage(rows) {
            this.rowsPerPage = rows;
            this.currentPage = 1; // Reset to first page
            await this.fetchTransactions();
        },

        /**
         * Go to next page
         */
        async nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                await this.fetchTransactions();
            }
        },

        /**
         * Go to previous page
         */
        async prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                await this.fetchTransactions();
            }
        },

        /**
         * Apply search query and refetch
         */
        async applySearch(query) {
            this.searchQuery = query;
            this.currentPage = 1; // Reset to first page on search
            await this.fetchTransactions();
        },

        // /**
        //  * Set selected month filter
        //  */
        // setSelectedMonth(month) {
        //     this.selectedMonth = month;
        //     // Implement month-based filtering if needed
        //     // You may need to add month filtering to the API call
        // },


        // Update existing setSelectedMonth method
        setSelectedMonth(monthValue) {
            const [monthName, year] = monthValue.split('-');
            this.setDateRange(monthName, parseInt(year));
        },


        /**
        * Set date range from month/year selection
        * Format: dd/mm/yyyy
        * If month end is future date → use current date as date_to
        */
        async setDateRange(monthName, year) {
            const monthIndex = {
                'january': 0, 'february': 1, 'march': 2, 'april': 3, 'may': 4,
                'june': 5, 'july': 6, 'august': 7, 'september': 8, 'october': 9,
                'november': 10, 'december': 11
            };

            const monthNum = monthIndex[monthName];

            // First day of month: always 01/mm/yyyy
            const firstDay = `01/${String(monthNum + 1).padStart(2, '0')}/${year}`;

            // Calculate last day of month
            const lastDayDate = new Date(year, monthNum + 1, 0);
            let lastDay = `${String(lastDayDate.getDate()).padStart(2, '0')}/${String(monthNum + 1).padStart(2, '0')}/${year}`;

            // Check if month end is in future → use current date instead
            const today = new Date();
            const monthEndDate = new Date(year, monthNum, lastDayDate.getDate());

            if (monthEndDate > today) {
                // Use current date as date_to (dd/mm/yyyy)
                const currentDay = String(today.getDate()).padStart(2, '0');
                const currentMonth = String(today.getMonth() + 1).padStart(2, '0');
                const currentYear = today.getFullYear();
                lastDay = `${currentDay}/${currentMonth}/${currentYear}`;
            }

            this.dateFrom = firstDay;
            this.dateTo = lastDay;
            this.currentPage = 1;
            await this.fetchTransactions();
        },


        /**
         * Reset store to initial state
         */
        resetStore() {
            this.transactions = [];
            this.totalSales = 0;
            this.totalRecords = 0;
            this.filteredRecords = 0;
            this.loading = false;
            this.error = null;
            this.currentPage = 1;
            this.rowsPerPage = 10;
            this.searchQuery = '';
            this.selectedMonth = 'july-2025';
            this.paymentStatus = 0;
            this.dateFrom = '';
            this.dateTo = '';
        },
    },
});