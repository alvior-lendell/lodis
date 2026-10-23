import { createRouter, createWebHistory } from 'vue-router';
import { ref } from 'vue';

const pageDataElement = document.getElementById('spa-page-data');
const pageData = pageDataElement ? JSON.parse(pageDataElement.textContent) : {};

export const isRouteLoading = ref(true);

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/dashboard',
            name: 'dashboard',
            component: () => import('./components/dashboard/DashboardView.vue'),
            props: pageData,
        },
    ],
});

router.beforeEach(() => {
    isRouteLoading.value = true;
});

router.afterEach(() => {
    isRouteLoading.value = false;
});

router.onError((error) => {
    isRouteLoading.value = false;
    console.error('Router navigation failed:', error);
});
