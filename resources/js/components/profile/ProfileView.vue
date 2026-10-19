<template>
  <div class="space-y-8">
    <!-- Status Alerts -->
    <div v-if="localStatusMessage" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-800 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="font-medium leading-relaxed">{{ localStatusMessage }}</span>
      </div>
      <button type="button" @click="localStatusMessage = ''" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold p-1">&times;</button>
    </div>

    <!-- Error Alerts -->
    <div v-if="errorsList && errorsList.length > 0" class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs text-rose-700 shadow-sm">
      <div class="flex items-center gap-2 mb-2 font-bold text-rose-800">
        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Please resolve the following submission errors:</span>
      </div>
      <ul class="list-disc list-inside space-y-1 font-medium pl-1">
        <li v-for="(err, idx) in errorsList" :key="idx">{{ err }}</li>
      </ul>
    </div>

    <!-- Identity Banner Card with Avatar Upload Form -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden relative">
      <!-- Banner Image Container -->
      <div class="w-full h-48 sm:h-64 relative overflow-hidden bg-slate-900 flex items-center justify-center">
        <img :src="bannerUrl" alt="" class="absolute inset-0 w-full h-full object-cover blur-xl opacity-60 scale-110 pointer-events-none">
        <img :src="bannerUrl" alt="Profile Banner" class="relative z-10 max-w-full max-h-full object-contain drop-shadow-md">
        <div class="absolute inset-0 bg-slate-900/10 pointer-events-none z-20"></div>
      </div>

      <form method="POST" :action="routes.updateProfile" enctype="multipart/form-data">
        <input type="hidden" name="_token" :value="csrfToken">
        <input type="hidden" name="_method" value="PATCH">
        
        <input 
          ref="fileInputRef" 
          name="profile_photo" 
          type="file" 
          accept="image/jpeg,image/png,image/webp" 
          class="hidden" 
          @change="handleFileChange"
        >

        <!-- Content Container Below Banner -->
        <div class="px-6 sm:px-8 pb-6 pt-2 flex flex-col md:flex-row items-center md:items-end justify-between gap-6 relative z-10">
          <div class="flex flex-col sm:flex-row items-center sm:items-end gap-6 text-center sm:text-left">
            <!-- Avatar Container -->
            <div class="relative group shrink-0 -mt-16 sm:-mt-20 z-20">
              <img 
                v-if="currentAvatarSrc" 
                :src="currentAvatarSrc" 
                :alt="displayName"
                class="w-32 h-32 sm:w-36 sm:h-36 rounded-3xl object-cover border-4 border-white shadow-xl shadow-slate-900/15 transition group-hover:brightness-90 bg-white"
              >
              <div 
                v-else 
                class="w-32 h-32 sm:w-36 sm:h-36 rounded-3xl bg-slate-900 text-white font-extrabold text-3xl flex items-center justify-center border-4 border-white shadow-xl shadow-slate-900/15 tracking-widest uppercase"
              >
                {{ avatarInitials }}
              </div>

              <!-- Overlay Change Button -->
              <button 
                type="button" 
                @click="triggerFileInput"
                class="absolute inset-0 bg-slate-900/60 rounded-3xl opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center text-white text-xs font-bold gap-1 border-4 border-white backdrop-blur-[2px]"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Change</span>
              </button>

              <span v-if="stagedAvatarPreview" class="absolute -top-1 -right-1 bg-brand text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full shadow-md z-20">Staged</span>
            </div>

            <!-- Profile Details -->
            <div class="space-y-1 pt-2 sm:pt-4">
              <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ displayName }}</h1>
              </div>
              <p class="text-xs font-semibold text-slate-500 font-mono">{{ displayEmail }}</p>
              <p class="text-xs text-slate-400 font-medium">
                {{ employee?.position || 'Staff Member' }} • <span class="text-slate-700 font-bold">{{ employee?.department || 'General Operations' }}</span>
              </p>

              <!-- Staging Info & Direct Save Controls -->
              <div class="text-[11px] font-medium min-h-[16px] pt-1">
                <span v-if="avatarErrorMessage" class="font-bold text-rose-600 flex items-center justify-center sm:justify-start gap-1">
                  {{ avatarErrorMessage }}
                </span>
                <span v-else-if="stagedFileInfo" class="font-semibold text-emerald-600 flex items-center justify-center sm:justify-start gap-1">
                  Staged: {{ stagedFileInfo.name }} ({{ stagedFileInfo.sizeMB }} MB)
                </span>
              </div>

              <div v-if="stagedAvatarPreview" class="flex items-center gap-2 pt-1 justify-center sm:justify-start">
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  <span>Save Photo</span>
                </button>
                <button type="button" @click="cancelAvatarStaging" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-xs font-bold transition">
                  Cancel
                </button>
              </div>
            </div>
          </div>

          <!-- Status Badge -->
          <div class="flex items-center gap-2 flex-wrap justify-center">
            <span class="px-4 py-2 rounded-2xl text-xs font-bold border flex items-center gap-2 shadow-sm" :class="statusBadgeClass">
              <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
              Status: {{ employee?.employment_status || 'Probationary' }}
            </span>
          </div>
        </div>
      </form>
    </div>

    <!-- Main Workspace Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Sidebar Navigation Tabs -->
      <aside class="lg:col-span-3 bg-white rounded-3xl border border-slate-200/80 p-3 shadow-sm sticky top-24">
        <nav class="flex lg:flex-col overflow-x-auto no-scrollbar gap-1" aria-label="Tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            type="button" 
            @click="switchTab(tab.id)"
            class="w-full py-3.5 px-4 rounded-2xl font-bold text-xs transition whitespace-nowrap flex items-center gap-3"
            :class="activeTab === tab.id ? 'text-brand bg-brand-light/60' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="tab.iconSvg"></svg>
            <span>{{ tab.label }}</span>
          </button>
        </nav>
      </aside>

      <!-- Main Content Panels -->
      <div class="lg:col-span-9 space-y-6">
        <ProfileOverviewTab 
          v-show="activeTab === 'overview'"
          :employee="employee"
          :pending-profile-request="pendingProfileRequest"
          :csrf-token="csrfToken"
          :update-profile-route="routes.updateProfile"
        />

        <ProfileSecurityTab 
          v-show="activeTab === 'security'"
          :security-settings="securitySettings"
          :password-histories="passwordHistories"
          :csrf-token="csrfToken"
          :update-password-route="routes.updatePassword"
        />

        <ProfileSessionsTab 
          v-show="activeTab === 'sessions'"
          :trusted-devices="trustedDevices"
          :current-device-key="currentDeviceKey"
          :csrf-token="csrfToken"
          :routes="routes"
        />

        <ProfileSubsystemsTab 
          v-show="activeTab === 'subsystems'"
          :user-systems="userSystems"
        />

        <ProfileAuditTab 
          v-show="activeTab === 'audit'"
          :audit-logs="auditLogs"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ProfileOverviewTab from './ProfileOverviewTab.vue';
