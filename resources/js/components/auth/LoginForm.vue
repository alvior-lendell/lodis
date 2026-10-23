<template>
  <form 
    :action="currentFormAction" 
    method="POST" 
    autocomplete="off" 
    @submit="handleSubmit" 
    class="space-y-5 transition-opacity duration-200"
    :class="{ 'opacity-85 pointer-events-none': isSubmitting || isRemovingAccount }"
    :style="{ '--brand-color': brandColor, '--brand-hover': brandHoverColor }"
  >
    <input type="hidden" name="_token" :value="csrfToken">

    <!-- Subsystem Dynamic Header -->
    <div class="text-center mb-6">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
        Sign in to
      </p>

      <img 
        v-if="systemLogo" 
        :src="systemLogo" 
        :alt="systemName" 
        class="h-10 w-auto mx-auto mb-3 object-contain drop-shadow-sm"
      />
      <h3 v-else class="text-2xl font-extrabold text-slate-900 tracking-tight mb-1">
        {{ systemName }}
      </h3>

      <p class="text-xs text-slate-500 mt-1">
        Enter your credentials to access your portal session.
      </p>
    </div>

    <!-- ========================================================================= -->
    <!-- STATE 1: MULTIPLE ACCOUNTS GRID (Facebook-Style Grid when > 1 accounts)   -->
    <!-- ========================================================================= -->
    <div v-if="isMultiAccountGridView" class="space-y-4">
      <div class="flex items-center justify-between px-1">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
          Saved Accounts
        </h4>
        <span class="text-[11px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
          Recognized Workstation
        </span>
      </div>

      <!-- Accounts Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <!-- Account Card Tile -->
        <div 
          v-for="acc in accounts" 
          :key="acc.id || acc.user_id"
          class="group relative p-4 rounded-3xl bg-slate-50/90 border border-slate-200/90 hover:border-slate-300 hover:bg-white hover:shadow-md transition-all duration-200 cursor-pointer flex flex-col items-center text-center select-none"
          @click="selectAccount(acc)"
        >
          <!-- Remove individual account button -->
          <button 
            type="button" 
            @click.stop="removeAccount(acc)" 
            :disabled="isRemovingAccount"
            title="Remove this account"
            class="absolute top-2.5 right-2.5 p-1 rounded-full text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>

          <!-- Avatar -->
          <div class="relative mb-2.5 mt-1">
            <img 
              v-if="acc.avatar_url" 
              :src="acc.avatar_url" 
              alt="Profile Photo" 
              class="w-14 h-14 rounded-2xl object-cover ring-2 ring-white shadow-md border border-slate-200/60 group-hover:scale-105 transition-transform"
            />
            <div 
              v-else 
              class="w-14 h-14 rounded-2xl flex items-center justify-center font-extrabold text-white text-lg shadow-md border border-white/20 group-hover:scale-105 transition-transform"
              :style="{ backgroundColor: brandColor }"
            >
              {{ acc.initials }}
            </div>

            <!-- 1-Click indicator badge -->
            <div 
              v-if="acc.one_click_enabled" 
              class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-amber-500 border-2 border-white flex items-center justify-center text-white shadow-sm"
              title="1-Click Login Enabled"
            >
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div 
              v-else 
              class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white shadow-sm"
              title="Recognized Workstation"
            >
              <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
          </div>

          <!-- Name & Masked Login -->
          <h5 class="text-sm font-extrabold text-slate-900 truncate w-full tracking-tight group-hover:text-brand transition">
            {{ acc.name }}
          </h5>
          <p class="text-[11px] text-slate-500 truncate w-full font-mono font-medium mt-0.5">
            {{ acc.redacted_login || acc.login }}
          </p>

          <!-- Action Hint Badge -->
          <span class="mt-2 text-[10px] font-bold px-2 py-0.5 rounded-full text-slate-600 bg-slate-100 group-hover:bg-slate-200 transition">
            {{ acc.one_click_enabled ? '⚡ 1-Click Login' : 'Enter Password' }}
          </span>
        </div>

        <!-- Add Another Account Tile -->
        <div 
          class="group p-4 rounded-3xl border-2 border-dashed border-slate-200 hover:border-slate-400 hover:bg-slate-50/50 transition-all duration-200 cursor-pointer flex flex-col items-center justify-center text-center min-h-[140px] select-none"
          @click="switchToAnotherAccount"
        >
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 group-hover:text-slate-700 group-hover:bg-slate-200 flex items-center justify-center mb-2 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
          </div>
          <span class="text-xs font-bold text-slate-700 group-hover:text-slate-900 transition">
            Add Account
          </span>
          <span class="text-[10px] text-slate-400 font-medium">Log into another profile</span>
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STATE 2: SINGLE ACTIVE ACCOUNT VIEW (1-Click or Password prompt)          -->
    <!-- ========================================================================= -->
    <div v-else-if="selectedAccount && !useAnotherAccount" class="space-y-4">
      <!-- Back to Accounts Grid Button (if multiple accounts saved) -->
      <div v-if="accounts.length > 1" class="flex items-center justify-between pb-1">
        <button 
          type="button" 
          @click="selectedAccount = null"
          class="text-xs font-bold text-slate-600 hover:text-slate-900 transition inline-flex items-center gap-1.5 py-1 px-2 rounded-xl hover:bg-slate-100"
        >
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          <span>All saved accounts</span>
        </button>
      </div>

      <!-- Profile Account Card -->
      <div class="relative p-5 rounded-3xl bg-slate-50/90 border border-slate-200/90 shadow-sm transition hover:border-slate-300">
        <!-- Remove / Forget button -->
        <button 
          type="button" 
          @click="removeAccount(selectedAccount)" 
          :disabled="isRemovingAccount"
          title="Remove this account"
          class="absolute top-3.5 right-3.5 p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>

        <div class="flex items-center gap-4">
          <!-- Avatar -->
          <div class="relative shrink-0">
            <img 
              v-if="selectedAccount.avatar_url" 
              :src="selectedAccount.avatar_url" 
              alt="Profile Photo" 
              class="w-16 h-16 rounded-2xl object-cover ring-2 ring-white shadow-md border border-slate-200/50"
            />
            <div 
              v-else 
              class="w-16 h-16 rounded-2xl flex items-center justify-center font-extrabold text-white text-xl shadow-md border border-white/20"
              :style="{ backgroundColor: brandColor }"
            >
              {{ selectedAccount.initials }}
            </div>
            <!-- Verified Workstation Badge -->
            <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white shadow-sm" title="Recognized Workstation">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
          </div>

          <!-- Account Details -->
          <div class="min-w-0 flex-1 pr-6">
            <div class="flex items-center gap-1.5 mb-1">
              <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                Recognized Workstation
              </span>
            </div>
            <h4 class="text-base font-extrabold text-slate-900 truncate tracking-tight">
              {{ selectedAccount.name }}
            </h4>
            <p class="text-xs text-slate-500 truncate font-mono font-medium">
              {{ selectedAccount.redacted_login || selectedAccount.login }}
            </p>
          </div>
        </div>
      </div>

      <!-- Hidden inputs for authentication -->
      <input type="hidden" name="login" :value="selectedAccount.login">
      <input type="hidden" name="user_id" :value="selectedAccount.user_id">

      <!-- Password Prompt (when 1-click is not active or user clicked password mode) -->
      <div v-if="!isOneClickActive" class="space-y-4">
        <div>
          <div class="flex justify-between items-center mb-2">
            <label for="saved_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
            <a 
              v-if="forgotPasswordUrl" 
              :href="forgotPasswordUrl" 
              class="dynamic-link text-xs font-bold transition"
            >Forgot Password?</a>
          </div>
          <div class="relative rounded-2xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <input 
              id="saved_password" 
              :type="showPassword ? 'text' : 'password'" 
              name="password" 
              v-model="form.password"
              :readonly="isSubmitting"
              @keyup="checkCapsLock"
              @keydown="checkCapsLock"
              @blur="isCapsLockOn = false"
              required 
              autofocus
              autocomplete="current-password"
              class="dynamic-input block w-full pl-11 pr-11 py-3.5 rounded-2xl border border-slate-200 text-sm outline-none transition font-medium placeholder:text-slate-400"
              placeholder="••••••••"
            >

            <button 
              type="button" 
              @click="showPassword = !showPassword" 
              title="Toggle password visibility"
              class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 outline-none transition z-10"
            >
              <svg v-if="!showPassword" class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <svg v-else class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
              </svg>
            </button>
          </div>

          <!-- Caps Lock Warning -->
          <div v-if="isCapsLockOn" class="text-[11px] font-bold text-amber-600 mt-1.5 flex items-center gap-1.5 animate-pulse">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Caps Lock is turned ON</span>
          </div>
        </div>

        <!-- 1-Click Login Toggle -->
        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
          <label for="enable_one_click_saved" class="flex items-center justify-between cursor-pointer select-none group">
            <div class="flex flex-col pr-2">
              <span class="text-xs font-bold text-slate-800 group-hover:text-slate-950 transition">Enable 1-Click Login</span>
              <span class="text-[11px] text-slate-400 font-medium leading-tight">Log in directly next time without entering password</span>
            </div>
            <div class="relative inline-flex items-center shrink-0">
              <input 
                type="checkbox" 
                id="enable_one_click_saved" 
                name="enable_one_click" 
                v-model="enableOneClick" 
                value="1"
                class="sr-only peer"
              >
              <div 
                class="w-9 h-5 bg-slate-200 rounded-full peer transition-colors duration-200"
                :style="{ backgroundColor: enableOneClick ? brandColor : '' }"
              ></div>
              <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-transform duration-200 peer-checked:translate-x-4 shadow-sm pointer-events-none"></div>
            </div>
          </label>
        </div>
      </div>

      <!-- Action Button -->
      <button 
        type="submit" 
        :disabled="isSubmitting"
        class="dynamic-btn w-full py-4 px-5 rounded-2xl text-white text-sm font-bold transition duration-200 ease-in-out flex items-center justify-center gap-2 group shadow-lg disabled:opacity-85 disabled:cursor-not-allowed"
      >
        <svg v-if="isSubmitting" class="animate-spin h-5 w-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <template v-else>
          <template v-if="isOneClickActive">
            <span>Continue as {{ selectedAccount.first_name }}</span>
            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
          </template>
          <template v-else>
            <span>Sign In as {{ selectedAccount.first_name }}</span>
          </template>
        </template>
      </button>

      <!-- Sub-options for Selected Account -->
      <div class="pt-1 text-center space-y-2">
        <div v-if="isOneClickActive">
          <button 
            type="button" 
            @click="forcePasswordMode = true"
            class="text-xs font-semibold text-slate-500 hover:text-slate-900 transition underline underline-offset-4"
          >
            Sign in with password instead
          </button>
        </div>

        <div>
          <button 
            type="button" 
            @click="switchToAnotherAccount"
            class="text-xs font-bold text-slate-600 hover:text-slate-900 transition inline-flex items-center gap-1.5 py-1 px-3 rounded-xl hover:bg-slate-100"
          >
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <span>Log into another account</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STATE 3: STANDARD CREDENTIALS FORM (Untrusted / Log into another account) -->
    <!-- ========================================================================= -->
    <div v-else class="space-y-4">
      <!-- Back to saved accounts chip -->
      <div v-if="accounts.length > 0" class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-sm">
        <span class="text-xs text-slate-600 font-medium">
          Have a saved account?
        </span>
        <button 
          type="button" 
          @click="backToSavedAccounts" 
          class="dynamic-link text-xs font-bold"
        >
          View saved accounts ({{ accounts.length }})
        </button>
      </div>

      <!-- Employee ID / Email Field -->
      <div>
        <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Employee ID or Email</label>
        <div class="relative rounded-2xl shadow-sm">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <input 
            id="login" 
            type="text" 
            name="login" 
            v-model="form.login"
            :readonly="isSubmitting"
            required 
            autofocus 
            autocomplete="off"
            class="dynamic-input block w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 text-sm outline-none transition font-medium placeholder:text-slate-400"
            placeholder="Employee ID or Email"
          >
        </div>
      </div>

      <!-- Password Field -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <label for="standard_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
          <a 
            v-if="forgotPasswordUrl" 
            :href="forgotPasswordUrl" 
            class="dynamic-link text-xs font-bold transition"
          >Forgot Password?</a>
        </div>
        <div class="relative rounded-2xl shadow-sm">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
          </div>
          <input 
            id="standard_password" 
            :type="showPassword ? 'text' : 'password'" 
            name="password" 
            v-model="form.password"
            :readonly="isSubmitting"
            @keyup="checkCapsLock"
            @keydown="checkCapsLock"
            @blur="isCapsLockOn = false"
            required 
            autocomplete="current-password"
            class="dynamic-input block w-full pl-11 pr-11 py-3.5 rounded-2xl border border-slate-200 text-sm outline-none transition font-medium placeholder:text-slate-400"
            placeholder="••••••••"
          >

          <button 
            type="button" 
            @click="showPassword = !showPassword" 
            title="Toggle password visibility"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 outline-none transition z-10"
          >
            <svg v-if="!showPassword" class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg v-else class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
            </svg>
          </button>
        </div>

        <!-- Caps Lock Warning -->
        <div v-if="isCapsLockOn" class="text-[11px] font-bold text-amber-600 mt-1.5 flex items-center gap-1.5 animate-pulse">
          <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <span>Caps Lock is turned ON</span>
        </div>
      </div>

      <!-- 1-Click Login Toggle on Standard Form -->
      <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
        <label for="enable_one_click_standard" class="flex items-center justify-between cursor-pointer select-none group">
          <div class="flex flex-col pr-2">
            <span class="text-xs font-bold text-slate-800 group-hover:text-slate-950 transition">Enable 1-Click Login</span>
            <span class="text-[11px] text-slate-400 font-medium leading-tight">Remember account & allow 1-click sign-in on this device</span>
          </div>
          <div class="relative inline-flex items-center shrink-0">
            <input 
              type="checkbox" 
              id="enable_one_click_standard" 
              name="enable_one_click" 
              v-model="enableOneClick" 
              value="1"
              class="sr-only peer"
            >
            <div 
              class="w-9 h-5 bg-slate-200 rounded-full peer transition-colors duration-200"
              :style="{ backgroundColor: enableOneClick ? brandColor : '' }"
            ></div>
            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-transform duration-200 peer-checked:translate-x-4 shadow-sm pointer-events-none"></div>
          </div>
        </label>
      </div>

      <!-- TURNSTILE SLOT: ONLY rendered for standard credentials on untrusted/new logins -->
      <slot name="turnstile"></slot>

      <!-- Standard Submit Button -->
      <button 
        type="submit" 
        :disabled="isSubmitting"
        class="dynamic-btn w-full py-4 px-5 rounded-2xl text-white text-sm font-bold transition duration-200 ease-in-out flex items-center justify-center gap-2 group shadow-lg disabled:opacity-85 disabled:cursor-not-allowed"
      >
        <svg v-if="isSubmitting" class="animate-spin h-5 w-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>{{ isSubmitting ? 'Authenticating...' : 'Authenticate & Sign In' }}</span>
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  actionUrl: { type: String, required: true },
  oneClickUrl: { type: String, default: '' },
  forgetDeviceUrl: { type: String, default: '' },
  savedAccounts: { type: Array, default: () => [] },
  csrfToken: { type: String, required: true },
  oldLogin: { type: String, default: '' },
  forgotPasswordUrl: { type: String, default: '' },
  systemName: { type: String, default: 'LODISv2' },
  systemLogo: { type: String, default: '' },
  brandColor: { type: String, default: '#00687A' },
  brandHoverColor: { type: String, default: '#005261' },
});

