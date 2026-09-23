export default [
    {
        path: '/register',
        name: 'Register',
        component: () => import('../views/RegisterPage.vue'),
        meta: {
            // layout: 'MainLayout'
            guest: true
        }
    },

    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/LoginPage.vue'),
        meta: {
            layout: '',
            guest: true
        }
    },

    {
        path: '/grocery/change-password',
        name: 'ChnagePassword',
        component: () => import('../views/ChangePasswordPage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'dashboard'
        }
    },
]
