export default [

    {
        path: '/sell',
        name: 'QuickSell',
        component: () => import('../views/QuickSellPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },

    {
        path: '/refund',
        name: 'Refund',
        component: () => import('../views/RefundPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },

    {
        path: '/add-inventory',
        name: 'AddInventory',
        component: () => import('../views/AddInventoryPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },

    {
        path: '/upload-data',
        name: 'UploadData',
        component: () => import('../views/UploadDataPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
            layout: 'dashboard'
        }
    },

    {
        path: '/how-to-upload',
        name: 'DataUploadInstruction',
        component: () => import('../views/DataUploadInstructionPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
            layout: 'dashboard'
        }
    },

    {
        path: '/inventory',
        name: 'InventoryList',
        component: () => import('../views/UpdateInventoryPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },


    {
        path: '/transactions',
        name: 'Transactions',
        component: () => import('../views/TransactionsPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },

    {
        path: '/generate-report',
        name: 'GenerateReport',
        component: () => import('../views/GenerateReportPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
            layout: 'dashboard'
        }
    },

    {
        path: '/add-employee',
        name: 'AddEmployee',
        component: () => import('../views/AddEmployeePage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
            layout: 'dashboard'
        }
    },

    {
        path: '/employees',
        name: 'Employees',
        component: () => import('../views/EmployeesPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
            layout: 'dashboard'
        }
    },

    {
        path: '/setting',
        name: 'Setting',
        component: () => import('../views/SettingPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
            layout: 'dashboard'
        }
    },

    {
        path: '/profile',
        name: 'Profile',
        component: () => import('../views/ProfilePage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },


    {
        path: '/change-password',
        name: 'ChangePassword',
        component: () => import('@/modules/Authentication/views/ChangePasswordPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },

    {
        path: '/support',
        name: 'Support',
        component: () => import('../views/SupportPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },

    {
        path: '/dataset',
        name: 'Dataset',
        component: () => import('../views/DatasetPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
            layout: 'dashboard'
        }
    },

    {
        path: '/subscription',
        name: 'Subscription',
        component: () => import('../views/SubscriptionPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            layout: 'dashboard'
        }
    },


    {
        path: '/notifications',
        name: 'Notifications',
        component: () => import('../views/NotificationsPage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            // title: 'Notifications'
            layout: 'dashboard'
        }
    },


    {
        path: '/print/invoice/:bill_id',
        name: 'Invoice',
        component: () => import('../views/InvoicePage.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: false,
            // title: 'Notifications'
            // layout: 'dashboard'
        }
    }


];
