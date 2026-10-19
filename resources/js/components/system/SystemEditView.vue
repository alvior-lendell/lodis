<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
      <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Application Entry</h2>
        <p class="text-xs text-slate-500 mt-1">Modify configuration parameters and branding for {{ form.name || 'this system' }}.</p>
      </div>
      <span 
        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
        :class="form.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'"
      >
        {{ form.is_active ? 'Active Launcher' : 'Disabled' }}
      </span>
    </div>

    <!-- Error Summary Alert -->
    <div v-if="errorsList && errorsList.length > 0" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 shadow-sm">
      <div class="flex items-center gap-2 mb-1 font-bold text-rose-800">
        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Please resolve the following errors:</span>
      </div>
      <ul class="list-disc list-inside space-y-0.5">
        <li v-for="(err, idx) in errorsList" :key="idx">{{ err }}</li>
      </ul>
    </div>

    <form 
      ref="formRef" 
      method="POST" 
      :action="routes.update" 
      enctype="multipart/form-data" 
      @submit.prevent="openUpdateModal" 
      class="space-y-6"
    >
      <input type="hidden" name="_token" :value="csrfToken">
      <input type="hidden" name="_method" value="PUT">

      <!-- Name & Slug Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">System Name *</label>
          <input 
            type="text" 
            name="name" 
            id="name" 
            v-model="form.name" 
            required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition"
            placeholder="e.g. LORISv2 Portal"
          >
          <span v-if="errors.name" class="text-xs text-rose-500 mt-1 block">{{ errors.name[0] }}</span>
        </div>

        <div>
          <label for="slug" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Slug</label>
          <input 
            type="text" 
            name="slug" 
            id="slug" 
            v-model="form.slug" 
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition font-mono"
            placeholder="e.g. lorisv2-portal"
          >
          <span v-if="errors.slug" class="text-xs text-rose-500 mt-1 block">{{ errors.slug[0] }}</span>
        </div>
      </div>

      <!-- Target URL & Ping URL Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Target Launch URL *</label>
          <div class="flex rounded-xl border border-slate-200 overflow-hidden focus-within:border-brand focus-within:ring-1 focus-within:ring-brand shadow-sm transition">
            <span class="inline-flex items-center px-3.5 bg-slate-100 text-slate-500 font-mono text-xs font-bold border-r border-slate-200 select-none shrink-0">https://</span>
            <input 
              type="text" 
              name="url" 
              id="url" 
              v-model="form.url" 
              required
              class="w-full py-2.5 px-3 text-xs font-medium text-slate-900 outline-none font-mono"
              placeholder="loris.lendell-local.xyz"
            >
          </div>
          <span v-if="errors.url" class="text-xs text-rose-500 mt-1 block">{{ errors.url[0] }}</span>
        </div>

        <div>
          <label for="ping_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Health Ping URL</label>
          <input 
            type="url" 
            name="ping_url" 
            id="ping_url" 
            v-model="form.ping_url" 
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition font-mono"
            placeholder="https://loris.lendell-local.xyz/api/ping"
          >
          <span v-if="errors.ping_url" class="text-xs text-rose-500 mt-1 block">{{ errors.ping_url[0] }}</span>
        </div>
      </div>

      <!-- Description Input -->
      <div>
        <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Description</label>
        <textarea 
          name="description" 
          id="description" 
          rows="3"
          v-model="form.description"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition"
          placeholder="Brief overview of system functions or target department operations."
        ></textarea>
        <span v-if="errors.description" class="text-xs text-rose-500 mt-1 block">{{ errors.description[0] }}</span>
      </div>

      <!-- Badge & Category Tag Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="badge" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Badge Text</label>
          <input 
            type="text" 
            name="badge" 
            id="badge" 
            v-model="form.badge" 
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition"
            placeholder="e.g. NEW, BETA, V2"
          >
          <span v-if="errors.badge" class="text-xs text-rose-500 mt-1 block">{{ errors.badge[0] }}</span>
        </div>

        <div>
          <label for="tag" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Category Tag</label>
          <input 
            type="text" 
            name="tag" 
            id="tag" 
            v-model="form.tag" 
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition"
            placeholder="e.g. Operations, HR, Finance"
          >
          <span v-if="errors.tag" class="text-xs text-rose-500 mt-1 block">{{ errors.tag[0] }}</span>
        </div>
      </div>

      <!-- Theme Styling & Gradient Builder -->
      <div class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/50 p-4">
        <div class="flex items-center justify-between">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
            Brand Theme & Gradient Styling
          </label>
          <button 
            type="button" 
            @click="applyAutoPalette"
            class="text-[11px] font-bold text-brand hover:underline flex items-center gap-1"
          >
            <span>✨ Auto-generate from Brand Color</span>
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Main Brand Color -->
          <div>
            <label for="brand_color" class="block text-[11px] font-bold text-slate-600 uppercase mb-1.5">Main Brand Color</label>
            <div class="flex gap-2 items-center">
              <input 
                type="color" 
                v-model="form.brand_color" 
                @input="onBrandColorChange"
                class="w-10 h-10 rounded-xl border border-slate-200 p-1 cursor-pointer shrink-0 bg-white"
              >
              <input 
                type="text" 
                name="brand_color" 
                id="brand_color" 
                v-model="form.brand_color" 
                @input="onBrandColorChange"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-brand text-xs font-mono text-slate-900 bg-white"
                placeholder="#00687A"
              >
            </div>
            <span v-if="errors.brand_color" class="text-xs text-rose-500 mt-1 block">{{ errors.brand_color[0] }}</span>
          </div>

          <!-- Hover Color -->
          <div>
            <label for="brand_hover_color" class="block text-[11px] font-bold text-slate-600 uppercase mb-1.5">Hover Color</label>
            <div class="flex gap-2 items-center">
              <input 
                type="color" 
                v-model="form.brand_hover_color" 
                class="w-10 h-10 rounded-xl border border-slate-200 p-1 cursor-pointer shrink-0 bg-white"
              >
              <input 
                type="text" 
                name="brand_hover_color" 
                id="brand_hover_color" 
                v-model="form.brand_hover_color" 
                class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-brand text-xs font-mono text-slate-900 bg-white"
                placeholder="#005261"
              >
            </div>
            <span v-if="errors.brand_hover_color" class="text-xs text-rose-500 mt-1 block">{{ errors.brand_hover_color[0] }}</span>
          </div>
        </div>

        <!-- Gradient Stops -->
        <div class="pt-2 border-t border-slate-200/60">
          <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1.5">Background Gradient Stops</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
            <div class="flex gap-2 items-center">
              <span class="text-[10px] font-bold text-slate-400 w-10">START</span>
              <input 
                type="color" 
                v-model="gradStart" 
                @input="updateGradient"
                class="w-9 h-9 rounded-lg border border-slate-200 p-1 cursor-pointer bg-white"
              >
              <input 
                type="text" 
                v-model="gradStart" 
                @input="updateGradient"
                class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-mono bg-white"
              >
            </div>

            <div class="flex gap-2 items-center">
              <span class="text-[10px] font-bold text-slate-400 w-10">END</span>
              <input 
                type="color" 
                v-model="gradEnd" 
                @input="updateGradient"
                class="w-9 h-9 rounded-lg border border-slate-200 p-1 cursor-pointer bg-white"
              >
              <input 
                type="text" 
                v-model="gradEnd" 
                @input="updateGradient"
                class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-mono bg-white"
              >
            </div>
          </div>

          <!-- Realtime Live Preview Box -->
          <div 
            class="h-10 w-full rounded-xl border border-slate-200 shadow-inner flex items-center justify-between px-4 transition-all duration-300"
            :style="{ background: form.bg_gradient }"
          >
            <span class="text-[10px] font-mono font-bold text-white/80 drop-shadow">Gradient Preview</span>
            <span class="text-[9px] font-mono text-white/60 truncate max-w-[250px]">{{ form.bg_gradient }}</span>
          </div>

          <input type="hidden" name="bg_gradient" :value="form.bg_gradient">
        </div>
      </div>

      <!-- Sort Order & Launcher Status -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="default_sort_order" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Sort Order *</label>
          <input 
            type="number" 
            name="default_sort_order" 
            id="default_sort_order" 
            v-model="form.default_sort_order" 
            min="0" 
            required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand focus:ring-1 focus:ring-brand text-xs font-medium text-slate-900 shadow-sm outline-none transition font-mono"
          >
          <span class="text-[11px] text-slate-400 mt-1 block">Lower numbers display first.</span>
          <span v-if="errors.default_sort_order" class="text-xs text-rose-500 mt-1 block">{{ errors.default_sort_order[0] }}</span>
        </div>

        <div class="flex flex-col justify-center">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Launcher Status</label>
          <label class="inline-flex items-center gap-3 cursor-pointer">
            <input 
              type="checkbox" 
              name="is_active" 
              value="1" 
              v-model="form.is_active"
              class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand cursor-pointer"
            >
            <span class="text-xs font-bold text-slate-800">Enable Launcher Tile</span>
          </label>
        </div>
      </div>

      <!-- Logo Image Upload & Existing Logo Preview -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Platform Logo Asset</label>
        <div class="flex items-center gap-4">
          <div class="w-24 h-20 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center shrink-0 overflow-hidden relative">
            <span v-if="!logoPreviewUrl" class="text-[10px] text-slate-400 font-semibold text-center px-1">No Logo</span>
            <img v-else :src="logoPreviewUrl" alt="Logo Preview" class="max-h-16 w-auto object-contain">
          </div>

          <div class="flex-1">
            <input 
              type="file" 
              name="logo" 
              id="logo-input" 
              accept="image/*"
              @change="handleLogoChange"
              class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-light file:text-brand hover:file:bg-brand/20 cursor-pointer"
            >
            <span class="text-[11px] text-slate-400 mt-1 block">PNG, SVG, JPG, WEBP up to 2MB. Leave blank to keep existing logo.</span>
          </div>
        </div>
        <span v-if="errors.logo" class="text-xs text-rose-500 mt-1 block">{{ errors.logo[0] }}</span>
      </div>

      <!-- Action Buttons -->
      <div class="border-t border-slate-100 pt-6 flex items-center justify-end gap-3">
        <a 
          :href="routes.cancel || routes.index || '#'" 
          class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200"
        >
          Cancel
        </a>
        <button 
          type="submit" 
          class="px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm"
        >
          Update System Entry
        </button>
      </div>
    </form>

    <!-- Custom Update Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="isUpdateModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click.self="closeUpdateModal"
      >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Confirm System Entry Updates</h3>
              <p class="text-xs text-slate-500">Please review configuration parameters before saving.</p>
            </div>
          </div>

          <!-- Changes Preview Summary Card -->
          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">System Name:</span>
              <span class="font-bold text-slate-900">{{ form.name }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Target URL:</span>
              <span class="font-mono text-slate-800 text-[11px] truncate max-w-[200px]">https://{{ form.url }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Launcher Status:</span>
              <span 
                :class="form.is_active ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-slate-700 bg-slate-100 border-slate-200'"
                class="px-2 py-0.5 rounded-md font-bold text-[10px] border"
              >
                {{ form.is_active ? 'Active' : 'Disabled' }}
              </span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500 font-medium">Sort Order:</span>
              <span class="font-mono font-bold text-slate-900">{{ form.default_sort_order }}</span>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Are you sure you want to save these update settings for 
            <strong class="text-slate-900 font-bold">{{ form.name || 'this system' }}</strong>?
          </p>

          <div class="flex items-center justify-end gap-2.5 pt-2">
            <button 
              type="button" 
              @click="closeUpdateModal" 
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
            >
              Cancel
            </button>

            <button 
              type="button" 
              @click="confirmAndSubmit"
              class="px-4 py-2.5 rounded-xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Confirm & Save</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  system: {
    type: Object,
    required: true,
    default: () => ({}),
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  errorsList: {
    type: Array,
    default: () => [],
  },
  csrfToken: {
    type: String,
    required: true,
  },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      update: '#',
      cancel: '#',
      index: '#',
    }),
  },
});

