export default [
    {
        path: '/',
        name: 'Home',
        component: () => import('../views/HomePage.vue'),
        meta: {
            layout: 'MainLayout'
        }
    },

    {
        path: '/about',
        name: 'About',
        component: () => import('../views/AboutPage.vue'),
        meta: {
            layout: 'MainLayout'
        }
    },

    {
        path: '/contact',
        name: 'Contact',
        component: () => import('../views/ContactPage.vue'),
        meta: {
            layout: 'MainLayout'
        }
    },

    {
        path: '/agents',
        name: 'Agents',
        component: () => import('../views/AgentsPage.vue'),
        meta: {
            layout: 'MainLayout'
        }
    },


    {
        path: '/privacy-policy',
        name: 'PrivacyPolicy',
        component: () => import('../views/PrivacyPolicyPage.vue'),
        meta: {
            layout: 'MainLayout'
        }
    },

    {
        path: '/terms-of-use',
        name: 'TermsOfUse',
        component: () => import('../views/TermsOfUsePage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'MainLayout'
        }
    },


    {
        path: '/features',
        name: 'Features',
        component: () => import('../views/FeaturesPage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'MainLayout'
        }
    },

    {
        path: '/pricing',
        name: 'Pricing',
        component: () => import('../views/PricingPage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'MainLayout'
        }
    },

    {
        path: '/mobile-app',
        name: 'MobileApp',
        component: () => import('../views/MobileAppPage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'MainLayout'
        }
    },


    {
        path: '/integrations',
        name: 'Integrations',
        component: () => import('../views/IntegrationPage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'MainLayout'
        }
    },


    {
        path: '/career',
        name: 'Career',
        component: () => import('../views/CareerPage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'MainLayout'
        }
    },



    {
        path: '/cookie-policy',
        name: 'CookiePolicy',
        component: () => import('../views/CookiePolicyPage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'MainLayout'
        }
    },


    {
        path: '/watch-demo',
        name: 'WatchDemo',
        component: () => import('../views/WatchDemoPage.vue'),
        meta: {
            requiresAuth: false,
            layout: 'MainLayout'
        }
    },

    // {
    //     path: '/change-password',
    //     name: 'TermsOfUse',
    //     component: () => import('../views/TermsOfUsePage.vue'),
    //     meta: {
    //         requiresAuth: false,
    //         layout: 'MainLayout'
    //     }
    // },


    // {
    //     path: '/test',
    //     name: 'Test',
    //     component: () => import('../views/TestPage.vue'),
    //     meta: {
    //         layout: 'MainLayout'
    //     }
    // },

]
