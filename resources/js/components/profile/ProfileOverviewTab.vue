<template>
  <div class="space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
        <div>
          <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Personal Profile Request</h2>
          <p class="text-xs text-slate-500 mt-0.5">Modifications to official employee records are submitted for HR verification.</p>
        </div>
        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200/80 w-fit">
          HR Approval Required
        </span>
      </div>

      <div v-if="pendingProfileRequest" class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-800 text-xs flex items-start gap-3 shadow-sm">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="font-bold text-amber-900">Profile Change Request Pending HR Approval</p>
          <p class="mt-0.5 text-amber-700">You submitted a profile update request on {{ pendingProfileRequest.created_at_formatted }}. Editing is temporarily locked until HR reviews your request.</p>
        </div>
      </div>

      <form method="POST" :action="updateProfileRoute" autocomplete="off" class="space-y-6">
        <input type="hidden" name="_token" :value="csrfToken">
        <input type="hidden" name="_method" value="PATCH">

        <!-- Name Fields Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">First Name <span class="text-rose-500">*</span></label>
            <input 
              id="first_name" 
              name="first_name" 
              type="text" 
              :value="employee?.first_name" 
              required
              :disabled="!!pendingProfileRequest"
              class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm font-medium outline-none transition"
              :class="pendingProfileRequest ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'focus:border-brand focus:ring-4 focus:ring-brand/15'"
            >
          </div>

          <div>
            <label for="middle_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Middle Name</label>
            <input 
              id="middle_name" 
              name="middle_name" 
              type="text" 
              :value="employee?.middle_name"
              :disabled="!!pendingProfileRequest"
              class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm font-medium outline-none transition"
              :class="pendingProfileRequest ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'focus:border-brand focus:ring-4 focus:ring-brand/15'"
            >
          </div>

          <div>
            <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Last Name <span class="text-rose-500">*</span></label>
            <input 
              id="last_name" 
              name="last_name" 
              type="text" 
              :value="employee?.last_name" 
              required
              :disabled="!!pendingProfileRequest"
              class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm font-medium outline-none transition"
              :class="pendingProfileRequest ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'focus:border-brand focus:ring-4 focus:ring-brand/15'"
            >
          </div>

          <div>
            <label for="suffix" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Suffix</label>
            <input 
              id="suffix" 
              name="suffix" 
              type="text" 
              :value="employee?.suffix" 
              placeholder="e.g. Jr., III"
              :disabled="!!pendingProfileRequest"
              class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm font-medium outline-none transition"
              :class="pendingProfileRequest ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'focus:border-brand focus:ring-4 focus:ring-brand/15'"
            >
          </div>
        </div>

        <!-- Demographics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label for="contact_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Contact Number</label>
            <input 
              id="contact_number" 
              name="contact_number" 
              type="text" 
              :value="employee?.contact_number" 
              placeholder="09xxxxxxxxx"
              :disabled="!!pendingProfileRequest"
              class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm font-medium outline-none transition"
              :class="pendingProfileRequest ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'focus:border-brand focus:ring-4 focus:ring-brand/15'"
            >
          </div>

          <div>
            <label for="gender" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Gender</label>
            <select 
              id="gender" 
              name="gender"
              :value="employee?.gender"
              :disabled="!!pendingProfileRequest"
              class="form-select block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm font-medium outline-none transition"
              :class="pendingProfileRequest ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'focus:border-brand focus:ring-4 focus:ring-brand/15'"
            >
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Non-Binary">Non-Binary</option>
            </select>
          </div>

          <div>
            <label for="birthdate" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Birth Date</label>
            <input 
              id="birthdate" 
              name="birthdate" 
              type="date" 
              :value="employee?.birthdate_formatted"
              :disabled="!!pendingProfileRequest"
              class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm font-medium outline-none transition"
              :class="pendingProfileRequest ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'focus:border-brand focus:ring-4 focus:ring-brand/15'"
            >
          </div>
        </div>

        <!-- Address Row -->
        <div>
          <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Residential Address</label>
          <input 
            id="address" 
            name="address" 
            type="text" 
            :value="employee?.address" 
            placeholder="Enter residential address"
            :disabled="!!pendingProfileRequest"
            class="form-input block w-full px-4 py-3 rounded-2xl border-slate-200 text-sm font-medium outline-none transition"
            :class="pendingProfileRequest ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'focus:border-brand focus:ring-4 focus:ring-brand/15'"
          >
        </div>

        <!-- Submit Button -->
        <div class="flex items-center gap-4 pt-2">
          <button 
            v-if="pendingProfileRequest" 
            type="button" 
            disabled 
            class="px-6 py-3.5 rounded-2xl bg-slate-200 text-slate-500 text-xs font-bold cursor-not-allowed flex items-center gap-2"
          >
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 08-8 0v4h8z"/>
            </svg>
            <span>Request Pending Review</span>
          </button>
          <button 
            v-else 
            type="submit" 
            class="px-6 py-3.5 rounded-2xl bg-brand hover:bg-brand-hover text-white text-xs font-bold shadow-md shadow-brand/20 transition"
          >
            Submit Change Request to HR
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
defineProps({
  employee: { type: Object, default: null },
  pendingProfileRequest: { type: Object, default: null },
  csrfToken: { type: String, required: true },
  updateProfileRoute: { type: String, default: '#' },
});
</script>