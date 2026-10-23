<template>
  <div
    v-if="isRouteLoading"
    class="space-y-8 animate-pulse"
    role="status"
    aria-label="Loading dashboard"
  >
    <span class="sr-only">Loading dashboard...</span>

    <div class="rounded-2xl bg-brand/10 p-6 sm:p-8">
      <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
        <div class="w-full space-y-3">
          <div class="h-3 w-44 rounded bg-brand/20"></div>
          <div class="h-8 w-64 max-w-full rounded bg-brand/20"></div>
          <div class="h-3 w-72 max-w-full rounded bg-brand/20"></div>
        </div>
        <div class="flex flex-wrap gap-3">
          <div class="h-16 w-44 rounded-xl bg-brand/20"></div>
          <div class="h-16 w-52 rounded-xl bg-brand/20"></div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
      <div class="flex items-center justify-between gap-4">
        <div class="space-y-2">
          <div class="h-4 w-40 rounded bg-slate-200"></div>
          <div class="h-3 w-64 max-w-full rounded bg-slate-100"></div>
        </div>
        <div class="h-9 w-28 rounded-lg bg-slate-100"></div>
      </div>
    </div>

    <div class="space-y-4">
      <div class="h-5 w-48 rounded bg-slate-200"></div>
      <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="card in 6"
          :key="card"
          class="rounded-2xl border border-slate-200 bg-white p-5"
        >
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-slate-100"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 w-2/3 rounded bg-slate-200"></div>
              <div class="h-3 w-1/2 rounded bg-slate-100"></div>
            </div>
          </div>
          <div class="mt-5 space-y-2">
            <div class="h-3 w-full rounded bg-slate-100"></div>
            <div class="h-3 w-4/5 rounded bg-slate-100"></div>
          </div>
          <div class="mt-5 h-9 rounded-lg bg-slate-100"></div>
        </div>
      </div>
    </div>
  </div>

  <router-view v-else></router-view>
</template>

<script setup>
import { isRouteLoading } from '../router';
</script>