const formRef = ref(null);
const isUpdateModalOpen = ref(false);

const openUpdateModal = () => {
  isUpdateModalOpen.value = true;
};

const closeUpdateModal = () => {
  isUpdateModalOpen.value = false;
};

const confirmAndSubmit = () => {
  if (formRef.value) {
    formRef.value.submit();
  }
};

// Color adjustment helper
const adjustColor = (hex, percent) => {
  let num = parseInt((hex || '#00687A').replace('#', ''), 16);
  if (isNaN(num)) return hex;
  let r = Math.min(255, Math.max(0, (num >> 16) + percent));
  let g = Math.min(255, Math.max(0, ((num >> 8) & 0x00FF) + percent));
  let b = Math.min(255, Math.max(0, (num & 0x0000FF) + percent));
  return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
};

// Gradient stop extraction
const extractGradientColors = (str) => {
  const hexes = (str || '').match(/#[a-fA-F0-9]{6}/g);
  return {
    start: hexes?.[0] || '#020617',
    end: hexes?.[1] || adjustColor(props.system.brand_color || '#00687A', -60),
  };
};

const initialGradient = props.system.bg_gradient || 'linear-gradient(135deg, #020617 0%, #003843 100%)';
const extracted = extractGradientColors(initialGradient);

const gradStart = ref(extracted.start);
const gradEnd = ref(extracted.end);

// Form Reactive State bound to initial props.system values
const form = ref({
  name: props.system.name || '',
  slug: props.system.slug || '',
  url: props.system.url ? props.system.url.replace(/^https?:\/\//i, '') : '',
  ping_url: props.system.ping_url || '',
  description: props.system.description || '',
  badge: props.system.badge || '',
  tag: props.system.tag || '',
  brand_color: props.system.brand_color || '#00687A',
  brand_hover_color: props.system.brand_hover_color || '#005261',
  bg_gradient: initialGradient,
  default_sort_order: props.system.default_sort_order !== undefined ? props.system.default_sort_order : 0,
  is_active: props.system.is_active !== undefined ? !!props.system.is_active : true,
});

// Logo preview initially points to logo_url asset sent by controller
const logoPreviewUrl = ref(props.system.logo_url || null);

const handleLogoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (evt) => {
      logoPreviewUrl.value = evt.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    logoPreviewUrl.value = props.system.logo_url || null;
  }
};

const updateGradient = () => {
  form.value.bg_gradient = `linear-gradient(135deg, ${gradStart.value} 0%, ${gradEnd.value} 100%)`;
};

const applyAutoPalette = () => {
  const brand = form.value.brand_color;
  if (!/^#[0-9A-F]{6}$/i.test(brand)) return;

  form.value.brand_hover_color = adjustColor(brand, -25);
  gradStart.value = '#020617';
  gradEnd.value = adjustColor(brand, -60);
  updateGradient();
};

const onBrandColorChange = () => {
  applyAutoPalette();
};
</script>