<template>
  <div class="space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
      <div>
        <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Security Audit History</h3>
        <p class="text-xs text-slate-500 mt-0.5">Automated event logging of profile views, authentication activity, and security changes.</p>
      </div>

      <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              <th class="p-4">Security Event</th>
              <th class="p-4">IP Address</th>
              <th class="p-4">Timestamp</th>
              <th class="p-4 text-right">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
            <tr v-for="log in auditLogs" :key="log.id" class="hover:bg-slate-50/50 transition">
              <td class="p-4 font-bold text-slate-900">
                <span class="flex items-center gap-1.5" :class="log.is_failed ? 'text-rose-700' : 'text-emerald-700'">
                  <!-- Success Icon -->
                  <svg v-if="!log.is_failed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <!-- Failure Icon -->
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ log.eventTitle }}
                </span>
              </td>
              <td class="p-4 font-mono text-slate-500">{{ log.ip_address }}</td>
              <td class="p-4 text-slate-500">{{ log.created_at_formatted }} ({{ log.created_at_human }})</td>
              <td class="p-4 text-right">
                <span 
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold" 
                  :class="log.is_failed ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'"
                >
                  {{ log.status }}
                </span>
              </td>
            </tr>
            <tr v-if="auditLogs.length === 0">
              <td colspan="4" class="p-8 text-center text-slate-500 text-xs">No security audit logs recorded yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  auditLogs: { type: Array, default: () => [] },
});
</script>