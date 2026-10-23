<template>
  <div
    v-if="variant === 'navigation'"
    class="flex h-16 animate-pulse items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8"
    role="status"
    aria-label="Loading navigation"
  >
    <span class="sr-only">Loading navigation...</span>
    <div class="h-10 w-28 rounded bg-slate-100"></div>
    <div class="flex items-center gap-3">
      <div class="h-9 w-9 rounded-xl bg-slate-100"></div>
      <div class="h-9 w-28 rounded-xl bg-slate-100"></div>
    </div>
  </div>

  <div
    v-else-if="variant === 'slideshow'"
    class="flex min-h-[560px] animate-pulse flex-col justify-between gap-8 border-r border-slate-800/80 bg-slate-800 p-8 text-white sm:p-12 lg:min-h-screen lg:p-16"
    role="status"
    aria-label="Loading systems"
  >
    <span class="sr-only">Loading systems...</span>
    <div class="flex items-center justify-between">
      <div class="flex gap-2">
        <div v-for="item in 4" :key="item" class="h-6 w-14 rounded-full bg-white/10"></div>
      </div>
      <div class="h-9 w-20 rounded-xl bg-white/10"></div>
    </div>
    <div class="w-full max-w-xl space-y-5">
      <div class="h-20 w-40 rounded-xl bg-white/10"></div>
      <div class="h-7 w-48 rounded-full bg-white/10"></div>
      <div class="space-y-2">
        <div class="h-4 w-full rounded bg-white/10"></div>
        <div class="h-4 w-4/5 rounded bg-white/10"></div>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div v-for="item in 2" :key="item" class="h-24 rounded-2xl bg-white/10"></div>
      </div>
    </div>
    <div class="h-10 border-t border-white/10"></div>
  </div>

  <div
    v-else-if="variant === 'form'"
    class="animate-pulse space-y-6"
    role="status"
    aria-label="Loading form"
  >
    <span class="sr-only">Loading form...</span>
    <div class="space-y-3">
      <div class="h-7 w-56 max-w-full rounded bg-slate-200"></div>
      <div class="h-3 w-72 max-w-full rounded bg-slate-100"></div>
    </div>
    <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <div v-for="field in 3" :key="field" class="space-y-2">
        <div class="h-3 w-24 rounded bg-slate-200"></div>
        <div class="h-10 rounded-xl bg-slate-100"></div>
      </div>
      <div class="h-10 rounded-xl bg-brand/20"></div>
    </div>
  </div>

  <div
    v-else-if="variant === 'table'"
    class="animate-pulse space-y-6"
    role="status"
    aria-label="Loading list"
  >
    <span class="sr-only">Loading list...</span>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div class="space-y-2">
        <div class="h-7 w-56 max-w-full rounded bg-slate-200"></div>
        <div class="h-3 w-80 max-w-full rounded bg-slate-100"></div>
      </div>
      <div class="h-10 w-36 rounded-xl bg-brand/20"></div>
    </div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th v-for="column in 6" :key="column" class="px-6 py-4">
                <div class="h-3 w-20 rounded bg-slate-200"></div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="row in 6" :key="row">
              <td v-for="column in 6" :key="column" class="px-6 py-5">
                <div v-if="column === 2" class="space-y-2">
                  <div class="h-4 w-32 rounded bg-slate-200"></div>
                  <div class="h-3 w-40 rounded bg-slate-100"></div>
                </div>
                <div v-else class="h-6 w-20 rounded-full bg-slate-100"></div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div
    v-else
    class="animate-pulse space-y-6"
    role="status"
    aria-label="Loading content"
  >
    <span class="sr-only">Loading content...</span>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div class="space-y-2">
        <div class="h-7 w-56 max-w-full rounded bg-slate-200"></div>
        <div class="h-3 w-80 max-w-full rounded bg-slate-100"></div>
      </div>
      <div class="h-10 w-36 rounded-xl bg-slate-100"></div>
    </div>
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="card in 3"
        :key="card"
        class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
      >
        <div class="flex items-center gap-3">
          <div class="h-11 w-11 rounded-xl bg-slate-100"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 w-2/3 rounded bg-slate-200"></div>
            <div class="h-3 w-1/2 rounded bg-slate-100"></div>
          </div>
        </div>
        <div class="space-y-2">
          <div class="h-3 w-full rounded bg-slate-100"></div>
          <div class="h-3 w-4/5 rounded bg-slate-100"></div>
        </div>
        <div class="h-9 rounded-lg bg-slate-100"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'content',
  },
});
</script>