const accounts = ref([...(props.savedAccounts || [])]);
const selectedAccount = ref(accounts.value.length === 1 ? accounts.value[0] : null);
const useAnotherAccount = ref(false);
const forcePasswordMode = ref(false);
const enableOneClick = ref(selectedAccount.value ? Boolean(selectedAccount.value.one_click_enabled) : false);

const form = ref({
  login: props.oldLogin || (selectedAccount.value ? selectedAccount.value.login : ''),
  password: '',
});

// View state helpers
const isMultiAccountGridView = computed(() => {
  return accounts.value.length > 1 && !selectedAccount.value && !useAnotherAccount.value;
});

const isOneClickActive = computed(() => {
  return selectedAccount.value && !useAnotherAccount.value && selectedAccount.value.one_click_enabled && !forcePasswordMode.value;
});

const currentFormAction = computed(() => {
  if (isOneClickActive.value && props.oneClickUrl) {
    return props.oneClickUrl;
  }
  return props.actionUrl;
});

const showPassword = ref(false);
const isCapsLockOn = ref(false);
const isSubmitting = ref(false);
const isRemovingAccount = ref(false);

let widgetId = null;
let turnstileTimer = null;

const renderTurnstile = () => {
  // Only render turnstile when in standard form mode
  if (!useAnotherAccount.value && accounts.value.length > 0) {
    return false;
  }

  const container = document.getElementById('turnstile-container');
  if (!container || !window.turnstile) return false;

  const sitekey = container.getAttribute('data-sitekey');
  if (!sitekey) return false;

  if (widgetId !== null && container.querySelector('iframe, input[name="cf-turnstile-response"]')) {
    return true;
  }

  if (widgetId !== null && window.turnstile) {
    try {
      window.turnstile.remove(widgetId);
    } catch (e) {}
    widgetId = null;
  }

  try {
    container.innerHTML = '';

    widgetId = window.turnstile.render('#turnstile-container', {
      sitekey: sitekey,
      theme: 'light',
      size: 'flexible',
      appearance: 'always',
      'expired-callback': () => {
        if (widgetId !== null && window.turnstile) {
          window.turnstile.reset(widgetId);
        }
      },
    });
    return true;
  } catch (error) {
    console.warn('[Turnstile] Render attempt warning:', error);
    return false;
  }
};

