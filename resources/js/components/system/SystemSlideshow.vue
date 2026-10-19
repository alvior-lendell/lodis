<template>
  <div 
    class="relative min-h-[560px] lg:min-h-screen p-8 sm:p-12 lg:p-16 flex flex-col justify-between text-white transition-all duration-700 ease-in-out overflow-hidden border-t lg:border-t-0 lg:border-r border-slate-800/80"
    :style="{ background: currentSystem.gradient }"
    @mouseenter="pauseAutoplay"
    @mouseleave="resumeAutoplay"
  >
    <!-- Ambient Radial Glow Effects -->
    <div 
      class="absolute -top-24 -left-24 w-96 h-96 rounded-full blur-[100px] pointer-events-none opacity-30 transition-all duration-1000" 
      :style="{ backgroundColor: currentSystem.brand_color }"
    ></div>
    <div 
      class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full blur-[100px] pointer-events-none opacity-25 transition-all duration-1000" 
      :style="{ backgroundColor: currentSystem.brand_color }"
    ></div>

    <!-- Geometric Grid Overlay -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:2.5rem_2.5rem] pointer-events-none opacity-60"></div>

    <!-- Top Carousel Navigation Controls -->
    <div class="relative z-10 flex items-center justify-between mb-6">
      <!-- Direct Tab Selector Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
        <button 
          v-for="(system, index) in systems" 
          :key="index"
          @click="goToSlide(index)"
          type="button"
          class="px-3 py-1 rounded-full text-[11px] font-bold transition-all duration-300 shrink-0"
          :class="currentIndex === index ? 'bg-white text-slate-900 shadow-md scale-105' : 'bg-white/10 text-white/70 hover:bg-white/20 hover:text-white'"
        >
          {{ system.tag }}
        </button>
      </div>

      <!-- Arrow Controls -->
      <div class="flex items-center gap-2 shrink-0 ml-4">
        <button 
          @click="prevSlide" 
          type="button"
          class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition active:scale-95 border border-white/10"
          aria-label="Previous Slide"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button 
          @click="nextSlide" 
          type="button"
          class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition active:scale-95 border border-white/10"
          aria-label="Next Slide"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Active Slide Content Container -->
    <div 
      class="relative z-10 my-auto transition-opacity duration-300" 
      :class="{ 'opacity-0 scale-98': isTransitioning, 'opacity-100 scale-100': !isTransitioning }"
    >
      <!-- System Logo -->
      <div class="mb-6">
        <img 
          :src="currentSystem.logo" 
          :alt="currentSystem.name" 
          class="h-16 lg:h-20 w-auto object-contain drop-shadow-2xl transition-transform duration-300 hover:scale-105"
        />
      </div>

      <!-- System Badge -->
      <div class="mb-4">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-white/10 backdrop-blur-md border border-white/20 text-white shadow-lg">
          <span 
            class="h-2 w-2 rounded-full animate-pulse" 
            :style="{ backgroundColor: currentSystem.brand_color, boxShadow: `0 0 10px ${currentSystem.brand_color}` }"
          ></span>
          <span>{{ currentSystem.badge }}</span>
        </span>
      </div>

      <!-- System Description -->
      <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-6 max-w-lg font-normal">
        {{ currentSystem.description }}
      </p>

      <!-- Slot for PWA Banner -->
      <slot name="pwa-slot"></slot>

      <!-- System Action Button / Direct System Login Button -->
      <div v-if="currentSystem.url" class="mb-6">
        <a 
          :href="currentSystem.url" 
          target="_blank" 
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition-all shadow-md active:scale-95 group backdrop-blur-md"
        >
          <span>Access {{ currentSystem.name }}</span>
          <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </a>
      </div>

      <!-- Static Features Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div 
          v-for="(feature, idx) in currentSystem.features" 
          :key="idx"
          class="p-4 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10 flex items-start gap-3.5 transition hover:bg-white/10"
        >
          <div class="p-2.5 rounded-xl bg-white/10 border border-white/10 text-white shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">{{ feature.title }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Pagination Dots & Footer Meta -->
    <div class="relative z-10 mt-8 pt-6 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-400 font-medium">
      <!-- Slideshow Indicator Bar -->
      <div class="flex items-center gap-2">
        <button 
          v-for="(_, index) in systems" 
          :key="index"
          @click="goToSlide(index)"
          type="button"
          class="h-1.5 rounded-full transition-all duration-500"
          :class="currentIndex === index ? 'w-8 bg-white' : 'w-2 bg-white/30 hover:bg-white/50'"
          :aria-label="`Go to slide ${index + 1}`"
        ></button>
      </div>

      <!-- Copyright & Version -->
      <div class="flex items-center gap-3">
        <span>&copy; {{ new Date().getFullYear() }} Lendell Outsourcing Solutions, Inc.</span>
        <span class="font-mono text-slate-500">v2.1.0</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SystemSlideshow',
  props: {
    systems: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      currentIndex: 0,
      timer: null,
      isTransitioning: false,
      intervalDuration: 6000
    }
  },
  computed: {
    currentSystem() {
      return this.systems[this.currentIndex]
    }
  },
  mounted() {
    this.startAutoplay()
  },
  beforeUnmount() {
    this.stopAutoplay()
  },
  methods: {
    startAutoplay() {
      this.stopAutoplay()
      this.timer = setInterval(() => {
        this.nextSlide()
      }, this.intervalDuration)
    },
    stopAutoplay() {
      if (this.timer) {
        clearInterval(this.timer)
        this.timer = null
      }
    },
    pauseAutoplay() {
      this.stopAutoplay()
    },
    resumeAutoplay() {
      this.startAutoplay()
    },
    goToSlide(index) {
      if (index === this.currentIndex) return
      this.triggerTransition(() => {
        this.currentIndex = index
      })
    },
    nextSlide() {
      this.triggerTransition(() => {
        this.currentIndex = (this.currentIndex + 1) % this.systems.length
      })
    },
    prevSlide() {
      this.triggerTransition(() => {
        this.currentIndex = (this.currentIndex - 1 + this.systems.length) % this.systems.length
      })
    },
    triggerTransition(callback) {
      this.isTransitioning = true
      setTimeout(() => {
        callback()
        this.isTransitioning = false
      }, 150)
    }
  }
}
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scale-98 {
  transform: scale(0.98);
}
</style>