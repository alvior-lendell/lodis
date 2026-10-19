<template>
  <div class="space-y-6">
    <!-- Status Alert -->
    <div v-if="localStatusMessage" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ localStatusMessage }}</span>
      </div>
      <button type="button" @click="localStatusMessage = ''" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold p-1">&times;</button>
    </div>

    <!-- Error Alerts -->
    <div v-if="errorsList && errorsList.length > 0" class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs font-medium text-rose-700 shadow-sm">
      <ul class="list-disc list-inside space-y-1">
        <li v-for="(error, idx) in errorsList" :key="idx">{{ error }}</li>
      </ul>
    </div>

    <!-- Action Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Philippine Holiday Schedule</h1>
        <p class="text-xs text-slate-500 mt-1">Manage and sync official PH holidays into the dedicated <code class="font-mono text-slate-700">holidays</code> database table.</p>
      </div>

      <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap sm:flex-nowrap">
        <!-- Year Selector Form -->
        <form method="GET" :action="routes.index" ref="yearFormRef" class="flex items-center">
          <select 
            name="year" 
            v-model="selectedYearValue" 
            @change="submitYearFilter" 
            class="text-xs font-bold rounded-2xl border-slate-200 bg-slate-50 text-slate-700 py-2.5 px-3.5 focus:ring-4 focus:ring-brand/15 focus:border-brand transition"
          >
            <option v-for="y in availableYears" :key="y" :value="y">Year {{ y }}</option>
          </select>
        </form>

        <!-- Open Add Modal Button -->
        <button 
          type="button" 
          @click="showModal = true" 
          class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-200 flex items-center gap-2"
        >
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          <span>Add Holiday</span>
        </button>

        <!-- Sync Button Form -->
        <form ref="syncFormRef" method="POST" :action="routes.sync" @submit.prevent="openSyncModal">
          <input type="hidden" name="_token" :value="csrfToken">
          <input type="hidden" name="year" :value="selectedYearValue">
          <button 
            type="submit" 
            class="px-4 py-2.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>One-Click Pull PH Holidays</span>
          </button>
        </form>
      </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-extrabold uppercase text-slate-500 tracking-wider">
              <th class="py-4 px-6">Date</th>
              <th class="py-4 px-6">Holiday Name</th>
              <th class="py-4 px-6">Type & Tags</th>
              <th class="py-4 px-6">Proclamation / Notes</th>
              <th class="py-4 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr v-for="holiday in holidays" :key="holiday.id" class="hover:bg-slate-50/60 transition">
              <td class="py-4 px-6 font-bold text-slate-800 whitespace-nowrap">
                {{ holiday.date_formatted || formatDate(holiday.date) }}
                <span class="block text-[10px] font-normal text-slate-400">
                  {{ holiday.day_of_week || getDayOfWeek(holiday.date) }}
                </span>
              </td>
              <td class="py-4 px-6">
                <span class="font-bold text-slate-900 block">{{ holiday.name }}</span>
                <span v-if="holiday.double_holiday && holiday.double_holiday_names && holiday.double_holiday_names.length" class="text-[10px] text-purple-600 font-semibold block mt-0.5">
                  Double Holiday: {{ holiday.double_holiday_names.join(' & ') }}
                </span>
              </td>
              <td class="py-4 px-6 whitespace-nowrap space-y-1">
                <span 
                  class="inline-block px-2.5 py-1 rounded-full text-[10px] font-extrabold border"
                  :class="getTypeStyle(holiday.type)"
                >
                  {{ getTypeLabel(holiday.type) }}
                </span>

                <span v-if="holiday.is_part_of_long_weekend" class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200 ml-1">
                  Long Weekend
                </span>
              </td>
              <td class="py-4 px-6">
                <span v-if="holiday.proclamation_ref" class="text-[11px] font-semibold text-slate-700 block">{{ holiday.proclamation_ref }}</span>
                <span v-if="holiday.notes" class="text-[10px] text-slate-400 truncate block max-w-xs">{{ holiday.notes }}</span>
              </td>
              <td class="py-4 px-6 text-right whitespace-nowrap">
                <button 
                  type="button" 
                  @click="openDeleteModal(holiday)" 
                  class="text-rose-600 hover:text-rose-800 text-xs font-bold transition"
                >
                  Remove
                </button>
              </td>
            </tr>

            <tr v-if="holidays.length === 0">
              <td colspan="5" class="py-12 text-center text-slate-500">
                No holidays found for {{ selectedYearValue }}. Click <span class="font-bold text-brand">One-Click Pull PH Holidays</span> to sync automatically.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Holiday Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
      <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <h3 class="text-base font-extrabold text-slate-900">Add Holiday Entry</h3>
          <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form method="POST" :action="routes.store" class="space-y-4">
          <input type="hidden" name="_token" :value="csrfToken">

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Holiday Name *</label>
            <input 
              type="text" 
              name="name" 
              v-model="newHoliday.name" 
              required 
              placeholder="e.g. Independence Day" 
              class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15"
            >
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Date *</label>
              <input 
                type="date" 
                name="date" 
                v-model="newHoliday.date" 
                required 
                class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15"
              >
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Type *</label>
              <select 
                name="type" 
                v-model="newHoliday.type" 
                required 
                class="form-select block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15"
              >
                <option value="regular">Regular Holiday</option>
                <option value="special_non_working">Special Non-Working</option>
                <option value="special_working">Special Working</option>
                <option value="islamic">Islamic Holiday</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Proclamation Reference</label>
            <input 
              type="text" 
              name="proclamation_ref" 
              v-model="newHoliday.proclamation_ref" 
              placeholder="e.g. Proclamation No. 1006" 
              class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15"
            >
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Notes</label>
            <textarea 
              name="notes" 
              rows="2" 
              v-model="newHoliday.notes" 
              placeholder="Optional context..." 
              class="form-textarea block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm outline-none transition resize-none focus:border-brand focus:ring-4 focus:ring-brand/15"
            ></textarea>
          </div>

          <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
            <button type="submit" class="px-5 py-3 rounded-2xl bg-brand text-white text-xs font-bold transition hover:bg-brand-hover">
              Save Holiday
            </button>
            <button type="button" @click="showModal = false" class="px-4 py-3 rounded-2xl bg-white border border-slate-200 text-slate-700 text-xs font-bold transition hover:bg-slate-100">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Custom Sync Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="isSyncModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click.self="closeSyncModal"
      >
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-light text-brand flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Sync Philippine Holidays</h3>
              <p class="text-xs text-slate-500">Fetch and update official holiday schedule.</p>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Are you sure you want to pull and sync official Philippine holidays for year 
            <strong class="text-slate-900 font-bold font-mono">{{ selectedYearValue }}</strong>? 
            This will retrieve and update holiday entries in the database.
          </p>

          <div class="flex items-center justify-end gap-2.5 pt-2">
            <button 
              type="button" 
              @click="closeSyncModal" 
              class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
            >
              Cancel
            </button>

            <button 
              type="button" 
              @click="confirmAndSubmitSync"
              class="px-4 py-2.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold transition shadow-md shadow-brand/20 flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              <span>Confirm & Sync</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Custom Delete Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="isDeleteModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click.self="closeDeleteModal"
      >
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 transform transition-all space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Delete Holiday Entry</h3>
              <p class="text-xs text-slate-500">This action is permanent and cannot be undone.</p>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Are you sure you want to permanently delete 
            <strong class="text-slate-900 font-bold">'{{ selectedHolidayForDelete?.name }}'</strong>?
          </p>

          <div class="flex items-center justify-end gap-2.5 pt-2">
            <button 
              type="button" 
              @click="closeDeleteModal" 
              class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
            >
              Cancel
            </button>

            <form method="POST" :action="selectedHolidayForDelete?.destroy_url">
              <input type="hidden" name="_token" :value="csrfToken">
              <input type="hidden" name="_method" value="DELETE">
              <button 
                type="submit" 
                class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
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
  holidays: { type: Array, default: () => [] },
  selectedYear: { type: [Number, String], required: true },
  availableYears: { type: Array, default: () => [] },
  statusMessage: { type: String, default: '' },
  errorsList: { type: Array, default: () => [] },
  csrfToken: { type: String, required: true },
  routes: {
    type: Object,
    required: true,
    default: () => ({
      index: '#',
      store: '#',
      sync: '#',
    }),
  },
});

