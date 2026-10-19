<template>
  <div>
    <!-- Status Session Alert -->
    <div v-if="statusMessage" class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ statusMessage }}</span>
      </div>
    </div>

    <!-- Hero Greeting & Widget Banner -->
    <div class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-brand to-brand-hover text-white shadow-md relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      <div class="z-10">
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-brand-light/80">
          <span>{{ liveDate }}</span>
          <span>•</span>
          <span class="font-mono">{{ liveClock }}</span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1">
          {{ greetingText }}, {{ greetingName }}
        </h2>
        <p class="text-xs text-brand-light/90 mt-1">Access your operational applications and system modules below.</p>
      </div>

      <div class="z-10 flex flex-wrap items-center gap-3 sm:gap-4">
        <!-- Weather Card -->
        <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/10 flex items-center gap-3 min-w-[170px] max-w-xs">
          <div class="text-amber-300 shrink-0">
            <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
          <div class="overflow-hidden min-w-0">
            <div class="flex items-center gap-2">
              <span class="text-sm font-extrabold leading-none">{{ weather.temp }}</span>
              <span class="text-[10px] text-amber-200 font-semibold truncate">{{ weather.condition }}</span>
            </div>
            <span class="text-[10px] text-brand-light/90 block mt-1 truncate font-medium" :title="weather.city">
              {{ weather.city }}
            </span>
          </div>
        </div>

        <!-- Schedule / Nearest Event Card -->
        <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/10 flex items-center gap-3 max-w-xs min-w-[200px]">
          <div class="p-2 rounded-lg shrink-0" :class="scheduleInfo.isToday ? 'bg-emerald-400/20 text-emerald-300' : 'bg-amber-400/20 text-amber-300'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <div class="overflow-hidden w-full">
            <div class="flex items-center justify-between gap-1 mb-0.5">
              <span class="text-[10px] font-semibold uppercase tracking-wider" :class="scheduleInfo.isToday ? 'text-emerald-200' : 'text-amber-200'">
                {{ scheduleInfo.eventType || 'Schedule' }}
              </span>
              <span v-if="scheduleInfo.isToday" class="px-1.5 py-0.5 text-[9px] font-extrabold uppercase bg-emerald-500/30 text-emerald-200 rounded animate-pulse">
                Today
              </span>
            </div>

            <h4 class="text-xs font-bold text-white truncate" :title="scheduleInfo.combinedTitle || 'No upcoming events scheduled'">
              {{ scheduleInfo.combinedTitle || 'No upcoming events scheduled' }}
            </h4>

            <span v-if="scheduleInfo.nearestDateFormatted" class="text-[10px] text-brand-light/80 block mt-0.5">
              {{ scheduleInfo.nearestDateFormatted }}
              <template v-if="scheduleInfo.distanceLabel && !scheduleInfo.isToday">
                • <span class="font-bold text-amber-200">{{ scheduleInfo.distanceLabel }}</span>
              </template>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Administrative Metrics Section -->
    <div v-if="['Superadmin', 'Admin'].includes(userRole)" class="mb-8 p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="p-2.5 rounded-xl bg-brand-light text-brand shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-slate-900">Administrative Metrics</h3>
          <p class="text-xs text-slate-500 mt-0.5">Overview of active application infrastructure and permission deployments.</p>
        </div>
      </div>
      <div class="flex items-center gap-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
        <div class="text-right">
          <span class="block text-xs text-slate-500">Total Systems</span>
          <span class="text-base font-extrabold text-slate-900">{{ systemList.length }}</span>
        </div>
        <div class="h-8 w-px bg-slate-200"></div>
        <div class="text-right">
          <span class="block text-xs text-slate-500">User Grants</span>
          <span class="text-base font-extrabold text-brand">{{ authorizedCount }}</span>
        </div>
      </div>
    </div>

    <!-- Section Header & Controls -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">System Launcher Grid</h2>
        <p class="text-xs text-slate-500 mt-1">Authorized applications are prioritized. Systems are grouped by status and access.</p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Authorized Badge -->
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-600 shadow-sm">
          <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
          {{ authorizedCount }} Authorized
        </span>

        <!-- Windows Explorer Style View Dropdown -->
        <div class="relative">
          <button 
            @click="isViewMenuOpen = !isViewMenuOpen" 
            type="button"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition"
          >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span>View</span>
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <!-- Menu Backdrop -->
          <div v-if="isViewMenuOpen" @click="isViewMenuOpen = false" class="fixed inset-0 z-20"></div>

          <!-- Dropdown Popup -->
          <div 
            v-if="isViewMenuOpen" 
            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 z-30 text-xs font-medium text-slate-700"
          >
            <button 
              @click="currentView = 'grid'; isViewMenuOpen = false"
              class="w-full px-3 py-2 flex items-center justify-between hover:bg-slate-50 transition text-left"
            >
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Large Cards</span>
              </div>
              <span v-if="currentView === 'grid'" class="w-1.5 h-1.5 rounded-full bg-brand"></span>
            </button>

            <button 
              @click="currentView = 'tiles'; isViewMenuOpen = false"
              class="w-full px-3 py-2 flex items-center justify-between hover:bg-slate-50 transition text-left"
            >
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M3 9h2m-2 6h2m14-6h2m-2 6h2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Compact Tiles</span>
              </div>
              <span v-if="currentView === 'tiles'" class="w-1.5 h-1.5 rounded-full bg-brand"></span>
            </button>

            <button 
              @click="currentView = 'list'; isViewMenuOpen = false"
              class="w-full px-3 py-2 flex items-center justify-between hover:bg-slate-50 transition text-left"
            >
              <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Details List</span>
              </div>
              <span v-if="currentView === 'list'" class="w-1.5 h-1.5 rounded-full bg-brand"></span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Global Empty State -->
    <div v-if="systemList.length === 0" class="mb-8 bg-white p-12 rounded-2xl border border-slate-200 text-center shadow-sm">
      <p class="text-sm font-semibold text-slate-700">No active systems registered in LODISv2.</p>
      <p class="text-xs text-slate-500 mt-1">Please contact your administrator to configure platform entries.</p>
    </div>

    <!-- SECTION 1: Authorized / Has Access -->
    <section v-if="authorizedSystems.length > 0" class="mb-10">
      <div class="flex items-center gap-3 mb-4">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Authorized Systems</h3>
        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
          {{ authorizedSystems.length }}
        </span>
        <div class="flex-1 h-px bg-slate-200"></div>
      </div>

      <!-- VIEW MODE 1: Large Grid -->
      <div v-if="currentView === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="system in authorizedSystems" 
          :key="system.id"
          class="bg-white rounded-2xl border border-slate-200 hover:border-brand/40 transition duration-200 flex flex-col justify-between overflow-hidden shadow-sm hover:shadow-md"
        >
          <div class="p-6 flex flex-col justify-between flex-1">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold"
                :class="system.is_online ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80'"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="system.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                <span>{{ system.is_online ? 'Online' : 'Offline' }}</span>
              </div>

              <div>
                <span v-if="system.user_role === 'Superadmin'" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Superadmin</span>
                <span v-else-if="system.user_role === 'Admin'" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Admin</span>
                <span v-else class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Employee</span>
              </div>
            </div>

            <div class="py-6 flex items-center justify-center min-h-[110px]">
              <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-24 w-auto object-contain transition-transform duration-200 hover:scale-105">
              <div v-else class="text-center">
                <h3 class="text-xl font-extrabold text-slate-900">{{ system.name }}</h3>
                <p v-if="system.description" class="text-xs text-slate-500 mt-1">{{ system.description }}</p>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100">
            <a :href="system.url" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 rounded-xl bg-brand hover:bg-brand-hover active:bg-brand-hover text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-2">
              <span>Launch Platform</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
          </div>
        </div>
      </div>

      <!-- VIEW MODE 2: Compact Tiles -->
      <div v-else-if="currentView === 'tiles'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
        <a 
          v-for="system in authorizedSystems" 
          :key="system.id" 
          :href="system.url" 
          target="_blank" 
          rel="noopener noreferrer" 
          class="p-4 bg-white border border-slate-200 rounded-xl hover:border-brand/50 hover:shadow-md transition flex flex-col items-center text-center group relative overflow-hidden"
        >
          <div class="absolute top-2 right-2">
            <span class="w-2 h-2 rounded-full block" :class="system.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
          </div>
          <div class="w-12 h-12 mb-2 flex items-center justify-center">
            <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-10 max-w-full object-contain group-hover:scale-105 transition">
            <span v-else class="text-xs font-extrabold text-slate-800">{{ system.name.substring(0, 3).toUpperCase() }}</span>
          </div>
          <span class="text-xs font-bold text-slate-800 group-hover:text-brand truncate w-full">{{ system.name }}</span>
          <span class="text-[10px] text-slate-400 capitalize mt-0.5">{{ system.user_role || 'Employee' }}</span>
        </a>
      </div>

      <!-- VIEW MODE 3: Details List -->
      <div v-else-if="currentView === 'list'" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm divide-y divide-slate-100">
        <div v-for="system in authorizedSystems" :key="system.id" class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50 transition">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-10 h-10 shrink-0 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center p-1">
              <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-8 max-w-full object-contain">
              <span v-else class="text-[10px] font-bold text-slate-600">{{ system.name.substring(0, 2) }}</span>
            </div>
            <div class="min-w-0">
              <h4 class="text-xs font-bold text-slate-900 truncate">{{ system.name }}</h4>
              <p class="text-[11px] text-slate-500 truncate">{{ system.description || 'No description provided' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-4 shrink-0">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold" :class="system.is_online ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
              <span class="w-1.5 h-1.5 rounded-full" :class="system.is_online ? 'bg-emerald-500' : 'bg-rose-500'"></span>
              {{ system.is_online ? 'Online' : 'Offline' }}
            </span>
            <span class="text-[11px] font-bold text-slate-500 px-2 py-0.5 bg-slate-100 rounded-md">{{ system.user_role || 'Employee' }}</span>
            <a :href="system.url" target="_blank" rel="noopener noreferrer" class="py-1.5 px-3 rounded-lg bg-brand hover:bg-brand-hover text-white text-xs font-semibold shadow-sm transition">
              Launch
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 2: Active / Restricted (No Access) -->
    <section v-if="restrictedSystems.length > 0" class="mb-10">
      <div class="flex items-center gap-3 mb-4">
        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Restricted Systems (Active)</h3>
        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 rounded-full">
          {{ restrictedSystems.length }}
        </span>
        <div class="flex-1 h-px bg-slate-200"></div>
      </div>

      <!-- VIEW MODE 1: Large Grid -->
      <div v-if="currentView === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="system in restrictedSystems" 
          :key="system.id"
          class="bg-slate-50/50 rounded-2xl border border-slate-200/80 opacity-80 flex flex-col justify-between overflow-hidden shadow-sm"
        >
          <div class="p-6 flex flex-col justify-between flex-1">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold"
                :class="system.is_online ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80'"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="system.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                <span>{{ system.is_online ? 'Online' : 'Offline' }}</span>
              </div>

              <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200 flex items-center gap-1">
                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 08-8 0v4h8z"/></svg>
                Restricted
              </span>
            </div>

            <div class="py-6 flex items-center justify-center min-h-[110px]">
              <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-24 w-auto object-contain grayscale opacity-60">
              <div v-else class="text-center">
                <h3 class="text-xl font-extrabold text-slate-700">{{ system.name }}</h3>
                <p v-if="system.description" class="text-xs text-slate-400 mt-1">{{ system.description }}</p>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 bg-slate-100/80 border-t border-slate-200/60">
            <button type="button" disabled class="w-full py-2.5 px-4 rounded-xl bg-slate-200 text-slate-400 text-xs font-semibold cursor-not-allowed">Access Restricted</button>
          </div>
        </div>
      </div>

      <!-- VIEW MODE 2: Compact Tiles -->
      <div v-else-if="currentView === 'tiles'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
        <div v-for="system in restrictedSystems" :key="system.id" class="p-4 bg-slate-50 border border-slate-200/80 rounded-xl opacity-75 flex flex-col items-center text-center relative">
          <div class="w-12 h-12 mb-2 flex items-center justify-center">
            <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-10 max-w-full object-contain grayscale opacity-50">
            <span v-else class="text-xs font-extrabold text-slate-500">{{ system.name.substring(0, 3).toUpperCase() }}</span>
          </div>
          <span class="text-xs font-bold text-slate-600 truncate w-full">{{ system.name }}</span>
          <span class="text-[10px] text-amber-600 font-semibold mt-0.5">Restricted</span>
        </div>
      </div>

      <!-- VIEW MODE 3: Details List -->
      <div v-else-if="currentView === 'list'" class="bg-slate-50/50 border border-slate-200 rounded-2xl overflow-hidden shadow-sm divide-y divide-slate-200/60 opacity-80">
        <div v-for="system in restrictedSystems" :key="system.id" class="p-4 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-10 h-10 shrink-0 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center p-1">
              <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-8 max-w-full object-contain grayscale opacity-60">
              <span v-else class="text-[10px] font-bold text-slate-500">{{ system.name.substring(0, 2) }}</span>
            </div>
            <div class="min-w-0">
              <h4 class="text-xs font-bold text-slate-700 truncate">{{ system.name }}</h4>
              <p class="text-[11px] text-slate-400 truncate">{{ system.description || 'No description provided' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-4 shrink-0">
            <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">Restricted</span>
            <button type="button" disabled class="py-1.5 px-3 rounded-lg bg-slate-200 text-slate-400 text-xs font-semibold cursor-not-allowed">No Access</button>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 3: Inactive / Disabled Systems -->
    <section v-if="inactiveSystems.length > 0" class="mb-10">
      <div class="flex items-center gap-3 mb-4">
        <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Inactive / Disabled Systems</h3>
        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200 rounded-full">
          {{ inactiveSystems.length }}
        </span>
        <div class="flex-1 h-px bg-slate-200"></div>
      </div>

      <!-- VIEW MODE 1: Large Grid -->
      <div v-if="currentView === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="system in inactiveSystems" 
          :key="system.id"
          class="bg-slate-100/50 rounded-2xl border border-slate-200/80 opacity-60 flex flex-col justify-between overflow-hidden shadow-sm"
        >
          <div class="p-6 flex flex-col justify-between flex-1">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                <span>Inactive</span>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-200/80 text-slate-500 border border-slate-300/80">Disabled</span>
            </div>

            <div class="py-6 flex items-center justify-center min-h-[110px]">
              <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-24 w-auto object-contain grayscale opacity-40">
              <div v-else class="text-center">
                <h3 class="text-xl font-extrabold text-slate-600">{{ system.name }}</h3>
                <p v-if="system.description" class="text-xs text-slate-400 mt-1">{{ system.description }}</p>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 bg-slate-100 border-t border-slate-200/80">
            <button type="button" disabled class="w-full py-2.5 px-4 rounded-xl bg-slate-200/80 text-slate-400 text-xs font-semibold cursor-not-allowed">System Inactive</button>
          </div>
        </div>
      </div>

      <!-- VIEW MODE 2: Compact Tiles -->
      <div v-else-if="currentView === 'tiles'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
        <div v-for="system in inactiveSystems" :key="system.id" class="p-4 bg-slate-100/60 border border-slate-200/60 rounded-xl opacity-60 flex flex-col items-center text-center">
          <div class="w-12 h-12 mb-2 flex items-center justify-center">
            <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-10 max-w-full object-contain grayscale opacity-40">
            <span v-else class="text-xs font-extrabold text-slate-400">{{ system.name.substring(0, 3).toUpperCase() }}</span>
          </div>
          <span class="text-xs font-bold text-slate-500 truncate w-full">{{ system.name }}</span>
          <span class="text-[10px] text-slate-400 mt-0.5">Disabled</span>
        </div>
      </div>

      <!-- VIEW MODE 3: Details List -->
      <div v-else-if="currentView === 'list'" class="bg-slate-100/40 border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm divide-y divide-slate-200/60 opacity-60">
        <div v-for="system in inactiveSystems" :key="system.id" class="p-4 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-10 h-10 shrink-0 bg-slate-200/50 border border-slate-200 rounded-lg flex items-center justify-center p-1">
              <img v-if="system.logo_url" :src="system.logo_url" :alt="system.name" class="max-h-8 max-w-full object-contain grayscale opacity-40">
              <span v-else class="text-[10px] font-bold text-slate-400">{{ system.name.substring(0, 2) }}</span>
            </div>
            <div class="min-w-0">
              <h4 class="text-xs font-bold text-slate-600 truncate">{{ system.name }}</h4>
              <p class="text-[11px] text-slate-400 truncate">{{ system.description || 'Disabled system module' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-4 shrink-0">
            <span class="text-[10px] font-semibold text-slate-500 bg-slate-200/80 px-2 py-0.5 rounded-full">Inactive</span>
            <button type="button" disabled class="py-1.5 px-3 rounded-lg bg-slate-200/80 text-slate-400 text-xs font-semibold cursor-not-allowed">Inactive</button>
          </div>
        </div>
      </div>
    </section>

    <!-- Standalone Superadmin Action -->
    <div v-if="userRole === 'Superadmin'" class="mt-6">
      <a 
        :href="createSystemUrl || '#'" 
        class="border-2 border-dashed border-slate-300 hover:border-brand rounded-2xl p-6 flex flex-col items-center justify-center text-center transition duration-200 group bg-slate-50/50 hover:bg-white min-h-[140px]"
      >
        <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-brand-light text-slate-400 group-hover:text-brand flex items-center justify-center mb-2 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <span class="text-xs font-bold text-slate-700 group-hover:text-brand transition">Add New System</span>
        <span class="text-[11px] text-slate-400 mt-0.5">Register an operational application module</span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  greetingName: { type: String, default: 'User' },
  userRole: { type: String, default: 'Employee' },
  statusMessage: { type: String, default: '' },
  createSystemUrl: { type: String, default: '#' },
  scheduleInfo: {
    type: Object,
    default: () => ({
      eventType: 'Schedule',
      isToday: false,
      combinedTitle: '',
      nearestDateFormatted: '',
      distanceLabel: '',
    }),
  },
  weatherLocation: {
    type: Object,
    default: () => ({
      city: 'Mandaluyong City',
      latitude: 14.5792,
      longitude: 121.0359,
    }),
  },
  systems: {
    type: Array,
    default: () => [],
  },
});

const liveClock = ref('--:--:-- --');
const liveDate = ref('--');
const greetingText = ref('Welcome back');
const systemList = ref([...props.systems]);
let clockInterval = null;

// View Mode State ('grid' | 'tiles' | 'list')
const currentView = ref('grid');
const isViewMenuOpen = ref(false);

const weather = ref({
  temp: '--°C',
  condition: 'Clear Sky',
  city: props.weatherLocation.city || 'Mandaluyong City',
});

// Category 1: Active & User Has Access (Online items first)
const authorizedSystems = computed(() => {
  return systemList.value
    .filter((s) => s.is_active && s.employee_has_access)
    .sort((a, b) => Number(b.is_online) - Number(a.is_online));
});

// Category 2: Active & No Access (Online items first)
const restrictedSystems = computed(() => {
  return systemList.value
    .filter((s) => s.is_active && !s.employee_has_access)
    .sort((a, b) => Number(b.is_online) - Number(a.is_online));
});

// Category 3: Inactive / Disabled
const inactiveSystems = computed(() => {
  return systemList.value.filter((s) => !s.is_active);
});

// Metric Count
const authorizedCount = computed(() => authorizedSystems.value.length);

const updateClockAndGreeting = () => {
  const now = new Date();
  const hours = now.getHours();

  liveClock.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
  liveDate.value = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

  if (hours >= 5 && hours < 12) {
    greetingText.value = 'Good morning';
  } else if (hours >= 12 && hours < 18) {
    greetingText.value = 'Good afternoon';
  } else {
    greetingText.value = 'Good evening';
  }
};

const fetchWeather = async () => {
  try {
    const lat = props.weatherLocation.latitude || 14.5792;
    const lng = props.weatherLocation.longitude || 121.0359;

    const response = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&current_weather=true`);
    if (!response.ok) throw new Error('Weather request failed');
    
    const data = await response.json();
    const temp = Math.round(data.current_weather.temperature);
    const code = data.current_weather.weathercode;

    weather.value.temp = `${temp}°C`;
    weather.value.city = props.weatherLocation.city || 'Mandaluyong City';

    let condition = 'Clear Sky';
    if (code === 0) condition = 'Clear Sky';
    else if ([1, 2, 3].includes(code)) condition = 'Partly Cloudy';
    else if ([45, 48].includes(code)) condition = 'Foggy';
    else if ([51, 53, 55, 61, 63, 65, 80, 81].includes(code)) condition = 'Rain Showers';
    else if ([95, 96, 99].includes(code)) condition = 'Thunderstorm';

    weather.value.condition = condition;
  } catch (err) {
    weather.value.temp = '29°C';
    weather.value.condition = 'Clear Sky';
    weather.value.city = props.weatherLocation.city || 'Mandaluyong City';
  }
};

onMounted(() => {
  updateClockAndGreeting();
  clockInterval = setInterval(updateClockAndGreeting, 1000);
  fetchWeather();

  if (typeof window.Echo !== 'undefined') {
    window.Echo.channel('dashboard-events')
      .listen('.health.updated', (e) => {
        const healthData = e.healthData || {};
        const systemStatuses = healthData.systems || {};

        systemList.value = systemList.value.map((sys) => {
          if (systemStatuses[sys.id] !== undefined) {
            return {
              ...sys,
              is_online: systemStatuses[sys.id] === 'operational',
            };
          }
          return sys;
        });

        const footerStatusText = document.getElementById('footer-global-status-text');
        const footerStatusDot = document.getElementById('footer-global-status-dot');
        if (footerStatusText && footerStatusDot) {
          footerStatusText.textContent = healthData.label || 'All Systems Operational';
          footerStatusDot.className = healthData.status === 'operational'
            ? 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse'
            : 'w-2 h-2 rounded-full bg-rose-500';
        }
      })
      .listen('.schedule.updated', () => {
        window.location.reload();
      })
      .listen('.system.updated', () => {
        window.location.reload();
      });
  }
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
});
</script>