import { createRouter, createWebHistory } from 'vue-router';

import DashboardPage from '@/pages/DashboardPage.vue';
import LoginPage from '@/pages/LoginPage.vue';
import OperationsPage from '@/pages/OperationsPage.vue';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/',
        redirect: '/dashboard',
    },
    {
        path: '/login',
        name: 'login',
        component: LoginPage,
        meta: {
            guestOnly: true,
        },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: DashboardPage,
        meta: {
            authOnly: true,
        },
    },
    {
        path: '/operations',
        name: 'operations',
        component: OperationsPage,
        meta: {
            authOnly: true,
        },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/dashboard',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (! auth.state.initialized) {
        await auth.bootstrap();
    }

    if (to.meta.authOnly && auth.state.user === null) {
        return { name: 'login' };
    }

    if (to.meta.guestOnly && auth.state.user !== null) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
