<template>
  <div class="min-h-screen bg-ivory font-sans text-charcoal">
    <!-- Mobile Menu Button -->
    <button
      @click="sidebarOpen = !sidebarOpen"
      class="md:hidden fixed top-4 left-4 z-50 p-2.5 rounded-md bg-forest text-white shadow-lg hover:bg-forest-800 transition-colors"
    >
      <svg v-if="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
      <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Overlay -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="md:hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-20"
    ></div>

    <!-- Sidebar -->
    <div
      :class="[
        'fixed inset-y-0 left-0 w-64 bg-white border-r border-sand/30 shadow-lg z-30 transform transition-transform duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo Header -->
        <div class="flex items-center justify-between h-16 bg-forest px-4 shadow-sm">
          <router-link to="/" class="flex items-center space-x-2 text-white font-serif font-semibold text-lg tracking-wider">
            <div class="w-7 h-7 rounded-full bg-gold/20 border border-gold/40 flex items-center justify-center text-gold font-bold text-xs">A</div>
            <span>AURA Hotel</span>
          </router-link>
          
          <!-- Language Switcher -->
          <div class="flex items-center space-x-1">
            <button
              @click="changeLanguage('en')"
              :class="[
                'px-2 py-0.5 text-xs font-medium rounded transition-colors',
                currentLocale === 'en' 
                  ? 'bg-sand text-forest font-bold' 
                  : 'text-white/80 hover:bg-forest-600'
              ]"
            >
              EN
            </button>
            <button
              @click="changeLanguage('id')"
              :class="[
                'px-2 py-0.5 text-xs font-medium rounded transition-colors',
                currentLocale === 'id' 
                  ? 'bg-sand text-forest font-bold' 
                  : 'text-white/80 hover:bg-forest-600'
              ]"
            >
              ID
            </button>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto">
          <!-- Back to Public Site -->
          <router-link
            to="/"
            class="flex items-center px-3.5 py-2.5 text-xs uppercase tracking-wider text-gold bg-forest/5 hover:bg-forest/10 rounded-md mb-4 border border-gold/20 font-semibold transition-colors"
          >
            <svg class="w-4 h-4 mr-2.5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Public Website
          </router-link>

          <!-- Dashboard -->
          <router-link
            to="/dashboard"
            class="flex items-center px-3.5 py-2.5 text-sm text-charcoal rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
            active-class="bg-forest/10 text-forest font-semibold border-l-4 border-gold"
          >
            <svg class="w-5 h-5 mr-3 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            {{ $t('nav.dashboard') }}
          </router-link>

          <!-- Guests -->
          <router-link
            to="/guests"
            class="flex items-center px-3.5 py-2.5 text-sm text-charcoal rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
            active-class="bg-forest/10 text-forest font-semibold border-l-4 border-gold"
          >
            <svg class="w-5 h-5 mr-3 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            {{ $t('nav.guests') }}
          </router-link>

          <!-- Room Management -->
          <div>
            <button
              @click="roomMenuOpen = !roomMenuOpen"
              class="w-full flex items-center justify-between px-3.5 py-2.5 text-sm text-charcoal rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
              :class="{ 'bg-forest/10 text-forest': isRoomMenuActive }"
            >
              <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ $t('nav.roomManagement') }}
              </div>
              <svg
                :class="{ 'transform rotate-180': roomMenuOpen }"
                class="w-4 h-4 transition-transform text-taupe"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div v-show="roomMenuOpen" class="ml-4 mt-1 space-y-1">
              <router-link
                to="/rooms"
                class="flex items-center px-3.5 py-2 text-xs text-taupe rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
                active-class="bg-forest/10 text-forest font-semibold"
              >
                <svg class="w-3.5 h-3.5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                {{ $t('nav.rooms') }}
              </router-link>
              
              <router-link
                to="/room-types"
                class="flex items-center px-3.5 py-2 text-xs text-taupe rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
                active-class="bg-forest/10 text-forest font-semibold"
              >
                <svg class="w-3.5 h-3.5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                {{ $t('nav.roomTypes') }}
              </router-link>

              <router-link
                to="/bookings"
                class="flex items-center px-3.5 py-2 text-xs text-taupe rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
                active-class="bg-forest/10 text-forest font-semibold"
              >
                <svg class="w-3.5 h-3.5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                {{ $t('nav.roomBookings') }}
              </router-link>
            </div>
          </div>

          <!-- Hall Management -->
          <div>
            <button
              @click="hallMenuOpen = !hallMenuOpen"
              class="w-full flex items-center justify-between px-3.5 py-2.5 text-sm text-charcoal rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
              :class="{ 'bg-forest/10 text-forest': isHallMenuActive }"
            >
              <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ $t('nav.hallManagement') }}
              </div>
              <svg
                :class="{ 'transform rotate-180': hallMenuOpen }"
                class="w-4 h-4 transition-transform text-taupe"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div v-show="hallMenuOpen" class="ml-4 mt-1 space-y-1">
              <router-link
                to="/halls"
                class="flex items-center px-3.5 py-2 text-xs text-taupe rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
                active-class="bg-forest/10 text-forest font-semibold"
              >
                <svg class="w-3.5 h-3.5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                {{ $t('nav.halls') }}
              </router-link>
              
              <router-link
                to="/hall-bookings"
                class="flex items-center px-3.5 py-2 text-xs text-taupe rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
                active-class="bg-forest/10 text-forest font-semibold"
              >
                <svg class="w-3.5 h-3.5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                {{ $t('nav.hallBookings') }}
              </router-link>
            </div>
          </div>

          <!-- Services Menu -->
          <div>
            <button
              @click="servicesOpen = !servicesOpen"
              class="w-full flex items-center justify-between px-3.5 py-2.5 text-sm text-charcoal rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
              :class="{ 'bg-forest/10 text-forest': servicesOpen }"
            >
              <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ $t('nav.services') }}
              </div>
              <svg
                class="w-4 h-4 transition-transform text-taupe"
                :class="{ 'rotate-180': servicesOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div v-show="servicesOpen" class="ml-4 mt-1 space-y-1">
              <router-link
                to="/breakfast"
                class="flex items-center px-3.5 py-2 text-xs text-taupe rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
                active-class="bg-forest/10 text-forest font-semibold"
              >
                <svg class="w-3.5 h-3.5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                {{ $t('nav.breakfast') }}
              </router-link>
              
              <router-link
                to="/restaurant"
                class="flex items-center px-3.5 py-2 text-xs text-taupe rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
                active-class="bg-forest/10 text-forest font-semibold"
              >
                <svg class="w-3.5 h-3.5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                {{ $t('nav.restaurant') }}
              </router-link>
              
              <router-link
                to="/laundry"
                class="flex items-center px-3.5 py-2 text-xs text-taupe rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
                active-class="bg-forest/10 text-forest font-semibold"
              >
                <svg class="w-3.5 h-3.5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                {{ $t('nav.laundry') }}
              </router-link>
            </div>
          </div>

          <!-- Housekeeping -->
          <router-link
            to="/housekeeping"
            class="flex items-center px-3.5 py-2.5 text-sm text-charcoal rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
            active-class="bg-forest/10 text-forest font-semibold border-l-4 border-gold"
          >
            <svg class="w-5 h-5 mr-3 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            {{ $t('nav.housekeeping') }}
          </router-link>

          <!-- Payments -->
          <router-link
            to="/payments"
            class="flex items-center px-3.5 py-2.5 text-sm text-charcoal rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
            active-class="bg-forest/10 text-forest font-semibold border-l-4 border-gold"
          >
            <svg class="w-5 h-5 mr-3 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            {{ $t('nav.payments') }}
          </router-link>

          <!-- Settings -->
          <router-link
            to="/settings"
            class="flex items-center px-3.5 py-2.5 text-sm text-charcoal rounded-md hover:bg-sand/20 hover:text-forest transition-colors"
            active-class="bg-forest/10 text-forest font-semibold border-l-4 border-gold"
          >
            <svg class="w-5 h-5 mr-3 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ $t('nav.settings') }}
          </router-link>
        </nav>

        <!-- User section -->
        <div class="p-4 border-t border-sand/30 bg-ivory/50">
          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-forest text-gold border border-gold/40 flex items-center justify-center font-bold text-sm shadow-sm">
                {{ userInitials }}
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-charcoal truncate">
                {{ user?.name }}
              </p>
              <p class="text-xs text-taupe truncate">
                {{ user?.role?.display_name }}
              </p>
            </div>
          </div>
          <button
            @click="handleLogout"
            class="mt-3 w-full flex items-center justify-center px-4 py-2 border border-sand/40 rounded-md text-xs font-semibold uppercase tracking-wider text-charcoal hover:bg-white transition-colors shadow-xs"
          >
            <svg class="w-4 h-4 mr-2 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            {{ $t('nav.logout') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="md:ml-64">
      <main class="p-4 pt-16 md:pt-8 md:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useI18n } from 'vue-i18n'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const { locale } = useI18n()

const currentLocale = computed(() => locale.value)

const sidebarOpen = ref(false)
const roomMenuOpen = ref(true) // Default open
const hallMenuOpen = ref(true) // Default open
const servicesOpen = ref(true) // Default open

const user = computed(() => authStore.user)

// Close sidebar on mobile when route changes
watch(() => route.path, () => {
  sidebarOpen.value = false
})

// Check if Room Management menu is active
const isRoomMenuActive = computed(() => {
  return route.path === '/rooms' || route.path === '/room-types' || route.path === '/bookings'
})

// Check if Hall Management menu is active
const isHallMenuActive = computed(() => {
  return route.path === '/halls' || route.path === '/hall-bookings'
})

const userInitials = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}

function changeLanguage(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)
}
</script>
