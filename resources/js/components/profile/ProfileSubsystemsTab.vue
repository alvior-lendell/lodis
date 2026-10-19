<template>
  <div class="space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
      <!-- Section Header with Counter -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
        <div>
          <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Ecosystem Subsystem Entitlements</h2>
          <p class="text-xs text-slate-500 mt-0.5">Application access privileges managed through system authorization policy.</p>
        </div>
        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200 w-fit shrink-0">
          Authorized: {{ authorizedCount }} / {{ userSystems.length }}
        </span>
      </div>

      <!-- List View Table -->
      <div class="border border-slate-200/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <th class="p-4">Subsystem</th>
                <th class="p-4">Entitlement Role</th>
                <th class="p-4 text-right">Access Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
              <tr 
                v-for="system in sortedUserSystems" 
                :key="system.id" 
                class="hover:bg-slate-50/50 transition"
                :class="{ 'opacity-60 bg-slate-50/30': !system.has_access }"
              >
                <!-- Subsystem Logo & Details -->
                <td class="p-4">
                  <div class="flex items-center gap-3">
                    <img 
                      v-if="system.logo_url || system.logo" 
                      :src="system.logo_url || system.logo" 
                      :alt="system.name" 
                      class="w-9 h-9 rounded-xl object-contain border p-1 bg-white shrink-0 shadow-xs"
                      :class="system.has_access ? 'border-slate-200' : 'border-slate-100 grayscale'"
                    />
                    <div 
                      v-else 
                      class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border"
                      :class="system.has_access ? 'bg-brand-light text-brand border-brand/20' : 'bg-slate-100 text-slate-400 border-slate-200'"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                      </svg>
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 text-xs">{{ system.name }}</h4>
                      <span class="text-[10px] font-mono font-semibold text-slate-400 uppercase tracking-wider">
                        {{ system.code || 'N/A' }}
                      </span>
                    </div>
                  </div>
                </td>

                <!-- Entitlement Role -->
                <td class="p-4">
                  <span 
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1.5"
                    :class="system.has_access && system.role && system.role !== 'None' ? 'bg-blue-50 text-blue-700 border border-blue-200/80' : 'bg-slate-100 text-slate-500 border border-slate-200/60'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="system.has_access && system.role && system.role !== 'None' ? 'bg-blue-500' : 'bg-slate-400'"></span>
                    {{ system.role || 'None' }}
                  </span>
                </td>

                <!-- Access Status Badge -->
                <td class="p-4 text-right">
                  <span 
                    class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider inline-flex items-center gap-1.5"
                    :class="system.has_access ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="system.has_access ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                    {{ system.has_access ? 'Authorized' : 'Unassigned' }}
                  </span>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="userSystems.length === 0">
                <td colspan="3" class="p-8 text-center text-slate-500 space-y-2">
                  <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 08-8 0v4h8z"/>
                  </svg>
                  <h4 class="text-xs font-bold text-slate-700">No Subsystem Mappings Configured</h4>
                  <p class="text-[11px] text-slate-500 max-w-sm mx-auto">No subsystem access records found in system configuration.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  userSystems: { type: Array, default: () => [] },
});

const authorizedCount = computed(() => {
  return props.userSystems.filter(sys => sys.has_access).length;
});

// Sorts systems with access first, then alphabetically by name
const sortedUserSystems = computed(() => {
  return [...props.userSystems].sort((a, b) => {
    if (a.has_access === b.has_access) {
      return (a.name || '').localeCompare(b.name || '');
    }
    return a.has_access ? -1 : 1;
  });
});
</script>