const localStatusMessage = ref(props.statusMessage);
const selectedYearValue = ref(props.selectedYear);
const showModal = ref(false);
const yearFormRef = ref(null);

// Modal state management
const syncFormRef = ref(null);
const isSyncModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedHolidayForDelete = ref(null);

const openSyncModal = () => {
  isSyncModalOpen.value = true;
};

const closeSyncModal = () => {
  isSyncModalOpen.value = false;
};

const confirmAndSubmitSync = () => {
  if (syncFormRef.value) {
    syncFormRef.value.submit();
  }
};

const openDeleteModal = (holiday) => {
  selectedHolidayForDelete.value = holiday;
  isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false;
  selectedHolidayForDelete.value = null;
};

const newHoliday = ref({
  name: '',
  date: '',
  type: 'regular',
  proclamation_ref: '',
  notes: '',
});

const typeStyles = {
  regular: 'bg-amber-50 text-amber-800 border-amber-200',
  special_non_working: 'bg-purple-50 text-purple-800 border-purple-200',
  special_working: 'bg-sky-50 text-sky-800 border-sky-200',
  islamic: 'bg-emerald-50 text-emerald-800 border-emerald-200',
};

const typeLabels = {
  regular: 'Regular Holiday',
  special_non_working: 'Special Non-Working',
  special_working: 'Special Working',
  islamic: 'Islamic Holiday',
};

const getTypeStyle = (type) => typeStyles[type] || 'bg-slate-50 text-slate-700 border-slate-200';
const getTypeLabel = (type) => typeLabels[type] || type;

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  const date = new Date(Number(year), Number(month) - 1, Number(day));
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getDayOfWeek = (dateStr) => {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length !== 3) return '';
  const [year, month, day] = parts;
  const date = new Date(Number(year), Number(month) - 1, Number(day));
  return date.toLocaleDateString('en-US', { weekday: 'long' });
};

const submitYearFilter = () => {
  if (yearFormRef.value) {
    yearFormRef.value.submit();
  }
};
</script>