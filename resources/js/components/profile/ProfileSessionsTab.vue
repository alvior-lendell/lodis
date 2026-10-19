<template>
  <div class="space-y-6">
    <!-- Trusted Devices Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
      <!-- Card Header with Bulk Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
          <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Recognized & Trusted Devices</h2>
          <p class="text-xs text-slate-500 mt-0.5">Registered devices authorized to access your profile without triggering security warnings.</p>
        </div>

        <div class="flex items-center gap-2">
          <!-- Bulk Delete Selected Button -->
          <button 
            v-if="selectedDeviceIds.length > 0"
            type="button" 
            @click="promptBulkForget"
            class="px-3.5 py-2 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            <span>Forget Selected ({{ selectedDeviceIds.length }})</span>
          </button>

          <!-- Forget All Other Devices Button -->
          <button 
            v-if="otherDevicesCount > 0 && routes?.revokeOtherDevices" 
            type="button" 
            @click="promptForgetOthers"
            class="px-4 py-2 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 text-xs font-bold transition flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            <span>Forget All Other Devices</span>
          </button>
        </div>
      </div>

      <!-- Hidden Bulk Deletion Form -->
      <form ref="bulkFormRef" method="POST" :action="(routes?.bulkRevokeDevices || '#') + '#sessions'" class="hidden">
        <input type="hidden" name="_token" :value="csrfToken">
        <input v-for="id in selectedDeviceIds" :key="id" type="hidden" name="device_ids[]" :value="id">
      </form>

      <!-- Hidden Revoke Others Form -->
      <form ref="revokeOthersFormRef" method="POST" :action="(routes?.revokeOtherDevices || '#') + '#sessions'" class="hidden">
        <input type="hidden" name="_token" :value="csrfToken">
        <input type="hidden" name="_method" value="DELETE">
      </form>

      <!-- Device List Table/Items -->
      <div class="border border-slate-200/80 rounded-2xl overflow-hidden divide-y divide-slate-100">
        <!-- Select All Row -->
        <div v-if="otherDevicesCount > 1" class="px-5 py-2.5 bg-slate-50 flex items-center justify-between text-xs font-semibold text-slate-600 border-b border-slate-100">
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input 
              type="checkbox" 
              :checked="isAllSelected" 
              @change="toggleSelectAll" 
              class="rounded border-slate-300 text-brand focus:ring-brand/20 w-4 h-4"
            >
            <span>Select All Other Devices ({{ otherDevicesCount }})</span>
          </label>
        </div>

        <!-- Individual Device Item -->
        <div v-for="dev in trustedDevices" :key="dev.id" class="p-5 flex items-center justify-between gap-4 bg-white hover:bg-slate-50/50 transition">
          <div class="flex items-center gap-4">
            <!-- Checkbox for non-current devices -->
            <input 
              v-if="dev.device_key !== currentDeviceKey" 
              type="checkbox" 
              :value="dev.id" 
              v-model="selectedDeviceIds" 
              class="rounded border-slate-300 text-brand focus:ring-brand/20 w-4 h-4 shrink-0"
            >
            <div v-else class="w-4 h-4 shrink-0"></div>

            <div class="p-3 rounded-2xl shrink-0" :class="dev.device_key === currentDeviceKey ? 'bg-brand-light text-brand' : 'bg-slate-100 text-slate-500'">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h4 class="text-xs font-bold text-slate-900">{{ dev.device_name }}</h4>
                <span v-if="dev.device_key === currentDeviceKey" class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 uppercase tracking-wider">This Device</span>
              </div>
              <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                IP: {{ dev.ip_address }} • <span class="text-slate-400">Last active {{ dev.last_active_human }}</span>
              </p>
            </div>
          </div>

          <!-- Single Device Revoke Button -->
          <button 
            v-if="dev.device_key !== currentDeviceKey && dev.revoke_url"
            type="button" 
            @click="promptSingleForget(dev)"
            class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-xs font-bold border border-slate-200 hover:border-rose-200 transition"
          >
            Forget Device
          </button>
        </div>

        <div v-if="trustedDevices.length === 0" class="p-8 text-center bg-slate-50 space-y-2">
          <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          <h4 class="text-xs font-bold text-slate-700">No Trusted Devices Logged</h4>
          <p class="text-[11px] text-slate-500">Sign in from your standard workstation to register it as a trusted device.</p>
        </div>
      </div>
    </div>

    <!-- Custom Tailwind Confirmation Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div 
          v-if="modal.isOpen" 
          class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
          @keydown.escape="closeModal"
          tabindex="0"
        >
          <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-start gap-4">
              <div class="p-3 rounded-2xl bg-rose-100 text-rose-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-base font-extrabold text-slate-900">{{ modal.title }}</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ modal.message }}</p>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <button 
                type="button" 
                @click="closeModal"
                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
              >
                Cancel
              </button>
              <button 
                type="button" 
                @click="confirmModalAction"
                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition"
              >
                Confirm Revocation
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, reactive, nextTick } from 'vue';

const props = defineProps({
  trustedDevices: { type: Array, default: () => [] },
  currentDeviceKey: { type: String, default: '' },
  csrfToken: { type: String, required: true },
  routes: { type: Object, default: () => ({}) },
});

const selectedDeviceIds = ref([]);
const bulkFormRef = ref(null);
const revokeOthersFormRef = ref(null);

// Reactive Modal State
const modal = reactive({
  isOpen: false,
  title: '',
  message: '',
  action: null,
});

const revocableDevices = computed(() => {
  return props.trustedDevices.filter(d => d.device_key !== props.currentDeviceKey);
});

const otherDevicesCount = computed(() => revocableDevices.value.length);

const isAllSelected = computed(() => {
  return otherDevicesCount.value > 0 && selectedDeviceIds.value.length === otherDevicesCount.value;
});

const toggleSelectAll = (e) => {
  selectedDeviceIds.value = e.target.checked ? revocableDevices.value.map(d => d.id) : [];
};

// Modal Handlers
const closeModal = () => {
  modal.isOpen = false;
  modal.action = null;
};

const confirmModalAction = () => {
  if (typeof modal.action === 'function') {
    modal.action();
  }
  closeModal();
};

const promptSingleForget = (device) => {
  modal.title = 'Forget Trusted Device?';
  modal.message = `Are you sure you want to remove "${device.device_name}"? Logins from this device will trigger security notifications.`;
  modal.action = () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = device.revoke_url;
    form.innerHTML = `
      <input type="hidden" name="_token" value="${props.csrfToken}">
      <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
  };
  modal.isOpen = true;
};

const promptBulkForget = () => {
  modal.title = `Forget ${selectedDeviceIds.value.length} Selected Device(s)?`;
  modal.message = `Are you sure you want to remove ${selectedDeviceIds.value.length} selected device(s) from your trusted list?`;
  modal.action = () => {
    if (bulkFormRef.value) bulkFormRef.value.submit();
  };
  modal.isOpen = true;
};

const promptForgetOthers = () => {
  modal.title = 'Forget All Other Devices?';
  modal.message = 'Are you sure you want to remove ALL other trusted devices? Future sign-ins from those devices will require re-authorization.';
  modal.action = () => {
    if (revokeOthersFormRef.value) revokeOthersFormRef.value.submit();
  };
  modal.isOpen = true;
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>