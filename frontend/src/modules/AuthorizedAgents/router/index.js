export default [

    {
        path: '/authorized-agents',
        name: 'AuthorizeAgents',
        component: () => import('../views/LoginPage.vue'),
        meta: {
            layout: '',
            agentGuest: true
        }
    },
    
    {
        path: '/authorized-agents/register',
        name: 'AuthorizeAgentsRegister',
        component: () => import('../views/RegisterPage.vue'),
        meta: {
            layout: '',
            agentGuest: true
        }
    },

    {
        path: '/authorized-agents/login',
        name: 'AuthorizeAgentsLogin',
        component: () => import('../views/LoginPage.vue'),
        meta: {
            layout: '',
            agentGuest: true 
        }
    },


    {
        path: '/authorized-agents/dashboard',
        name: 'AuthorizeAgentsDashboard',
        // component: () => import('../views/Dasbhoard.vue'),
        component: () => import('../views/MakeSubscription.vue'),
        meta: {
            layout: 'agentDashboard',
            requiresAgentAuth: true  
        }
    },


    {
        path: '/authorized-agents/make-subscription',
        name: 'AuthorizeAgentsMakeSubscription',
        component: () => import('../views/MakeSubscription.vue'),
        meta: {
            layout: 'agentDashboard',
            requiresAgentAuth: true  
        }
    },


    {
        path: '/authorized-agents/earnings',
        name: 'AuthorizeAgentsEarnings',
        component: () => import('../views/MyEarnings.vue'),
        meta: {
            layout: 'agentDashboard',
            requiresAgentAuth: true  
        }
    },


    {
        path: '/authorized-agents/profile',
        name: 'AuthorizeAgentsProfile',
        component: () => import('../views/ProfilePage.vue'),
        meta: {
            layout: 'agentDashboard',
            requiresAgentAuth: true  
        }
    },


    {
        path: '/authorized-agents/change-password',
        name: 'AuthorizeAgentsChangePasswordPage',
        component: () => import('../views/ChangePasswordPage.vue'),
        meta: {
            layout: 'agentDashboard',
            requiresAgentAuth: true  
        }
    },

]
