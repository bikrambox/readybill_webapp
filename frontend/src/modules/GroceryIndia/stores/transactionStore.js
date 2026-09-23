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
        filterColumn: 'invoice_number', // NEW: tracks active filter column
        selectedMonth: '',

        // Payment Status: 0 = unpaid, 1 = paid
        paymentStatus: 0,
        dateFrom: '',
        dateTo: '',

        // WebSocket state
        shopId: null,
        userId: null,
        isAdmin: null,
        echoChannel: null,
        newTransactionIds: [],
        highlightedTransactionIds: [],
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
         * Initialize WebSocket connection
         */
        async initializeWebSocket() {
            try {
                await this.fetchUserDetails();

                if (!this.shopId || this.isAdmin === null) {
                    console.warn('Cannot initialize WebSocket: shopId or isAdmin not set');
                    return;
                }

                const channelPrefix =
                    this.isAdmin == 1
                        ? `admin.transactions.${this.shopId}`
                        : `staff.transactions.${this.userId}`;

                console.log(`Initializing WebSocket for channel: ${channelPrefix}`);

                const attemptWebSocket = () => {
                    if (!window.Echo) {
                        console.log('Echo not available, retrying in 1s');
                        setTimeout(attemptWebSocket, 1000);
                        return;
                    }

                    console.log('Setting up WebSocket listeners...');

                    this.echoChannel = window.Echo.channel(channelPrefix)
                        .listen('.SaleCreated', (data) => {
                            console.log('SaleCreated event received:', data);
                            this.handleSaleCreated(data);
                        })
                        .listen('.TransactionUpdated', (data) => {
                            console.log('TransactionUpdated event received:', data);
                            this.handleTransactionUpdated(data);
                        })
                        .error((error) => {
                            console.error('WebSocket channel error:', error);
                        });

                    console.log('WebSocket listeners registered successfully');
                };

                attemptWebSocket();
            } catch (error) {
                console.error('Failed to initialize WebSocket:', error);
            }
        },

        /**
         * Fetch user details for WebSocket channel
         */
        async fetchUserDetails() {
            try {
                const { data } = await api.get('user-detail');
                const userData = data.data;

                this.shopId =
                    userData.isAdmin == 1
                        ? userData.details.shop_id
                        : userData.details.addedBy;
                this.isAdmin = userData.isAdmin;
                this.userId = userData.user_id;

                console.log(`User Details - shopId: ${this.shopId}, isAdmin: ${this.isAdmin}, userId: ${this.userId}`);
            } catch (error) {
                console.error('Failed to fetch user details:', error);
                throw error;
            }
        },

        /**
         * Handle SaleCreated event from WebSocket
         */
        handleSaleCreated(data) {
            const transactionId =
                data?.billing?.id || data?.id || data?.transaction?.id;

            if (transactionId) {
                console.log(`New transaction created: ${transactionId}`);

                this.newTransactionIds.push(String(transactionId));
                this.highlightedTransactionIds.push(String(transactionId));

                this.playNotificationSound();
                this.fetchTransactions();

                setTimeout(() => {
                    this.highlightedTransactionIds = this.highlightedTransactionIds.filter(
                        (id) => id !== String(transactionId)
                    );
                }, 3000);
            } else {
                console.warn('Transaction ID not found in SaleCreated event:', data);
            }
        },

        /**
         * Handle TransactionUpdated event from WebSocket
         */
        async handleTransactionUpdated(data) {
            const transactionId =
                data?.billing?.id ||
                data?.id ||
                data?.transaction?.id ||
                data?.transaction_id;

            if (transactionId) {
                console.log(`Transaction updated: ${transactionId}`);

                this.newTransactionIds.push(String(transactionId));
                this.highlightedTransactionIds.push(String(transactionId));

                this.playNotificationSound();

                if (this.paymentStatus === 0) {
                    const transactionIndex = this.transactions.findIndex(
                        (t) => t.rawId === transactionId
                    );
                    if (transactionIndex !== -1) {
                        console.log(`Transaction ${transactionId} will be removed from unpaid list`);
                    }
                }

                await this.fetchTransactions();

                setTimeout(() => {
                    this.highlightedTransactionIds = this.highlightedTransactionIds.filter(
                        (id) => id !== String(transactionId)
                    );
                }, 3000);
            } else {
                console.warn('Transaction ID not found in TransactionUpdated event:', data);
            }
        },

        /**
         * Play notification sound
         */
        playNotificationSound() {
            try {
                const audio = new Audio('/src/assets/sounds/bell.mp3');
                audio.play().catch((error) => {
                    console.warn('Failed to play notification sound:', error);
                });
            } catch (error) {
                console.error('Error playing notification sound:', error);
            }
        },

        /**
         * Check if transaction is newly added/updated
         */
        isNewTransaction(transactionId) {
            return this.highlightedTransactionIds.includes(String(transactionId));
        },

        /**
         * Disconnect WebSocket
         */
        disconnectWebSocket() {
            if (this.echoChannel) {
                console.log('Disconnecting WebSocket channel');
                window.Echo.leave(this.echoChannel.name);
                this.echoChannel = null;
            }
        },

        /**
         * Fetch transactions from API with current filters and pagination
         */
        async fetchTransactions() {
            this.loading = true;
            this.error = null;

            try {
                const formData = new FormData();

                formData.append('draw', '1');
                formData.append('start', this.startRecord.toString());
                formData.append('length', this.rowsPerPage.toString());
                formData.append('search[value]', this.searchQuery || '');
                formData.append('filter_option', this.filterColumn); // FIXED: dynamic column
                formData.append('payment_status', this.paymentStatus.toString());

                if (this.dateFrom && this.dateTo) {
                    formData.append('date_from', this.dateFrom);
                    formData.append('date_to', this.dateTo);
                }

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

                formData.append('order[0][column]', '4');
                formData.append('order[0][dir]', 'desc');
                formData.append('order[0][name]', '');

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}all-transactions`,
                    formData
                );

                this.transactions = data.data.map((item) => this.transformTransaction(item));
                this.totalRecords = data.recordsTotal;
                this.filteredRecords = data.recordsFiltered;

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

                this.newTransactionIds.push(String(transactionId));
                this.highlightedTransactionIds.push(String(transactionId));

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

            const subtotal = itemList.reduce((sum, item) => {
                const amount = parseFloat(item.amount || 0);
                return sum + (item.isRefund == '1' ? -amount : amount);
            }, 0);

            const tax = itemList.reduce((sum, item) => {
                const tax1 = parseFloat(item.tax1?.amount || 0);
                const tax2 = parseFloat(item.tax2?.amount || 0);
                const taxTotal = tax1 + tax2;
                return sum + (item.isRefund == '1' ? -taxTotal : taxTotal);
            }, 0);

            const calculatedTotal = subtotal;
            const customer = itemList[0]?.itemName || 'N/A';

            const quantity = itemList.reduce((sum, item) => {
                return sum + parseFloat(item.quantity || 0);
            }, 0);

            const quantityUnit = itemList[0]?.selectedUnit || 'ITEMS';

            return {
                bill_id: apiData.id,
                id: apiData.invoice_number,
                rawId: apiData.id,
                customer: customer,
                date: this.formatDate(apiData.created_at),
                quantity: quantity,
                quantityUnit: quantityUnit,
                user: apiData.user_name,
                total: calculatedTotal,
                status: apiData.payment_status === 1 ? 'paid' : 'unpaid',
                isNew: this.isNewTransaction(apiData.id),
                items: itemList.map((item) => ({
                    name: item.itemName,
                    quantity: parseFloat(item.quantity || 0),
                    unit: item.selectedUnit,
                    price: parseFloat(item.rate || 0),
                    total: parseFloat(item.amount || 0),
                    hsn: item.hsn,
                    tax1: item.tax1,
                    tax2: item.tax2,
                    isRefund: item.isRefund || '0',
                })),
                subtotal: subtotal,
                tax: Math.abs(tax),
                discount: 0,
                customer_mobile: apiData.customer_mobile,
                customer_name: apiData.customer_name,
                customer_address: apiData.customer_address,
                customer_state: apiData.customer_state,
                customer_state_code: apiData.customer_state_code,
                customer_gstin: apiData.customer_gstin,
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
            this.newTransactionIds = [];
            await this.fetchTransactions();
        },

        /**
         * Change to paid tab and fetch data
         */
        async changeTabToPaid() {
            this.paymentStatus = 1;
            this.currentPage = 1;
            this.newTransactionIds = [];
            await this.fetchTransactions();
        },

        /**
         * Update rows per page and refetch
         */
        async setRowsPerPage(rows) {
            this.rowsPerPage = rows;
            this.currentPage = 1;
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
            this.currentPage = 1;
            await this.fetchTransactions();
        },

        /**
         * Set the active filter column
         */
        setFilterColumn(column) {
            this.filterColumn = column;
            this.currentPage = 1;
        },

        /**
         * Set date range from month/year selection and refetch
         */
        async setDateRange(monthName, year) {
            const monthIndex = {
                'january': 0, 'february': 1, 'march': 2, 'april': 3,
                'may': 4, 'june': 5, 'july': 6, 'august': 7,
                'september': 8, 'october': 9, 'november': 10, 'december': 11,
            };

            const monthNum = monthIndex[monthName];
            const firstDay = `01/${String(monthNum + 1).padStart(2, '0')}/${year}`;

            const lastDayDate = new Date(year, monthNum + 1, 0);
            let lastDay = `${String(lastDayDate.getDate()).padStart(2, '0')}/${String(monthNum + 1).padStart(2, '0')}/${year}`;

            // Cap date_to to today if month end is in the future
            const today = new Date();
            const monthEndDate = new Date(year, monthNum, lastDayDate.getDate());

            if (monthEndDate > today) {
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
            this.disconnectWebSocket();
            this.transactions = [];
            this.totalSales = 0;
            this.totalRecords = 0;
            this.filteredRecords = 0;
            this.loading = false;
            this.error = null;
            this.currentPage = 1;
            this.rowsPerPage = 10;
            this.searchQuery = '';
            this.filterColumn = 'invoice_number';
            this.selectedMonth = '';
            this.paymentStatus = 0;
            this.dateFrom = '';
            this.dateTo = '';
            this.newTransactionIds = [];
            this.highlightedTransactionIds = [];
        },
    },
});