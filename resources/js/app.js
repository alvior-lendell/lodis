import Alpine from 'alpinejs';
import { createApp, defineAsyncComponent } from 'vue';

window.Alpine = Alpine;
Alpine.start();

import './echo';
import './it-chatbot';

const app = createApp({});

// Dynamic Async Registration (Code-Splitting)
app.component('login-form', defineAsyncComponent(() => import('./components/auth/LoginForm.vue')));
app.component('register-form', defineAsyncComponent(() => import('./components/auth/RegisterForm.vue')));
app.component('forgot-password-form', defineAsyncComponent(() => import('./components/auth/ForgotPasswordForm.vue')));
app.component('reset-password-form', defineAsyncComponent(() => import('./components/auth/ResetPasswordForm.vue')));
app.component('verify-method-select-form', defineAsyncComponent(() => import('./components/auth/VerifyMethodSelectForm.vue')));
app.component('verify-otp-form', defineAsyncComponent(() => import('./components/auth/VerifyOtpForm.vue')));
app.component('expired-password-form', defineAsyncComponent(() => import('./components/auth/ExpiredPasswordForm.vue')));
app.component('waiting-authorization-view', defineAsyncComponent(() => import('./components/auth/WaitingAuthorizationView.vue')));
app.component('dashboard-view', defineAsyncComponent(() => import('./components/dashboard/DashboardView.vue')));
app.component('notification-center-view', defineAsyncComponent(() => import('./components/setting/NotificationCenterView.vue')));
app.component('profile-view', defineAsyncComponent(() => import('./components/profile/ProfileView.vue')));
app.component('authorize-device-view', defineAsyncComponent(() => import('./components/auth/AuthorizeDeviceView.vue')));
app.component('device-verification-view', defineAsyncComponent(() => import('./components/setting/DeviceVerificationView.vue')));
app.component('header-navigation', defineAsyncComponent(() => import('./components/HeaderNavigation.vue')));
app.component('system-slideshow', defineAsyncComponent(() => import('./components/system/SystemSlideshow.vue')));
app.component('user-management-view', defineAsyncComponent(() => import('./components/user/UserManagementView.vue')));
app.component('user-edit-view', defineAsyncComponent(() => import('./components/user/UserEditView.vue')));
app.component('user-create-view', defineAsyncComponent(() => import('./components/user/UserCreateView.vue')));
app.component('holiday-calendar-view', defineAsyncComponent(() => import('./components/setting/HolidayCalendarView.vue')));
app.component('system-management-view', defineAsyncComponent(() => import('./components/system/SystemManagementView.vue')));
app.component('system-create-view', defineAsyncComponent(() => import('./components/system/SystemCreateView.vue')));
app.component('system-edit-view', defineAsyncComponent(() => import('./components/system/SystemEditView.vue')));
app.component('user-system-access', defineAsyncComponent(() => import('./components/user/UserSystemAccess.vue')));
app.component('otp-logs-view', defineAsyncComponent(() => import('./components/setting/OtpLogsView.vue')));

if (document.getElementById('app')) {
    app.mount('#app');
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.error('Service Worker registration failed:', error);
        });
    });
}