import ProfileSecurityTab from './ProfileSecurityTab.vue';
import ProfileSessionsTab from './ProfileSessionsTab.vue';
import ProfileSubsystemsTab from './ProfileSubsystemsTab.vue';
import ProfileAuditTab from './ProfileAuditTab.vue';

const props = defineProps({
  user: { type: Object, required: true },
  employee: { type: Object, default: null },
  pendingProfileRequest: { type: Object, default: null },
  statusColors: { type: Object, default: () => ({}) },
  securitySettings: { type: Object, default: () => ({}) },
  passwordHistories: { type: Array, default: () => [] },
  trustedDevices: { type: Array, default: () => [] },
  currentDeviceKey: { type: String, default: '' },
  userSystems: { type: Array, default: () => [] },
  auditLogs: { type: Array, default: () => [] },
  statusMessage: { type: String, default: '' },
  successMessage: { type: String, default: '' },
  errorsList: { type: Array, default: () => [] },
  csrfToken: { type: String, required: true },
  bannerUrl: { type: String, required: true },
  routes: {
    type: Object,
    default: () => ({
      updateProfile: '#',
      updatePassword: '',
      revokeOtherDevices: '',
      bulkRevokeDevices: '',
    }),
  },
});

const localStatusMessage = ref(props.statusMessage || props.successMessage || '');
const activeTab = ref('overview');

const fileInputRef = ref(null);
const stagedAvatarPreview = ref(null);
const stagedFileInfo = ref(null);
const avatarErrorMessage = ref('');

const tabs = [
  { id: 'overview', label: 'Employee Details', iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>' },
  { id: 'security', label: 'Password Security', iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 08-8 0v4h8z"/>' },
  { id: 'sessions', label: 'Trusted Devices', iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>' },
  { id: 'subsystems', label: 'Subsystem Access', iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>' },
  { id: 'audit', label: 'Audit History', iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' },
];

const displayName = computed(() => props.employee?.full_name || props.user.name || 'User');
const displayEmail = computed(() => props.employee?.email || props.user.email || '');

const currentAvatarSrc = computed(() => {
  if (stagedAvatarPreview.value) return stagedAvatarPreview.value;
  return props.employee?.avatar_url || null;
});

const avatarInitials = computed(() => {
  const f = props.employee?.first_name || props.user.name || 'E';
  const l = props.employee?.last_name || props.user.name || 'U';
  return (f.substring(0, 1) + l.substring(0, 1)).toUpperCase();
});

const statusBadgeClass = computed(() => {
  const st = props.employee?.employment_status;
  return props.statusColors[st] || 'bg-slate-100 text-slate-700 border-slate-200';
});

// Tab Switcher
const switchTab = (tabId) => {
  activeTab.value = tabId;
  window.location.hash = tabId;
};

const syncHashWithTab = () => {
  const hash = window.location.hash.replace('#', '').trim();
  if (!hash) return;
  const hashMapping = {
    overview: 'overview',
    security: 'security',
    password: 'security',
    sessions: 'sessions',
    devices: 'sessions',
    subsystems: 'subsystems',
    audit: 'audit',
  };
  if (hashMapping[hash]) {
    activeTab.value = hashMapping[hash];
  }
};

// Avatar File Handlers
const triggerFileInput = () => {
  if (fileInputRef.value) fileInputRef.value.click();
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) {
    cancelAvatarStaging();
    return;
  }

  const MAX_FILE_SIZE = 2 * 1024 * 1024;
  const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

  if (file.size > MAX_FILE_SIZE) {
    avatarErrorMessage.value = `File (${sizeMB} MB) exceeds maximum 2 MB limit.`;
    stagedAvatarPreview.value = null;
    stagedFileInfo.value = null;
    return;
  }

  avatarErrorMessage.value = '';
  stagedFileInfo.value = { name: file.name, sizeMB };

  const reader = new FileReader();
  reader.onload = (evt) => {
    stagedAvatarPreview.value = evt.target.result;
  };
  reader.readAsDataURL(file);
};

const cancelAvatarStaging = () => {
  if (fileInputRef.value) fileInputRef.value.value = '';
  stagedAvatarPreview.value = null;
  stagedFileInfo.value = null;
  avatarErrorMessage.value = '';
};

onMounted(() => {
  syncHashWithTab();
  window.addEventListener('hashchange', syncHashWithTab);
});
</script>