onMounted(() => {
  if (renderTurnstile()) return;

  window.onloadTurnstileCallback = () => {
    renderTurnstile();
  };

  let attempts = 0;
  turnstileTimer = setInterval(() => {
    attempts++;
    if (renderTurnstile() || attempts >= 100) {
      clearInterval(turnstileTimer);
    }
  }, 100);
});

onUnmounted(() => {
  if (turnstileTimer) {
    clearInterval(turnstileTimer);
  }
  if (widgetId !== null && window.turnstile) {
    try {
      window.turnstile.remove(widgetId);
    } catch (e) {}
  }
});

watch(useAnotherAccount, (isAnother) => {
  if (isAnother) {
    nextTick(() => {
      renderTurnstile();
    });
  }
});

const selectAccount = (acc) => {
  selectedAccount.value = acc;
  useAnotherAccount.value = false;
  forcePasswordMode.value = false;
  enableOneClick.value = Boolean(acc.one_click_enabled);
  form.value.password = '';
  form.value.login = acc.login;
};

const backToSavedAccounts = () => {
  useAnotherAccount.value = false;
  selectedAccount.value = accounts.value.length === 1 ? accounts.value[0] : null;
};

const switchToAnotherAccount = () => {
  useAnotherAccount.value = true;
  selectedAccount.value = null;
  form.value.login = '';
  form.value.password = '';
};

