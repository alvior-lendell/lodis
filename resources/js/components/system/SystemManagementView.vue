<template>
  <div class="space-y-6">
    <!-- Status Toast Banner -->
    <div 
      v-if="localStatusMessage" 
      class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-sm"
    >
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ localStatusMessage }}</span>
      </div>
      <button 
        type="button" 
        @click="localStatusMessage = ''" 
        class="text-emerald-600 hover:text-emerald-800 text-sm font-bold p-1 leading-none"
      >
        &times;
      </button>
    </div>

    <!-- Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Registered Systems</h2>
        <p class="text-xs text-slate-500 mt-1">Configure operational platform launchers and manage dynamic portal visibility.</p>
      </div>

      <a 
        v-if="routes.create" 
        :href="routes.create" 
        class="px-4 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm flex items-center gap-2 w-fit"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Add New System</span>
      </a>
    </div>

    <!-- Systems Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
              <th class="py-3.5 px-6">Order</th>
              <th class="py-3.5 px-6">Logo</th>
              <th class="py-3.5 px-6">System Info</th>
              <th class="py-3.5 px-6">Theme</th>
              <th class="py-3.5 px-6">Endpoints</th>
              <th class="py-3.5 px-6">Status</th>
              <th class="py-3.5 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr v-for="system in systems" :key="system.id" class="hover:bg-slate-50/50 transition">
              <!-- Sort Order -->
              <td class="py-4 px-6 font-bold text-slate-900">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200">
                  {{ system.default_sort_order }}
                </span>
              </td>

              <!-- Platform Logo -->
              <td class="py-4 px-6">
                <div class="w-16 h-12 rounded-xl border border-slate-100 bg-slate-50/80 flex items-center justify-center p-1.5 overflow-hidden">
                  <img 
                    v-if="system.logo_url" 
                    :src="system.logo_url" 
                    :alt="system.name" 
                    class="max-h-full max-w-full object-contain"
                  >
                  <span v-else class="text-[10px] italic text-slate-400">No Image</span>
                </div>
              </td>

              <!-- System Details & Tags -->
              <td class="py-4 px-6 max-w-xs">
                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                  <span class="font-bold text-slate-900 text-sm">{{ system.name }}</span>
                  <span 
                    v-if="system.badge" 
                    class="px-2 py-0.5 rounded-md bg-brand-light text-brand font-bold text-[10px] uppercase tracking-wide border border-brand/20"
                  >
                    {{ system.badge }}
                  </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap text-[11px] text-slate-500 mt-1">
                  <span v-if="system.tag" class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold border border-slate-200">
                    {{ system.tag }}
                  </span>
                  <span v-if="system.slug" class="font-mono text-slate-400">
                    /{{ system.slug }}
                  </span>
                </div>

                <p v-if="system.description" class="text-slate-500 text-[11px] line-clamp-2 mt-1">
                  {{ system.description }}
                </p>
              </td>

              <!-- Theme Swatch Preview -->
              <td class="py-4 px-6">
                <div 
                  class="w-20 h-8 rounded-lg border border-slate-200 shadow-inner p-1 flex items-center justify-center relative overflow-hidden"
                  :style="{ background: system.bg_gradient || system.brand_color || '#00687A' }"
                  :title="`Brand Color: ${system.brand_color || 'Default'}`"
                >
                  <span class="w-3 h-3 rounded-full border border-white/60 shadow-sm" :style="{ backgroundColor: system.brand_color }"></span>
                </div>
              </td>

              <!-- Target URL & Ping Endpoint -->
              <td class="py-4 px-6">
                <div class="space-y-1">
                  <div class="font-mono text-[11px] text-brand hover:underline flex items-center gap-1.5 truncate max-w-[200px]">
                    <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <a :href="system.url" target="_blank" rel="noopener noreferrer" class="truncate">{{ system.url }}</a>
                  </div>

                  <div v-if="system.ping_url" class="font-mono text-[10px] text-slate-400 flex items-center gap-1.5 truncate max-w-[200px]" title="Health Ping URL">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="truncate">{{ system.ping_url }}</span>
                  </div>
                </div>
              </td>

              <!-- Launcher Status & Health Ping Indicator -->
              <td class="py-4 px-6">
                <div class="flex flex-col gap-1.5 items-start">
                  <!-- Launcher Active Status -->
                  <span 
                    v-if="system.is_active" 
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                  >
                    Active
                  </span>
                  <span 
                    v-else 
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200"
                  >
                    Disabled
                  </span>

                  <!-- Online Ping Status -->
                  <span 
                    v-if="system.ping_url" 
                    class="inline-flex items-center gap-1 text-[10px] font-semibold"
                    :class="system.is_online ? 'text-emerald-600' : 'text-rose-600'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="system.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                    {{ system.is_online ? 'Online' : 'Unreachable' }}
                  </span>
                </div>
              </td>

              <!-- Actions -->
              <td class="py-4 px-6 text-right">
                <div class="flex items-center justify-end gap-2">
                  <a 
                    :href="system.edit_url" 
                    class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition border border-slate-200" 
                    title="Edit System"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                  </a>

                  <!-- Triggers Delete Confirmation Modal -->
                  <button 
                    type="button" 
                    @click="openDeleteModal(system)"
                    class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition border border-rose-200" 
                    title="Delete System"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="systems.length === 0">
              <td colspan="7" class="py-12 text-center text-slate-500 font-semibold">
                No systems configured in database. Click "Add New System" to register one.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="isDeleteModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click.self="closeDeleteModal"
      >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Delete System Entry</h3>
              <p class="text-xs text-slate-500">This action is permanent and cannot be undone.</p>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Are you sure you want to permanently delete 
            <strong class="text-slate-900 font-bold">{{ selectedSystemForDelete?.name }}</strong>? 
            This will remove its launcher portal tile and access configurations.
          </p>

          <div class="flex items-center justify-end gap-2.5 pt-2">
            <button 
              type="button" 
              @click="closeDeleteModal" 
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
            >
              Cancel
            </button>

            <form method="POST" :action="selectedSystemForDelete?.destroy_url">
              <input type="hidden" name="_token" :value="csrfToken">
              <input type="hidden" name="_method" value="DELETE">
              <button 
                type="submit" 
                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Confirm Delete</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  systems: { type: Array, default: () => [] },
  statusMessage: { type: String, default: '' },
  csrfToken: { type: String, required: true },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      create: '#',
    }),
  },
});

const localStatusMessage = ref(props.statusMessage);

// Modal state management
const isDeleteModalOpen = ref(false);
const selectedSystemForDelete = ref(null);

const openDeleteModal = (system) => {
  selectedSystemForDelete.value = system;
  isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false;
  selectedSystemForDelete.value = null;
};
</script>