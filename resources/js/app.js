import Alpine from 'alpinejs';
import { createApp, defineAsyncComponent, defineComponent, h } from 'vue';
import { router } from './router';
import RouterPageOutlet from './components/RouterPageOutlet.vue';
import ComponentLoadingSkeleton from './components/ComponentLoadingSkeleton.vue';

window.Alpine = Alpine;
Alpine.start();

import './echo';
import './it-chatbot';

const app = createApp({});

const asyncComponent = (loader, variant = 'content') => defineAsyncComponent({
    loader,
    loadingComponent: defineComponent({
        setup() {
            return () => h(ComponentLoadingSkeleton, { variant });
        },
    }),
    delay: 0,
});

app.component('router-page-outlet', RouterPageOutlet);
app.component('login-form', asyncComponent(() => import('./components/auth/LoginForm.vue'), 'form'));
app.component('register-form', asyncComponent(() => import('./components/auth/RegisterForm.vue'), 'form'));
app.component('forgot-password-form', asyncComponent(() => import('./components/auth/ForgotPasswordForm.vue'), 'form'));
app.component('reset-password-form', asyncComponent(() => import('./components/auth/ResetPasswordForm.vue'), 'form'));
app.component('verify-method-select-form', asyncComponent(() => import('./components/auth/VerifyMethodSelectForm.vue'), 'form'));
app.component('verify-otp-form', asyncComponent(() => import('./components/auth/VerifyOtpForm.vue'), 'form'));
app.component('expired-password-form', asyncComponent(() => import('./components/auth/ExpiredPasswordForm.vue'), 'form'));
app.component('waiting-authorization-view', asyncComponent(() => import('./components/auth/WaitingAuthorizationView.vue'), 'form'));
app.component('dashboard-view', asyncComponent(() => import('./components/dashboard/DashboardView.vue')));
app.component('notification-center-view', asyncComponent(() => import('./components/setting/NotificationCenterView.vue')));
app.component('profile-view', asyncComponent(() => import('./components/profile/ProfileView.vue')));
app.component('authorize-device-view', asyncComponent(() => import('./components/auth/AuthorizeDeviceView.vue'), 'form'));
app.component('device-verification-view', asyncComponent(() => import('./components/setting/DeviceVerificationView.vue')));
app.component('header-navigation', asyncComponent(() => import('./components/HeaderNavigation.vue'), 'navigation'));
app.component('system-slideshow', asyncComponent(() => import('./components/system/SystemSlideshow.vue'), 'slideshow'));
app.component('user-management-view', asyncComponent(() => import('./components/user/UserManagementView.vue'), 'table'));
app.component('user-edit-view', asyncComponent(() => import('./components/user/UserEditView.vue'), 'form'));
app.component('user-create-view', asyncComponent(() => import('./components/user/UserCreateView.vue'), 'form'));
app.component('holiday-calendar-view', asyncComponent(() => import('./components/setting/HolidayCalendarView.vue'), 'table'));
app.component('system-management-view', asyncComponent(() => import('./components/system/SystemManagementView.vue'), 'table'));
app.component('system-create-view', asyncComponent(() => import('./components/system/SystemCreateView.vue'), 'form'));
app.component('system-edit-view', asyncComponent(() => import('./components/system/SystemEditView.vue'), 'form'));
app.component('user-system-access', asyncComponent(() => import('./components/user/UserSystemAccess.vue'), 'content'));
app.component('otp-logs-view', asyncComponent(() => import('./components/setting/OtpLogsView.vue'), 'content'));

const appRoot = document.getElementById('app');

if (appRoot) {
    if (appRoot.hasAttribute('data-router-app')) {
        app.use(router);
    }

    app.mount('#app');
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.error('Service Worker registration failed:', error);
        });
    });
}