const checkCapsLock = (event) => {
  if (event.getModifierState && event.getModifierState('CapsLock')) {
    isCapsLockOn.value = true;
  } else {
    isCapsLockOn.value = false;
  }
};

const handleSubmit = () => {
  isSubmitting.value = true;
};

const removeAccount = async (acc) => {
  if (!confirm(`Remove ${acc.name}'s saved account from this computer?`)) return;
  isRemovingAccount.value = true;
  try {
    const res = await fetch(props.forgetDeviceUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': props.csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ user_id: acc.user_id }),
    });
    if (res.ok) {
      accounts.value = accounts.value.filter(a => a.user_id !== acc.user_id);
      if (selectedAccount.value && selectedAccount.value.user_id === acc.user_id) {
        selectedAccount.value = accounts.value.length === 1 ? accounts.value[0] : null;
      }
      if (accounts.value.length === 0) {
        useAnotherAccount.value = true;
      }
    }
  } catch (err) {
    console.error('Failed to remove account:', err);
  } finally {
    isRemovingAccount.value = false;
  }
};
</script>

<style scoped>
/* Dynamic Input Focus Border & Glow Ring */
.dynamic-input:focus {
  border-color: var(--brand-color) !important;
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-color) 18%, transparent) !important;
}

/* Dynamic Links */
.dynamic-link {
  color: var(--brand-color);
}
.dynamic-link:hover {
  color: var(--brand-hover);
  text-decoration: underline;
}

/* Dynamic Submit Button */
.dynamic-btn {
  background-color: var(--brand-color);
  box-shadow: 0 10px 25px -5px color-mix(in srgb, var(--brand-color) 30%, transparent);
}
.dynamic-btn:hover {
  background-color: var(--brand-hover);
  box-shadow: 0 15px 30px -5px color-mix(in srgb, var(--brand-color) 40%, transparent);
}
</style>