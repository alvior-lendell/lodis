<template>
  <div class="max-w-md w-full p-8 bg-white rounded-3xl border border-slate-100 text-center space-y-6 shadow-2xl relative overflow-hidden font-sans">
    
    <!-- State 1: Request Expired -->
    <div v-if="isExpired" class="space-y-5 py-4 animate-fade-in">
      <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto border border-rose-100 shadow-inner">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <div class="space-y-1">
        <h2 class="text-xl font-bold text-slate-900">Authorization Expired</h2>
        <p class="text-xs text-slate-500 max-w-xs mx-auto">This verification session timed out for security. Please return to sign in and try again.</p>
      </div>
      <a :href="routes.login" class="inline-flex items-center justify-center w-full py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-lg shadow-slate-900/10 transition-all active:scale-[0.98]">
        Return to Sign In
      </a>
    </div>

    <!-- State 2: Active Request -->
    <template v-else>
      <!-- Countdown Pill Badge -->
      <div v-if="timeLeft !== null" class="absolute top-5 right-5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100/80 backdrop-blur text-[11px] font-semibold text-slate-600 border border-slate-200/50">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ formattedTimeLeft }}</span>
      </div>

      <!-- Primary View: Trusted Device Verification -->
      <div v-if="hasTrustedDevices" class="space-y-6 pt-2">
        <!-- Hero Device Graphic with Pulse Ripples -->
        <div class="relative w-24 h-24 mx-auto flex items-center justify-center">
          <span class="absolute inline-flex h-full w-full rounded-full bg-brand-light/60 animate-ping opacity-75"></span>
          <span class="absolute inline-flex h-20 w-20 rounded-full bg-brand-light/80 animate-pulse"></span>
          <div class="relative w-16 h-16 bg-brand text-white rounded-2xl shadow-xl shadow-brand/20 flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
        </div>

        <div class="space-y-2">
          <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Check Your Device</h2>
          <p class="text-xs text-slate-500 leading-relaxed max-w-xs mx-auto">
            We sent a push prompt to your active device. Tap <span class="font-bold text-slate-800">Approve</span> on your logged-in device to authorize this session.
          </p>
        </div>

        <!-- Waiting Status Pill -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-[11px] font-medium text-brand">
          <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
          Waiting for mobile approval...
        </div>

        <!-- Fallback Trigger -->
        <div class="pt-4 border-t border-slate-100">
          <button 
            type="button" 
            @click="isModalOpen = true" 
            class="text-xs font-semibold text-slate-500 hover:text-brand transition-colors inline-flex items-center gap-1 group"
          >
            <span>Can't access your device?</span>
            <span class="underline decoration-slate-300 group-hover:decoration-brand underline-offset-4">Try other methods</span>
          </button>
        </div>
      </div>

      <!-- Primary View Fallback: Workstation Unrecognized (No Trusted Devices) -->
      <div v-else class="space-y-6 pt-2">
        <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto border border-amber-100 shadow-sm">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <div class="space-y-1">
          <h2 class="text-xl font-bold text-slate-900">Workstation Verification</h2>
          <p class="text-xs text-slate-500">Select a security channel to authorize this unrecognized system.</p>
        </div>
        <button 
          type="button" 
          @click="isModalOpen = true" 
          class="w-full py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-all shadow-md"
        >
          Select Verification Method
        </button>
      </div>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 text-left flex items-start gap-2">
        <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Hidden Form Auto-Submitted via Echo Event -->
      <form ref="finalizeFormRef" method="POST" :action="routes.finalize" class="hidden">
        <input type="hidden" name="_token" :value="csrfToken">
      </form>
    </template>

    <!-- Modal Drawer: Alternative Verification Methods -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm">
          <!-- Backdrop click to close -->
          <div class="fixed inset-0" @click="isModalOpen = false"></div>

          <!-- Modal Card -->
          <div class="relative w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl p-6 text-left shadow-2xl border border-slate-100 z-10 space-y-5 animate-slide-up sm:animate-none">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div>
                <h3 class="text-base font-bold text-slate-900">Alternative Verification</h3>
                <p class="text-xs text-slate-500">Choose another security method to complete login</p>
              </div>
              <button 
                type="button" 
                @click="isModalOpen = false"
                class="p-1 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Verification Options List -->
            <div class="space-y-3">
              <!-- Option 1: Email OTP -->
              <form method="POST" :action="routes.startVerification">
                <input type="hidden" name="_token" :value="csrfToken">
                <input type="hidden" name="method" value="email">
                <button type="submit" class="w-full p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 border border-slate-200/70 transition flex items-center justify-between group">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200/80 flex items-center justify-center text-slate-600 group-hover:border-brand group-hover:text-brand transition-colors shadow-sm">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <div class="text-left">
                      <div class="text-xs font-bold text-slate-800">Email OTP Code</div>
                      <div class="text-[11px] text-slate-400">Receive a 6-digit code via email</div>
                    </div>
                  </div>
                  <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
              </form>

              <!-- Option 2: SMS Mobile Match -->
              <form v-if="hasSms" method="POST" :action="routes.startVerification">
                <input type="hidden" name="_token" :value="csrfToken">
                <input type="hidden" name="method" value="sms">
                <button type="submit" class="w-full p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 border border-slate-200/70 transition flex items-center justify-between group">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200/80 flex items-center justify-center text-slate-600 group-hover:border-brand group-hover:text-brand transition-colors shadow-sm">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <div class="text-left">
                      <div class="text-xs font-bold text-slate-800">Confirm Registered Mobile</div>
                      <div class="text-[11px] text-slate-400">Send confirmation SMS to your phone</div>
                    </div>
                  </div>
                  <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
              </form>

              <!-- Option 3: Authenticator App (TOTP) -->
              <form v-if="hasAuthenticator" method="POST" :action="routes.startVerification">
                <input type="hidden" name="_token" :value="csrfToken">
                <input type="hidden" name="method" value="authenticator">
                <button type="submit" class="w-full p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 border border-slate-200/70 transition flex items-center justify-between group">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200/80 flex items-center justify-center text-slate-600 group-hover:border-brand group-hover:text-brand transition-colors shadow-sm">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                      </svg>
                    </div>
                    <div class="text-left">
                      <div class="text-xs font-bold text-slate-800">Authenticator App</div>
                      <div class="text-[11px] text-slate-400">Use 2FA code from Google/Authy</div>
                    </div>
                  </div>
                  <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
              </form>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  authId: { type: [Number, String], required: true },
  expiresAt: { type: String, default: null },
  hasTrustedDevices: { type: Boolean, default: false },
  hasSms: { type: Boolean, default: false },
  hasAuthenticator: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
  csrfToken: { type: String, required: true },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      finalize: '#',
      startVerification: '#',
      login: '#',
    }),
  },
});

const finalizeFormRef = ref(null);
const timeLeft = ref(null);
const isExpired = ref(false);
const isModalOpen = ref(false);
let timerInterval = null;

const formattedTimeLeft = computed(() => {
  if (timeLeft.value === null || timeLeft.value <= 0) return '00:00';
  const minutes = Math.floor(timeLeft.value / 60);
  const seconds = timeLeft.value % 60;
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

const updateTimer = () => {
  if (!props.expiresAt) return;

  const expirationTime = new Date(props.expiresAt).getTime();
  const now = new Date().getTime();
  const diffInSeconds = Math.floor((expirationTime - now) / 1000);

  if (diffInSeconds <= 0) {
    timeLeft.value = 0;
    isExpired.value = true;
    isModalOpen.value = false;
    if (timerInterval) clearInterval(timerInterval);
  } else {
    timeLeft.value = diffInSeconds;
  }
};

onMounted(() => {
  if (props.expiresAt) {
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
  }

  if (props.hasTrustedDevices && typeof window.Echo !== 'undefined') {
    window.Echo.channel(`device-auth.${props.authId}`)
      .listen('.device.status.changed', (e) => {
        if (e.status === 'approved') {
          if (finalizeFormRef.value) {
            finalizeFormRef.value.submit();
          }
        } else if (e.status === 'rejected') {
          const loginUrl = props.routes.login.includes('?') 
            ? `${props.routes.login}&rejected=1` 
            : `${props.routes.login}?rejected=1`;
          window.location.href = loginUrl;
        } else if (e.status === 'expired') {
          isExpired.value = true;
          isModalOpen.value = false;
          if (timerInterval) clearInterval(timerInterval);
        }
      });
  }
});

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval);
  }

  if (props.hasTrustedDevices && typeof window.Echo !== 'undefined') {
    window.Echo.leaveChannel(`device-auth.${props.authId}`);
  }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>