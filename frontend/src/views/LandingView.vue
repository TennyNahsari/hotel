<template>
  <div class="min-h-screen pb-16 lg:pb-0 bg-ivory text-charcoal font-sans selection:bg-sand selection:text-forest">
    
    <!-- 01. NAVIGATION -->
    <header
      :class="[
        'fixed top-0 left-0 right-0 z-50 transition-all duration-500',
        isScrolled
          ? 'bg-ivory/95 backdrop-blur-md shadow-md py-4 text-forest'
          : 'bg-gradient-to-b from-black/70 via-black/30 to-transparent py-6 text-white'
      ]"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <!-- Logo -->
        <a href="#hero" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-full border border-gold/40 flex items-center justify-center bg-forest/20 backdrop-blur-sm group-hover:border-gold transition-colors">
            <span class="font-serif text-xl font-bold text-gold">A</span>
          </div>
          <div class="flex flex-col">
            <span class="font-serif text-xl sm:text-2xl tracking-widest font-semibold uppercase">AURA</span>
            <span class="text-[9px] tracking-[0.25em] text-sand uppercase -mt-1 font-sans">Hotels & Resorts</span>
          </div>
        </a>

        <!-- Desktop Menu -->
        <nav class="hidden lg:flex items-center space-x-7 text-xs tracking-wider uppercase font-medium">
          <a href="#hero" class="hover:text-gold transition-colors">{{ $t('landing.nav.home') }}</a>
          <a href="#hotel" class="hover:text-gold transition-colors">{{ $t('landing.nav.hotel') }}</a>
          <a href="#rooms" class="hover:text-gold transition-colors">{{ $t('landing.nav.rooms') }}</a>
          <a href="#facilities" class="hover:text-gold transition-colors">{{ $t('landing.nav.facilities') }}</a>
          <a href="#dining" class="hover:text-gold transition-colors">{{ $t('landing.nav.dining') }}</a>
          <a href="#experiences" class="hover:text-gold transition-colors">{{ $t('landing.nav.experiences') }}</a>
        </nav>

        <!-- CTAs & Language Switcher -->
        <div class="hidden sm:flex items-center space-x-3">
          <!-- Language Switcher -->
          <div class="flex items-center space-x-1 mr-1 bg-black/10 backdrop-blur-xs p-1 rounded border border-white/20">
            <button
              @click="changeLanguage('en')"
              :class="[
                'px-2 py-0.5 text-xs font-semibold rounded transition-colors',
                currentLocale === 'en'
                  ? 'bg-gold text-forest'
                  : 'text-white/80 hover:text-white'
              ]"
            >
              EN
            </button>
            <button
              @click="changeLanguage('id')"
              :class="[
                'px-2 py-0.5 text-xs font-semibold rounded transition-colors',
                currentLocale === 'id'
                  ? 'bg-gold text-forest'
                  : 'text-white/80 hover:text-white'
              ]"
            >
              ID
            </button>
          </div>

          <button
            @click="openTrackModal()"
            class="px-4 py-2.5 bg-transparent border border-gold/60 text-gold hover:bg-gold hover:text-forest text-xs font-semibold uppercase tracking-wider rounded transition-all shadow-sm"
          >
            {{ $t('landing.nav.trackStatus') }}
          </button>

          <button
            @click="openBookingModal()"
            class="px-5 py-2.5 bg-forest text-white text-xs font-semibold uppercase tracking-widest rounded hover:bg-forest-800 transition-all shadow-sm hover:shadow"
          >
            {{ $t('landing.nav.bookNow') }}
          </button>
        </div>

        <!-- Mobile Menu Toggle Button -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="lg:hidden p-2 rounded-md focus:outline-none"
          :class="isScrolled ? 'text-forest' : 'text-white'"
        >
          <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Menu Dropdown -->
      <div v-if="mobileMenuOpen" class="lg:hidden bg-forest text-white px-6 py-6 space-y-4 shadow-xl border-t border-gold/20">
        <!-- Mobile Language Switcher -->
        <div class="flex items-center justify-between pb-3 border-b border-forest-600">
          <span class="text-xs uppercase tracking-widest text-sand">Language / Bahasa</span>
          <div class="flex items-center space-x-2">
            <button
              @click="changeLanguage('en')"
              :class="['px-2.5 py-1 text-xs font-bold rounded', currentLocale === 'en' ? 'bg-gold text-forest' : 'text-white bg-forest-600']"
            >
              EN
            </button>
            <button
              @click="changeLanguage('id')"
              :class="['px-2.5 py-1 text-xs font-bold rounded', currentLocale === 'id' ? 'bg-gold text-forest' : 'text-white bg-forest-600']"
            >
              ID
            </button>
          </div>
        </div>

        <a @click="mobileMenuOpen = false" href="#hero" class="block py-2 text-sm uppercase tracking-wider hover:text-sand">{{ $t('landing.nav.home') }}</a>
        <a @click="mobileMenuOpen = false" href="#hotel" class="block py-2 text-sm uppercase tracking-wider hover:text-sand">{{ $t('landing.nav.hotel') }}</a>
        <a @click="mobileMenuOpen = false" href="#rooms" class="block py-2 text-sm uppercase tracking-wider hover:text-sand">{{ $t('landing.nav.rooms') }}</a>
        <a @click="mobileMenuOpen = false" href="#facilities" class="block py-2 text-sm uppercase tracking-wider hover:text-sand">{{ $t('landing.nav.facilities') }}</a>
        <a @click="mobileMenuOpen = false" href="#dining" class="block py-2 text-sm uppercase tracking-wider hover:text-sand">{{ $t('landing.nav.dining') }}</a>
        <a @click="mobileMenuOpen = false" href="#experiences" class="block py-2 text-sm uppercase tracking-wider hover:text-sand">{{ $t('landing.nav.experiences') }}</a>
        
        <div class="pt-4 border-t border-forest-600 space-y-2">
          <button
            @click="mobileMenuOpen = false; openTrackModal()"
            class="w-full text-center py-2.5 bg-forest-600 border border-gold/40 text-gold font-semibold text-xs uppercase tracking-widest rounded"
          >
            {{ $t('landing.nav.trackStatus') }}
          </button>
          <button
            @click="mobileMenuOpen = false; openBookingModal()"
            class="w-full text-center py-3 bg-gold text-forest font-semibold text-xs uppercase tracking-widest rounded shadow"
          >
            {{ $t('landing.nav.bookNow') }}
          </button>
        </div>
      </div>
    </header>

    <!-- 02. HERO SECTION -->
    <section id="hero" class="relative h-screen min-h-[650px] flex items-center justify-center overflow-hidden">
      <!-- Background Image with Ambient Zoom -->
      <div class="absolute inset-0 z-0">
        <img
          src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=2000&q=85"
          alt="AURA Luxury Hotel Exterior"
          class="w-full h-full object-cover scale-105 animate-[pulse_10s_infinite_alternate]"
        />
        <!-- Dark Transparent Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/50"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 max-w-5xl mx-auto px-4 text-center text-white space-y-6 animate-fade-in mt-12">
        <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20">
          <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
          <span class="text-xs uppercase tracking-[0.3em] font-medium text-sand">{{ $t('landing.hero.welcome') }}</span>
        </div>

        <h1 class="font-display text-4xl sm:text-6xl md:text-7xl font-normal leading-[1.1] tracking-tight">
          {{ $t('landing.hero.title') }}
        </h1>

        <p class="max-w-2xl mx-auto text-base sm:text-lg md:text-xl text-white/85 font-light leading-relaxed">
          {{ $t('landing.hero.description') }}
        </p>

        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
          <button
            @click="openBookingModal()"
            class="w-full sm:w-auto px-8 py-4 bg-forest text-white text-sm font-semibold uppercase tracking-widest rounded hover:bg-forest-800 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5"
          >
            {{ $t('landing.hero.bookStay') }}
          </button>
          <button
            @click="openTrackModal()"
            class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/30 text-white text-sm font-medium uppercase tracking-widest rounded transition-all"
          >
            Cek Status Pesanan
          </button>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <a href="#hotel" class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center text-white/60 hover:text-white transition-colors">
        <span class="text-[10px] uppercase tracking-[0.25em] mb-2">{{ $t('landing.hero.scroll') }}</span>
        <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 14l-7 7-7-7" />
        </svg>
      </a>
    </section>

    <!-- 03. HOTEL INTRODUCTION -->
    <section id="hotel" class="py-24 md:py-36 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
        <!-- Left Image Column -->
        <div class="lg:col-span-6 relative">
          <div class="aspect-[4/5] rounded-sm overflow-hidden shadow-2xl relative">
            <img
              src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80"
              alt="AURA Hotel Architectural Atmosphere"
              class="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
            />
          </div>
          <!-- Decorative accent box -->
          <div class="hidden sm:block absolute -bottom-6 -right-6 w-48 h-48 border-2 border-gold/40 rounded-sm -z-10"></div>
          <div class="absolute top-6 left-6 bg-forest/90 backdrop-blur-md text-white p-6 rounded-sm max-w-xs hidden sm:block shadow-xl">
            <p class="font-serif text-2xl text-sand font-normal mb-1">{{ $t('landing.hotel.badgeVal') }}</p>
            <p class="text-xs text-white/80 uppercase tracking-wider">{{ $t('landing.hotel.badgeText') }}</p>
          </div>
        </div>

        <!-- Right Story Column -->
        <div class="lg:col-span-6 space-y-6">
          <div class="space-y-2">
            <span class="text-xs uppercase tracking-[0.25em] text-gold font-semibold">{{ $t('landing.hotel.eyebrow') }}</span>
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl text-charcoal font-normal leading-tight">
              {{ $t('landing.hotel.title') }}<br />
              <span class="italic text-forest">{{ $t('landing.hotel.titleItalic') }}</span>
            </h2>
          </div>

          <div class="space-y-4 text-taupe text-base sm:text-lg leading-relaxed font-light">
            <p>{{ $t('landing.hotel.p1') }}</p>
            <p>{{ $t('landing.hotel.p2') }}</p>
          </div>

          <div class="pt-4 flex items-center space-x-6">
            <a
              href="#rooms"
              class="inline-flex items-center space-x-3 text-forest font-semibold text-sm uppercase tracking-widest hover:text-gold transition-colors group"
            >
              <span>{{ $t('landing.hotel.cta') }}</span>
              <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- 04. ROOMS SECTION -->
    <section id="rooms" class="py-24 bg-white border-y border-sand/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header with Horizontal Pagination -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-4">
          <div class="space-y-3 max-w-2xl">
            <span class="text-xs uppercase tracking-[0.25em] text-gold font-semibold">{{ $t('landing.rooms.eyebrow') }}</span>
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl text-charcoal font-normal">
              {{ $t('landing.rooms.title') }}
            </h2>
            <p class="text-taupe text-base sm:text-lg font-light">
              {{ $t('landing.rooms.subtitle') }}
            </p>
          </div>

          <!-- Horizontal Pagination Controls for Rooms -->
          <div v-if="totalRoomsPages > 1" class="flex items-center space-x-3 self-start md:self-end">
            <button
              @click="prevRoomsPage"
              :disabled="roomsPage === 1"
              class="px-4 py-2 text-xs font-semibold uppercase tracking-wider bg-ivory border border-sand/40 text-forest rounded shadow-sm hover:bg-forest hover:text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center space-x-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>{{ $t('landing.halls.prev') }}</span>
            </button>

            <span class="text-xs text-taupe font-medium px-2">
              {{ $t('landing.halls.page') }} {{ roomsPage }} {{ $t('landing.halls.of') }} {{ totalRoomsPages }}
            </span>

            <button
              @click="nextRoomsPage"
              :disabled="roomsPage === totalRoomsPages"
              class="px-4 py-2 text-xs font-semibold uppercase tracking-wider bg-ivory border border-sand/40 text-forest rounded shadow-sm hover:bg-forest hover:text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center space-x-1"
            >
              <span>{{ $t('landing.halls.next') }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Rooms Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="room in displayRooms"
            :key="room.id"
            class="group bg-ivory rounded-sm overflow-hidden border border-sand/30 hover:border-gold/50 transition-all duration-300 shadow-sm hover:shadow-xl flex flex-col"
          >
            <!-- Room Image -->
            <div class="relative aspect-[4/3] overflow-hidden">
              <img
                :src="room.image"
                :alt="room.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              />
              <div class="absolute top-4 right-4 bg-forest/90 text-white text-xs font-medium px-3 py-1 rounded-sm backdrop-blur-sm">
                {{ $t('landing.rooms.from') }} {{ room.price }} / {{ $t('landing.rooms.perNight') }}
              </div>
            </div>

            <!-- Room Content -->
            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
              <div class="space-y-2">
                <h3 class="font-display text-2xl text-charcoal group-hover:text-forest transition-colors">
                  {{ room.title }}
                </h3>
                <p class="text-xs uppercase tracking-wider text-gold font-medium">
                  {{ room.specs }}
                </p>
                <p class="text-sm text-taupe font-light line-clamp-2">
                  {{ room.description }}
                </p>
              </div>

              <!-- Amenities Icons -->
              <div class="pt-4 border-t border-sand/20 flex items-center justify-between text-xs text-taupe">
                <div class="flex items-center space-x-3">
                  <span class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                    <span>Wi-Fi</span>
                  </span>
                  <span class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" />
                    </svg>
                    <span>Breakfast</span>
                  </span>
                </div>

                <div class="flex items-center space-x-2">
                  <button
                    @click="openRoomModal(room)"
                    class="text-xs uppercase tracking-wider font-medium text-taupe hover:text-charcoal transition-colors"
                  >
                    Details
                  </button>
                  <button
                    @click="openBookingModal(room)"
                    class="px-3 py-1 bg-forest text-white text-xs font-semibold uppercase tracking-wider rounded hover:bg-forest-800 transition-colors"
                  >
                    Reserve
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 05. FACILITIES SECTION -->
    <section id="facilities" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
        <span class="text-xs uppercase tracking-[0.25em] text-gold font-semibold">{{ $t('landing.facilities.eyebrow') }}</span>
        <h2 class="font-display text-3xl sm:text-4xl md:text-5xl text-charcoal font-normal">
          {{ $t('landing.facilities.title') }}
        </h2>
      </div>

      <!-- Asymmetric Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div
          v-for="(facility, idx) in getFacilitiesList()"
          :key="facility.id"
          :class="[
            'group relative rounded-sm overflow-hidden shadow-md border border-sand/30 min-h-[300px] flex flex-col justify-end p-6',
            idx === 0 ? 'md:col-span-2 md:row-span-2 min-h-[440px]' : '',
            idx === 3 ? 'md:col-span-2' : ''
          ]"
        >
          <!-- Background Image -->
          <img
            :src="facility.image"
            :alt="facility.name"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 z-0"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent z-10"></div>

          <!-- Content -->
          <div class="relative z-20 text-white space-y-2">
            <div class="w-9 h-9 rounded-full bg-gold/30 backdrop-blur-md flex items-center justify-center text-gold border border-gold/40 mb-2">
              <component :is="facility.icon" class="w-5 h-5" />
            </div>
            <h3 class="font-display text-xl sm:text-2xl text-white font-normal">
              {{ facility.name }}
            </h3>
            <p class="text-xs sm:text-sm text-white/80 font-light">
              {{ facility.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- 05B. EVENT HALLS & MEETING ROOMS SECTION -->
    <section id="halls" class="py-24 bg-ivory border-t border-sand/30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-4">
          <div class="space-y-3 max-w-2xl">
            <span class="text-xs uppercase tracking-[0.25em] text-gold font-semibold">{{ $t('landing.halls.eyebrow') }}</span>
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl text-charcoal font-normal">
              {{ $t('landing.halls.title') }}
            </h2>
            <p class="text-taupe text-base sm:text-lg font-light">
              {{ $t('landing.halls.subtitle') }}
            </p>
          </div>

          <!-- Horizontal Pagination Controls -->
          <div class="flex items-center space-x-3 self-start md:self-end">
            <button
              @click="prevHallsPage"
              :disabled="hallsPage === 1"
              class="px-4 py-2 text-xs font-semibold uppercase tracking-wider bg-white border border-sand/40 text-forest rounded shadow-sm hover:bg-forest hover:text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center space-x-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>{{ $t('landing.halls.prev') }}</span>
            </button>

            <span class="text-xs text-taupe font-medium px-2">
              {{ $t('landing.halls.page') }} {{ hallsPage }} {{ $t('landing.halls.of') }} {{ totalHallsPages }}
            </span>

            <button
              @click="nextHallsPage"
              :disabled="hallsPage === totalHallsPages"
              class="px-4 py-2 text-xs font-semibold uppercase tracking-wider bg-white border border-sand/40 text-forest rounded shadow-sm hover:bg-forest hover:text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center space-x-1"
            >
              <span>{{ $t('landing.halls.next') }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        <!-- 3 Halls Grid per row/page -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div
            v-for="hall in displayHalls"
            :key="hall.id"
            class="bg-white rounded-sm overflow-hidden border border-sand/30 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group"
          >
            <!-- Hall Image Banner -->
            <div class="aspect-[16/10] overflow-hidden relative">
              <img
                :src="hall.image"
                :alt="hall.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              />
              <div class="absolute top-3 left-3 bg-forest/90 backdrop-blur-sm text-gold px-3 py-1 text-[11px] font-semibold tracking-wider uppercase rounded-sm border border-gold/30">
                {{ hall.hall_type || 'Event Hall' }}
              </div>
              <div class="absolute bottom-3 right-3 bg-black/75 backdrop-blur-sm text-white px-3 py-1 text-xs font-bold rounded-sm">
                {{ hall.formattedPrice || formatCurrency(hall.price_per_hour) }} {{ $t('landing.halls.perHour') }}
              </div>
            </div>

            <!-- Hall Info & Specs -->
            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
              <div class="space-y-2">
                <h3 class="font-display text-2xl text-charcoal group-hover:text-forest transition-colors">
                  {{ hall.name }}
                </h3>

                <div class="flex items-center space-x-4 text-xs text-taupe font-medium pt-1">
                  <span class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ $t('landing.halls.capacity') }}: {{ hall.capacity }} {{ $t('landing.halls.guests') }}</span>
                  </span>

                  <span v-if="hall.area_sqm" class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-2V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <span>{{ hall.area_sqm }} {{ $t('landing.halls.area') }}</span>
                  </span>
                </div>

                <p class="text-xs text-taupe font-light line-clamp-2 leading-relaxed pt-1">
                  {{ hall.description || 'Ruang pertemuan serbaguna lengkap dengan fasilitas AV modern dan audio visual.' }}
                </p>
              </div>

              <!-- Actions -->
              <div class="pt-4 border-t border-sand/30 flex items-center justify-between">
                <button
                  @click="openHallModal(hall)"
                  class="text-xs uppercase tracking-wider font-medium text-taupe hover:text-charcoal transition-colors"
                >
                  {{ $t('landing.halls.details') }}
                </button>

                <button
                  @click="openHallBookingModal(hall)"
                  class="px-4 py-2 bg-gold text-forest text-xs font-bold uppercase tracking-wider rounded hover:bg-forest hover:text-white transition-all shadow-md"
                >
                  {{ $t('landing.halls.reserve') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 06. DINING SECTION -->
    <section id="dining" class="py-24 bg-forest text-white relative overflow-hidden">
      <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-gold/10 blur-3xl pointer-events-none"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          <div class="lg:col-span-5 space-y-6">
            <span class="text-xs uppercase tracking-[0.25em] text-sand font-semibold">{{ $t('landing.dining.eyebrow') }}</span>
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-normal leading-tight">
              {{ $t('landing.dining.title') }}<br />
              <span class="italic text-sand">{{ $t('landing.dining.titleItalic') }}</span>
            </h2>
            <p class="text-white/80 text-base sm:text-lg font-light leading-relaxed">
              {{ $t('landing.dining.description') }}
            </p>

            <blockquote class="p-4 border-l-2 border-gold bg-white/5 rounded-r-sm text-sm text-sand/90 font-serif italic">
              "{{ $t('landing.dining.quote') }}"
              <footer class="mt-2 text-xs font-sans not-italic text-white/70">— {{ $t('landing.dining.chef') }}</footer>
            </blockquote>

            <div class="pt-2">
              <button
                @click="openBookingModal()"
                class="inline-flex items-center space-x-3 px-6 py-3 bg-sand text-forest text-xs font-bold uppercase tracking-widest rounded hover:bg-gold transition-colors shadow-md"
              >
                <span>{{ $t('landing.dining.cta') }}</span>
              </button>
            </div>
          </div>

          <div class="lg:col-span-7 grid grid-cols-2 gap-4">
            <div class="aspect-[3/4] rounded-sm overflow-hidden shadow-xl">
              <img
                src="https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=800&q=80"
                alt="AURA Fine Dining Atmosphere"
                class="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
              />
            </div>
            <div class="aspect-[3/4] rounded-sm overflow-hidden shadow-xl mt-8">
              <img
                src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80"
                alt="Culinary Creation"
                class="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 07. EXPERIENCES SECTION -->
    <section id="experiences" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
        <span class="text-xs uppercase tracking-[0.25em] text-gold font-semibold">{{ $t('landing.experiences.eyebrow') }}</span>
        <h2 class="font-display text-3xl sm:text-4xl md:text-5xl text-charcoal font-normal">
          {{ $t('landing.experiences.title') }}
        </h2>
        <p class="text-taupe text-base sm:text-lg font-light">
          {{ $t('landing.experiences.subtitle') }}
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="exp in getExperiencesList()"
          :key="exp.id"
          class="bg-white border border-sand/30 rounded-sm overflow-hidden group shadow-sm hover:shadow-lg transition-all"
        >
          <div class="aspect-[4/3] overflow-hidden">
            <img :src="exp.image" :alt="exp.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
          </div>
          <div class="p-6 space-y-2">
            <span class="text-[10px] uppercase tracking-widest text-gold font-semibold">{{ exp.tag }}</span>
            <h3 class="font-display text-xl text-charcoal group-hover:text-forest transition-colors">{{ exp.title }}</h3>
            <p class="text-xs text-taupe font-light">{{ exp.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 08. WHY TECHNOLOGY MATTERS -->
    <section class="py-20 bg-ivory border-t border-sand/30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 space-y-2">
          <span class="text-xs uppercase tracking-[0.25em] text-gold font-semibold">{{ $t('landing.whyTech.eyebrow') }}</span>
          <h3 class="font-display text-3xl text-charcoal font-normal">{{ $t('landing.whyTech.title') }}</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-white p-8 rounded-sm border border-sand/30 space-y-3 shadow-sm hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-full bg-sand/30 flex items-center justify-center text-forest font-serif font-bold text-lg">1</div>
            <h4 class="font-display text-xl text-forest">{{ $t('landing.whyTech.b1.title') }}</h4>
            <p class="text-sm text-taupe font-light">
              {{ $t('landing.whyTech.b1.desc') }}
            </p>
          </div>

          <div class="bg-white p-8 rounded-sm border border-sand/30 space-y-3 shadow-sm hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-full bg-sand/30 flex items-center justify-center text-forest font-serif font-bold text-lg">2</div>
            <h4 class="font-display text-xl text-forest">{{ $t('landing.whyTech.b2.title') }}</h4>
            <p class="text-sm text-taupe font-light">
              {{ $t('landing.whyTech.b2.desc') }}
            </p>
          </div>

          <div class="bg-white p-8 rounded-sm border border-sand/30 space-y-3 shadow-sm hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-full bg-sand/30 flex items-center justify-center text-forest font-serif font-bold text-lg">3</div>
            <h4 class="font-display text-xl text-forest">{{ $t('landing.whyTech.b3.title') }}</h4>
            <p class="text-sm text-taupe font-light">
              {{ $t('landing.whyTech.b3.desc') }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- 09. TESTIMONIALS -->
    <section class="py-24 bg-white border-y border-sand/30">
      <div class="max-w-4xl mx-auto px-4 text-center space-y-8">
        <span class="text-xs uppercase tracking-[0.25em] text-gold font-semibold">{{ $t('landing.testimonials.eyebrow') }}</span>
        
        <div class="relative">
          <svg class="w-12 h-12 text-gold/30 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
          </svg>

          <p class="font-serif text-2xl sm:text-3xl text-charcoal font-normal italic leading-relaxed">
            "{{ $t('landing.testimonials.quote') }}"
          </p>

          <div class="mt-6 space-y-1">
            <p class="font-semibold text-sm text-forest uppercase tracking-widest">{{ $t('landing.testimonials.name') }}</p>
            <p class="text-xs text-taupe">{{ $t('landing.testimonials.role') }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 10. LOCATION SECTION -->
    <section id="location" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        <div class="lg:col-span-5 space-y-6">
          <span class="text-xs uppercase tracking-[0.25em] text-gold font-semibold">{{ $t('landing.location.eyebrow') }}</span>
          <h2 class="font-display text-3xl sm:text-4xl text-charcoal font-normal">{{ $t('landing.location.title') }}</h2>
          <p class="text-taupe text-base font-light">
            {{ $t('landing.location.desc') }}
          </p>

          <div class="space-y-4 text-sm text-charcoal pt-2">
            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-gold flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <div>
                <strong class="block text-forest">Address</strong>
                <span class="text-taupe font-light">{{ $t('landing.location.address') }}</span>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-gold flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
              <div>
                <strong class="block text-forest">Airport Proximity</strong>
                <span class="text-taupe font-light">{{ $t('landing.location.airport') }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-7 aspect-[16/9] bg-sand/20 rounded-sm overflow-hidden border border-sand/40 shadow-xl relative">
          <img
            src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1200&q=80"
            alt="Hotel Destination Map Preview"
            class="w-full h-full object-cover"
          />
          <div class="absolute inset-0 bg-forest/20 flex items-center justify-center">
            <div class="bg-white/90 backdrop-blur-md px-6 py-4 rounded-sm text-center shadow-lg border border-gold/40">
              <span class="font-serif text-lg text-forest font-semibold block">AURA Hotel & Resorts</span>
              <span class="text-xs text-taupe uppercase tracking-wider">Heritage Bay Destination</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 11. BOOKING CTA BANNER -->
    <section id="booking-cta" class="relative py-24 bg-forest text-white overflow-hidden">
      <div class="absolute inset-0 z-0 opacity-25">
        <img
          src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=2000&q=80"
          alt="Luxury Hotel Sunset View"
          class="w-full h-full object-cover"
        />
      </div>

      <div class="relative z-10 max-w-4xl mx-auto px-4 text-center space-y-6">
        <span class="text-xs uppercase tracking-[0.3em] text-sand font-semibold">{{ $t('landing.cta.eyebrow') }}</span>
        <h2 class="font-display text-4xl sm:text-5xl md:text-6xl font-normal leading-tight">
          {{ $t('landing.cta.title') }}
        </h2>
        <p class="text-white/80 text-base sm:text-lg max-w-xl mx-auto font-light">
          {{ $t('landing.cta.desc') }}
        </p>

        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
          <button
            @click="openBookingModal()"
            class="w-full sm:w-auto px-8 py-4 bg-sand text-forest text-sm font-bold uppercase tracking-widest rounded hover:bg-gold transition-all shadow-lg"
          >
            {{ $t('landing.cta.book') }}
          </button>
          <button
            @click="openTrackModal()"
            class="w-full sm:w-auto px-8 py-4 border border-white/40 text-white text-sm font-medium uppercase tracking-widest rounded hover:bg-white/10 transition-all"
          >
            Cek Status Pesanan
          </button>
        </div>
      </div>
    </section>

    <!-- 12. FOOTER -->
    <footer class="bg-forest text-white/80 pt-16 pb-12 border-t border-gold/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <!-- Column 1: Logo, Bio & Social Media Icons -->
          <div class="space-y-4">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 rounded-full border border-gold/40 flex items-center justify-center bg-forest/40">
                <span class="font-serif text-lg font-bold text-gold">A</span>
              </div>
              <span class="font-serif text-xl font-semibold text-white tracking-widest uppercase">AURA</span>
            </div>
            <p class="text-xs text-white/70 font-light leading-relaxed">
              Refined luxury hotel experiences supported by seamless modern management systems.
            </p>

            <!-- Social Media Icons -->
            <div class="pt-2">
              <span class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-sand mb-2.5">Follow Us</span>
              <div class="flex items-center space-x-2">
                <!-- Instagram -->
                <a
                  v-if="socialSettings.instagram"
                  :href="socialSettings.instagram"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-8 h-8 rounded-full bg-white/10 hover:bg-gold hover:text-forest flex items-center justify-center text-white/90 transition-all shadow-sm hover:scale-110"
                  title="Instagram"
                >
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                  </svg>
                </a>

                <!-- Twitter / X -->
                <a
                  v-if="socialSettings.twitter"
                  :href="socialSettings.twitter"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-8 h-8 rounded-full bg-white/10 hover:bg-gold hover:text-forest flex items-center justify-center text-white/90 transition-all shadow-sm hover:scale-110"
                  title="Twitter / X"
                >
                  <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                  </svg>
                </a>

                <!-- YouTube -->
                <a
                  v-if="socialSettings.youtube"
                  :href="socialSettings.youtube"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-8 h-8 rounded-full bg-white/10 hover:bg-gold hover:text-forest flex items-center justify-center text-white/90 transition-all shadow-sm hover:scale-110"
                  title="YouTube"
                >
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                  </svg>
                </a>

                <!-- Facebook -->
                <a
                  v-if="socialSettings.facebook"
                  :href="socialSettings.facebook"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-8 h-8 rounded-full bg-white/10 hover:bg-gold hover:text-forest flex items-center justify-center text-white/90 transition-all shadow-sm hover:scale-110"
                  title="Facebook"
                >
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
                </a>

                <!-- LinkedIn -->
                <a
                  v-if="socialSettings.linkedin"
                  :href="socialSettings.linkedin"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-8 h-8 rounded-full bg-white/10 hover:bg-gold hover:text-forest flex items-center justify-center text-white/90 transition-all shadow-sm hover:scale-110"
                  title="LinkedIn"
                >
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                  </svg>
                </a>

                <!-- Threads -->
                <a
                  v-if="socialSettings.threads"
                  :href="socialSettings.threads"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-8 h-8 rounded-full bg-white/10 hover:bg-gold hover:text-forest flex items-center justify-center text-white/90 transition-all shadow-sm hover:scale-110"
                  title="Threads"
                >
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.632 12.19c-.066 3.09-2.316 5.485-5.592 5.485-3.414 0-5.74-2.457-5.74-5.897 0-3.42 2.378-5.918 5.796-5.918 3.197 0 5.253 2.12 5.347 4.966h-1.996c-.078-1.736-1.282-3.053-3.351-3.053-2.158 0-3.717 1.637-3.717 3.978 0 2.38 1.517 3.978 3.66 3.978 1.874 0 3.098-1.127 3.264-2.539H12v-1.892h5.632v.892z"/>
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <!-- Column 2: Hotel Links -->
          <div class="space-y-3">
            <h4 class="text-xs font-semibold uppercase tracking-widest text-sand">Hotel</h4>
            <ul class="space-y-2 text-xs font-light">
              <li><a href="#rooms" class="hover:text-gold transition-colors">{{ $t('landing.nav.rooms') }}</a></li>
              <li><a href="#dining" class="hover:text-gold transition-colors">{{ $t('landing.nav.dining') }}</a></li>
              <li><a href="#facilities" class="hover:text-gold transition-colors">{{ $t('landing.nav.facilities') }}</a></li>
              <li><a href="#experiences" class="hover:text-gold transition-colors">{{ $t('landing.nav.experiences') }}</a></li>
            </ul>
          </div>

          <!-- Column 3: Information Links -->
          <div class="space-y-3">
            <h4 class="text-xs font-semibold uppercase tracking-widest text-sand">Information</h4>
            <ul class="space-y-2 text-xs font-light">
              <li><a href="#hotel" class="hover:text-gold transition-colors">{{ $t('landing.nav.hotel') }}</a></li>
              <li><a href="#location" class="hover:text-gold transition-colors">{{ $t('landing.location.title') }}</a></li>
              <li><button @click="openTrackModal()" class="hover:text-gold transition-colors">Cek Status Pesanan</button></li>
            </ul>
          </div>

          <!-- Column 4: Contact -->
          <div class="space-y-3">
            <h4 class="text-xs font-semibold uppercase tracking-widest text-sand">Contact</h4>
            <div class="space-y-1.5 text-xs font-light text-white/70">
              <p>Grand Ocean Drive No. 88</p>
              <p>Phone: +62 21 555 8899</p>
              <p>Email: stay@aurahotels.com</p>
            </div>
          </div>
        </div>

        <!-- Bottom Bar -->
        <div class="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-white/60 gap-4">
          <p>{{ $t('landing.footer.rights') }}</p>
          <div class="flex items-center space-x-6">
            <a href="#" class="hover:text-sand">Privacy Policy</a>
            <a href="#" class="hover:text-sand">Terms & Conditions</a>
            <a href="#" class="hover:text-sand">Cookie Settings</a>
          </div>
        </div>

      </div>
    </footer>

    <!-- FLOATING WHATSAPP BUTTON -->
    <a
      :href="getWhatsAppDirectUrl()"
      target="_blank"
      rel="noopener noreferrer"
      class="fixed bottom-16 right-4 lg:bottom-6 lg:right-6 z-40 group flex items-center space-x-2 bg-emerald-600 hover:bg-emerald-700 text-white p-3 lg:px-4 lg:py-3 rounded-full shadow-2xl hover:shadow-emerald-900/40 transition-all duration-300 transform hover:scale-105 active:scale-95 border border-emerald-400/40"
      :title="$t('landing.whatsapp.tooltip')"
      :aria-label="$t('landing.whatsapp.chat')"
    >
      <!-- Pulse Effect Ring -->
      <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-400"></span>
      </span>

      <!-- Official WhatsApp Icon -->
      <svg class="w-6 h-6 lg:w-6 lg:h-6 fill-current text-white flex-shrink-0" viewBox="0 0 24 24">
        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
      </svg>

      <!-- Label (Visible on Large Screens or on Hover) -->
      <span class="hidden sm:inline-block text-xs font-bold uppercase tracking-wider pr-1">
        {{ $t('landing.whatsapp.chat') }}
      </span>
    </a>

    <!-- FLOATING MOBILE BOTTOM ACTION BAR -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-forest/95 backdrop-blur-md text-white px-4 py-2.5 border-t border-gold/30 flex items-center justify-between gap-3 shadow-2xl">
      <button
        @click="openTrackModal()"
        class="flex-1 py-2.5 px-3 bg-white/10 hover:bg-white/20 border border-gold/40 text-gold text-xs font-semibold uppercase tracking-wider rounded text-center transition-all flex items-center justify-center space-x-1.5 active:scale-95"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <span>Cek Status</span>
      </button>

      <button
        @click="openBookingModal()"
        class="flex-1 py-2.5 px-3 bg-gold text-forest text-xs font-bold uppercase tracking-wider rounded text-center transition-all shadow-md flex items-center justify-center space-x-1.5 active:scale-95"
      >
        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
          <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
        </svg>
        <span>{{ $t('landing.nav.bookNow') }}</span>
      </button>
    </div>

    <!-- ROOM DETAIL PREVIEW MODAL -->
    <div
      v-if="selectedRoom"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-fade-in"
      @click.self="selectedRoom = null"
    >
      <div class="bg-ivory text-charcoal rounded-sm max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-sand/40 relative">
        <button
          @click="selectedRoom = null"
          class="absolute top-4 right-4 z-10 p-2 text-charcoal/70 hover:text-charcoal bg-white/80 rounded-full"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="aspect-[16/9] overflow-hidden relative">
          <img :src="selectedRoom.image" :alt="selectedRoom.title" class="w-full h-full object-cover" />
          <div class="absolute bottom-4 left-4 bg-forest text-white px-3 py-1 text-xs font-semibold rounded-sm">
            {{ selectedRoom.price }} / {{ $t('landing.rooms.perNight') }}
          </div>
        </div>

        <div class="p-6 space-y-4">
          <h3 class="font-display text-3xl text-forest">{{ selectedRoom.title }}</h3>
          <p class="text-xs uppercase tracking-widest text-gold font-bold">{{ selectedRoom.specs }}</p>
          <p class="text-sm text-taupe font-light leading-relaxed">{{ selectedRoom.description }}</p>

          <div class="pt-2 border-t border-sand/30">
            <h4 class="text-xs uppercase tracking-wider font-semibold text-charcoal mb-2">{{ $t('landing.rooms.modalTitle') }}</h4>
            <div class="grid grid-cols-2 gap-2 text-xs text-taupe">
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>King Plush Bedding</span>
              </span>
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>Marble Rain Shower</span>
              </span>
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>Smart Room Automation</span>
              </span>
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>24/7 Butler Service</span>
              </span>
            </div>
          </div>

          <div class="pt-4 flex items-center justify-end space-x-4">
            <button
              @click="selectedRoom = null"
              class="px-4 py-2 border border-forest text-forest text-xs font-semibold uppercase tracking-wider rounded"
            >
              {{ $t('landing.rooms.close') }}
            </button>
            <button
              @click="openBookingModal(selectedRoom)"
              class="px-6 py-2 bg-forest text-white text-xs font-semibold uppercase tracking-wider rounded hover:bg-forest-800"
            >
              {{ $t('landing.rooms.reserve') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- HALL DETAIL MODAL -->
    <div
      v-if="selectedHall"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-fade-in"
      @click.self="selectedHall = null"
    >
      <div class="bg-ivory text-charcoal rounded-sm max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-sand/40 relative">
        <button
          @click="selectedHall = null"
          class="absolute top-4 right-4 z-10 p-2 text-charcoal/70 hover:text-charcoal bg-white/80 rounded-full"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="aspect-[16/9] overflow-hidden relative">
          <img :src="selectedHall.image" :alt="selectedHall.name" class="w-full h-full object-cover" />
          <div class="absolute bottom-4 left-4 bg-forest text-white px-3 py-1 text-xs font-semibold rounded-sm">
            {{ selectedHall.formattedPrice || formatCurrency(selectedHall.price_per_hour) }} / {{ $t('landing.halls.perHour') }}
          </div>
          <div class="absolute top-4 left-4 bg-forest/90 text-gold px-3 py-1 text-xs font-semibold uppercase tracking-wider rounded-sm">
            {{ selectedHall.hall_type || 'Event Hall' }}
          </div>
        </div>

        <div class="p-6 space-y-4 text-left">
          <div class="flex items-center justify-between border-b border-sand/30 pb-3">
            <div>
              <h3 class="font-display text-3xl text-forest">{{ selectedHall.name }}</h3>
              <p class="text-xs uppercase tracking-widest text-gold font-bold mt-1">
                Lantai: {{ selectedHall.floor || 'Ground Floor' }} • Kapasitas: {{ selectedHall.capacity }} Tamu
              </p>
            </div>
            <span class="px-3 py-1 bg-sand/30 text-forest text-xs font-semibold uppercase tracking-wider rounded">
              {{ selectedHall.status || 'Available' }}
            </span>
          </div>

          <p class="text-sm text-taupe font-light leading-relaxed">
            {{ selectedHall.description || 'Ruang pertemuan serbaguna mewah yang dilengkapi dengan peralatan audio visual mutakhir, pencahayaan yang disesuaikan, serta layanan banquet eksklusif.' }}
          </p>

          <div class="pt-2 border-t border-sand/30">
            <h4 class="text-xs uppercase tracking-wider font-semibold text-charcoal mb-2">Fasilitas Hall & Peralatan</h4>
            <div class="grid grid-cols-2 gap-2 text-xs text-taupe">
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>Proyektor HD & Layar Lebar</span>
              </span>
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>Sound System & Microphone Wireless</span>
              </span>
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>Wi-Fi Berkecepatan Tinggi</span>
              </span>
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>Layout Meja & Kursi Fleksibel</span>
              </span>
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>AC Sentral & Control Lighting</span>
              </span>
              <span class="flex items-center space-x-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span>Layanan Catering & Banquet</span>
              </span>
            </div>
          </div>

          <div class="pt-4 flex items-center justify-end space-x-4">
            <button
              @click="selectedHall = null"
              class="px-4 py-2 border border-forest text-forest text-xs font-semibold uppercase tracking-wider rounded hover:bg-sand/20"
            >
              Tutup
            </button>
            <button
              @click="openHallBookingModal(selectedHall)"
              class="px-6 py-2 bg-gold text-forest font-bold text-xs uppercase tracking-wider rounded hover:bg-forest hover:text-white transition-all shadow-md"
            >
              {{ $t('landing.halls.reserve') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- LIVE GUEST RESERVATION MODAL (HYBRID PAYMENT MODEL) -->
    <div
      v-if="bookingModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm animate-fade-in"
      @click.self="closeBookingModal()"
    >
      <div class="bg-ivory text-charcoal rounded-md max-w-xl w-full max-h-[92vh] overflow-y-auto shadow-2xl border border-sand/40 relative p-6 sm:p-8">
        <!-- Close button -->
        <button
          @click="closeBookingModal()"
          class="absolute top-5 right-5 p-1.5 text-taupe hover:text-charcoal bg-white rounded-full border border-sand/30"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- SUCCESS SCREEN -->
        <div v-if="bookingSuccessData" class="py-6 text-center space-y-5 animate-fade-in">
          <div class="w-16 h-16 bg-forest text-gold rounded-full flex items-center justify-center mx-auto shadow-lg border border-gold/40">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <div class="space-y-2">
              <span class="text-xs uppercase tracking-[0.25em] text-gold font-bold">{{ $t('landing.bookingModal.submitted') }}</span>
              <h3 class="font-display text-3xl text-forest">{{ $t('landing.bookingModal.thankYou', { name: bookingSuccessData.data?.guest?.name || bookingForm.name }) }}</h3>
              <p class="text-sm text-taupe font-light">
                {{ $t('landing.bookingModal.successDesc') }}
              </p>
            </div>

            <div class="bg-white p-5 rounded-sm border border-sand/40 space-y-3 text-left shadow-sm">
              <div class="flex items-center justify-between pb-2 border-b border-sand/20">
                <span class="text-xs text-taupe uppercase tracking-wider">{{ $t('landing.bookingModal.bookingCode') }}</span>
                <span class="font-mono font-bold text-forest text-base">{{ bookingSuccessData.booking_number }}</span>
              </div>

              <div class="flex items-center justify-between text-xs">
                <span class="text-taupe">{{ $t('landing.bookingModal.paymentOption') }}</span>
                <span class="font-semibold text-forest">
                  {{ bookingSuccessData.payment_option === 'transfer_guaranteed' ? $t('landing.bookingModal.guaranteedDP') : $t('landing.bookingModal.payAtHotel') }}
                </span>
              </div>

              <div class="flex items-center justify-between text-xs">
                <span class="text-taupe">{{ $t('landing.bookingModal.stayDates') }}</span>
                <span class="font-medium text-charcoal">{{ bookingSuccessData.data?.check_in_date }} → {{ bookingSuccessData.data?.check_out_date }}</span>
              </div>

              <div v-if="bookingSuccessData.payment_option === 'transfer_guaranteed'" class="flex items-center justify-between text-xs">
                <span class="text-taupe">{{ $t('landing.bookingModal.paidDeposit') }}</span>
                <span class="font-bold text-gold text-sm">{{ formatCurrency(bookingSuccessData.deposit_amount) }}</span>
              </div>

              <div class="flex items-center justify-between text-xs">
                <span class="text-taupe">{{ $t('landing.bookingModal.totalAmount') }}</span>
                <span class="font-bold text-charcoal text-sm">{{ formatCurrency(bookingSuccessData.data?.total_amount) }}</span>
              </div>

              <!-- Rooms Breakdown in Booking Success Screen -->
              <div v-if="bookingSuccessData.data?.rooms && bookingSuccessData.data.rooms.length > 0" class="pt-2 border-t border-sand/20 space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                  <span class="text-taupe uppercase tracking-wider text-[11px] font-semibold">{{ $t('landing.bookingModal.bookedRoomsList') }}</span>
                  <span v-if="bookingSuccessData.data.rooms.length > 1" class="px-2 py-0.5 bg-forest text-gold text-[10px] font-bold rounded-full">
                    {{ $t('landing.bookingModal.multiRoomBadge', { count: bookingSuccessData.data.rooms.length }) }}
                  </span>
                </div>
                <div class="space-y-1">
                  <div v-for="rm in bookingSuccessData.data.rooms" :key="rm.id" class="flex flex-col sm:flex-row sm:items-center justify-between text-xs bg-sand/10 px-2.5 py-1.5 rounded border border-sand/20 gap-1">
                    <span class="font-semibold text-charcoal">Kamar {{ rm.room_number }} <span class="font-normal text-taupe text-[11px]">({{ rm.roomType?.name || rm.room_type?.name }})</span></span>
                    <span class="font-mono text-forest font-bold">{{ formatCurrency(rm.pivot?.subtotal || (rm.pivot?.room_rate * (bookingSuccessData.data?.nights || 1)) || 0) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-3 pt-2">
              <div class="p-3.5 bg-forest/5 border border-forest/10 rounded text-left text-xs space-y-1.5">
                <p class="font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.bankDestination') }}</p>
                <div class="space-y-1">
                  <p v-for="(bank, bIdx) in activeBankAccounts" :key="bIdx" class="text-taupe">
                    <strong>{{ bank.bank_name }}:</strong> <span class="font-mono font-semibold text-charcoal">{{ bank.account_number }}</span> a/n {{ bank.account_holder }}
                  </p>
                </div>
              </div>

              <!-- QRIS Box in Success Screen -->
              <div v-if="hasQrisUrl" class="p-3.5 bg-white border border-sand/40 rounded text-left space-y-2 shadow-xs">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.payViaQris') }}</span>
                  <span class="text-[10px] bg-gold text-forest px-2 py-0.5 rounded font-bold uppercase">{{ $t('landing.bookingModal.allEwallets') }}</span>
                </div>
                <div class="flex items-center gap-3">
                  <img
                    :src="formattedQrisUrl"
                    alt="QRIS Code"
                    @click="qrisPreviewModalUrl = formattedQrisUrl"
                    class="w-24 h-24 object-contain bg-white p-1.5 rounded border border-sand/30 shadow-xs cursor-pointer hover:scale-105 transition-transform"
                    :title="$t('landing.bookingModal.enlargeQris')"
                  />
                  <div class="text-xs text-taupe space-y-1">
                    <p class="text-[11px] leading-snug">{{ paymentSettings.qris_notes || 'Pindai QRIS untuk pembayaran langsung.' }}</p>
                    <button
                      type="button"
                      @click="qrisPreviewModalUrl = formattedQrisUrl"
                      class="text-[11px] text-forest font-bold underline hover:text-gold transition-colors inline-flex items-center space-x-1"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                      </svg>
                      <span>{{ $t('landing.bookingModal.enlargeQris') }}</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- OPSI 1: WA CONFIRMATION BUTTON -->
              <a
                :href="getWhatsAppConfirmUrl(
                  bookingSuccessData.booking_number,
                  bookingSuccessData.data?.guest?.name || bookingForm.name,
                  bookingForm.bank_name,
                  bookingSuccessData.deposit_amount,
                  bookingForm.reference_number
                )"
                target="_blank"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider rounded transition-colors shadow flex items-center justify-center space-x-2"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                </svg>
                <span>{{ $t('landing.bookingModal.waConfirm') }}</span>
              </a>

              <!-- OPSI 2: UPLOAD STRUK BOX -->
              <div class="p-3 bg-white border border-sand/40 rounded space-y-2 text-left shadow-sm">
                <label class="block text-[11px] font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.uploadStruk') }}</label>
                <div class="flex items-center gap-2">
                  <input
                    type="file"
                    accept="image/*,application/pdf"
                    @change="handleReceiptFileChange"
                    class="text-[11px] text-taupe flex-1 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-sand/30 file:text-forest hover:file:bg-sand/50"
                  />
                  <button
                    type="button"
                    @click="uploadReceiptFile(bookingSuccessData.booking_number, bookingSuccessData.data?.guest?.email || bookingForm.email)"
                    :disabled="uploadingReceipt || !receiptFile"
                    class="px-3 py-1.5 bg-forest text-white text-[11px] font-semibold rounded hover:bg-forest-800 disabled:opacity-50 transition-colors whitespace-nowrap"
                  >
                    {{ uploadingReceipt ? $t('landing.bookingModal.uploading') : $t('landing.bookingModal.uploadBtn') }}
                  </button>
                </div>
                <div v-if="receiptUploadSuccess" class="text-[11px] text-emerald-700 font-medium">
                  ✓ {{ receiptUploadSuccess }}
                </div>
                <div v-if="receiptUploadError" class="text-[11px] text-red-600 font-medium">
                  ⚠ {{ receiptUploadError }}
                </div>
              </div>
            </div>

            <p class="text-xs text-taupe italic">
              {{ $t('landing.bookingModal.autoNotice') }}
            </p>

            <button
              @click="closeBookingModal()"
              class="w-full py-3.5 bg-forest text-white text-xs font-semibold uppercase tracking-widest rounded hover:bg-forest-800 transition-colors shadow"
            >
              {{ $t('landing.bookingModal.doneBtn') }}
            </button>
          </div>

          <!-- FORM SCREEN -->
          <div v-else class="space-y-5">
            <div class="space-y-1 pr-6">
              <span class="text-xs uppercase tracking-[0.25em] text-gold font-bold">{{ $t('landing.bookingModal.title') }}</span>
              <h3 class="font-display text-2xl text-forest">{{ $t('landing.bookingModal.subTitle') }}</h3>
              <p class="text-xs text-taupe font-light">{{ $t('landing.bookingModal.desc') }}</p>
            </div>

            <!-- Multi-Room Notice Banner -->
            <div class="p-3 bg-forest/5 border border-forest/20 rounded text-xs text-forest flex items-start space-x-2.5 shadow-xs">
              <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="space-y-0.5 leading-snug">
                <span class="font-bold block text-forest">{{ $t('landing.bookingModal.multiRoomNoticeTitle') }}</span>
                <p class="text-taupe text-[11px]">
                  {{ $t('landing.bookingModal.multiRoomNoticeDesc') }}
                </p>
              </div>
            </div>

            <!-- Error Alert -->
            <div v-if="bookingErrorMessage" class="p-3 bg-red-50 border border-red-200 rounded text-xs text-red-800">
              {{ bookingErrorMessage }}
            </div>

            <form @submit.prevent="submitBooking" class="space-y-4">
              <!-- Name & Email -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.fullName') }}</label>
                  <input
                    v-model="bookingForm.name"
                    type="text"
                    required
                    placeholder="Budi Santoso"
                    class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                  />
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.email') }}</label>
                  <input
                    v-model="bookingForm.email"
                    type="email"
                    required
                    placeholder="guest@example.com"
                    class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                  />
                </div>
              </div>

              <!-- Phone Number & Room Type -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.phone') }}</label>
                  <input
                    v-model="bookingForm.phone"
                    type="tel"
                    required
                    @input="bookingForm.phone = bookingForm.phone.replace(/[^0-9]/g, '')"
                    placeholder="081234567890"
                    class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                  />
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.roomCategory') }}</label>
                  <select
                    v-model="bookingForm.room_type_id"
                    class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                  >
                    <option :value="null">All Available Room Types</option>
                    <option v-for="rt in roomTypesList" :key="rt.id" :value="rt.id">
                      {{ rt.name }} ({{ formatCurrency(rt.base_price) }}/night)
                    </option>
                  </select>
                </div>
              </div>

              <!-- Check-in & Check-out Dates -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.checkIn') }}</label>
                  <input
                    v-model="bookingForm.check_in_date"
                    type="date"
                    required
                    :min="todayDate"
                    class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                  />
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.checkOut') }}</label>
                  <input
                    v-model="bookingForm.check_out_date"
                    type="date"
                    required
                    :min="bookingForm.check_in_date || todayDate"
                    class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                  />
                </div>
              </div>

              <!-- Adults & Children -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.adults') }}</label>
                  <select
                    v-model.number="bookingForm.adults"
                    class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                  >
                    <option :value="1">1</option>
                    <option :value="2">2</option>
                    <option :value="3">3</option>
                    <option :value="4">4</option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.children') }}</label>
                  <select
                    v-model.number="bookingForm.children"
                    class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                  >
                    <option :value="0">0</option>
                    <option :value="1">1</option>
                    <option :value="2">2</option>
                  </select>
                </div>
              </div>

              <!-- HYBRID PAYMENT OPTIONS -->
              <div class="space-y-2 pt-2 border-t border-sand/30">
                <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal">{{ $t('landing.bookingModal.paymentChoice') }}</label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <!-- Option A: Pay at Hotel -->
                  <label
                    :class="[
                      'p-3.5 rounded border cursor-pointer transition-all flex flex-col justify-between space-y-2',
                      bookingForm.payment_option === 'pay_at_hotel'
                        ? 'border-forest bg-forest/5 ring-1 ring-forest'
                        : 'border-sand/40 bg-white hover:border-sand'
                    ]"
                  >
                    <div class="flex items-start space-x-2">
                      <input
                        type="radio"
                        v-model="bookingForm.payment_option"
                        value="pay_at_hotel"
                        class="mt-0.5 text-forest focus:ring-forest"
                      />
                      <div>
                        <span class="block text-xs font-bold text-forest">{{ $t('landing.bookingModal.payAtHotel') }}</span>
                        <span class="block text-[11px] text-taupe leading-snug">{{ $t('landing.bookingModal.payAtHotelOption') }}</span>
                      </div>
                    </div>
                  </label>

                  <!-- Option B: Bank Transfer Guaranteed -->
                  <label
                    :class="[
                      'p-3.5 rounded border cursor-pointer transition-all flex flex-col justify-between space-y-2',
                      bookingForm.payment_option === 'transfer_guaranteed'
                        ? 'border-forest bg-forest/5 ring-1 ring-forest'
                        : 'border-sand/40 bg-white hover:border-sand'
                    ]"
                  >
                    <div class="flex items-start space-x-2">
                      <input
                        type="radio"
                        v-model="bookingForm.payment_option"
                        value="transfer_guaranteed"
                        class="mt-0.5 text-forest focus:ring-forest"
                      />
                      <div>
                        <span class="block text-xs font-bold text-forest">{{ $t('landing.bookingModal.guaranteedDP') }}</span>
                        <span class="block text-[11px] text-taupe leading-snug">{{ $t('landing.bookingModal.transferGuaranteedOption') }}</span>
                      </div>
                    </div>
                  </label>
                </div>

                <!-- Additional details if Bank Transfer is selected -->
                <div v-if="bookingForm.payment_option === 'transfer_guaranteed'" class="p-3.5 bg-white border border-sand/40 rounded space-y-3 mt-2 animate-fade-in">
                  <div class="text-xs space-y-2">
                    <span class="font-bold text-forest uppercase tracking-wider block">{{ $t('landing.bookingModal.bankDestination') }}</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] text-taupe">
                      <div v-for="(bank, bIdx) in activeBankAccounts" :key="bIdx" class="p-2 bg-ivory rounded border border-sand/20">
                        <strong class="text-charcoal block">{{ bank.bank_name }}</strong>
                        <span class="font-mono font-semibold">{{ bank.account_number }}</span>
                        <span class="block text-[10px] text-taupe">a/n {{ bank.account_holder }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- QRIS Display Section -->
                  <div v-if="hasQrisUrl" class="p-3 bg-ivory/80 border border-sand/30 rounded space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="text-[11px] font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.qrisTitle') }}</span>
                      <span class="text-[10px] text-gold bg-forest px-2 py-0.5 rounded font-bold">{{ $t('landing.bookingModal.scanQris') }}</span>
                    </div>
                    <div class="flex items-center gap-3 pt-1">
                      <img
                        :src="formattedQrisUrl"
                        alt="Kode QRIS Pembayaran"
                        @click="qrisPreviewModalUrl = formattedQrisUrl"
                        class="w-24 h-24 object-contain bg-white p-1 rounded border border-sand/40 shadow-xs cursor-pointer hover:scale-105 transition-transform"
                        :title="$t('landing.bookingModal.enlargeQris')"
                      />
                      <div class="text-left text-[11px] text-taupe leading-relaxed space-y-1">
                        <p class="font-medium text-charcoal">{{ paymentSettings.qris_notes || 'Pindai QRIS menggunakan m-Banking atau e-Wallet.' }}</p>
                        <button
                          type="button"
                          @click="qrisPreviewModalUrl = formattedQrisUrl"
                          class="text-[10px] text-forest font-bold underline hover:text-gold transition-colors inline-flex items-center space-x-1"
                        >
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                          </svg>
                          <span>{{ $t('landing.bookingModal.enlargeQris') }}</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <div>
                      <label class="block text-[11px] font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.selectBank') }}</label>
                      <select
                        v-model="bookingForm.bank_name"
                        class="w-full px-2.5 py-1.5 text-xs bg-ivory border border-sand/40 rounded focus:outline-none focus:border-forest"
                      >
                        <option v-for="(bank, bIdx) in activeBankAccounts" :key="bIdx" :value="bank.bank_name">
                          {{ bank.bank_name }}
                        </option>
                        <option value="QRIS">QRIS</option>
                        <option value="Lainnya">Bank Lainnya</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-[11px] font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.senderName') }}</label>
                      <input
                        v-model="bookingForm.sender_name"
                        type="text"
                        :placeholder="$t('landing.bookingModal.senderPlaceholder')"
                        class="w-full px-2.5 py-1.5 text-xs bg-ivory border border-sand/40 rounded focus:outline-none focus:border-forest"
                      />
                    </div>

                    <div>
                      <label class="block text-[11px] font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.refNumber') }}</label>
                      <input
                        v-model="bookingForm.reference_number"
                        type="text"
                        :placeholder="$t('landing.bookingModal.refNumberPlaceholder')"
                        class="w-full px-2.5 py-1.5 text-xs bg-ivory border border-sand/40 rounded focus:outline-none focus:border-forest font-mono"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Special Requests -->
              <div>
                <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.bookingModal.specialRequests') }}</label>
                <textarea
                  v-model="bookingForm.special_requests"
                  rows="2"
                  class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
                ></textarea>
              </div>

              <div class="pt-2">
                <button
                  type="submit"
                  :disabled="submitting"
                  class="w-full py-3.5 bg-forest text-white text-xs font-semibold uppercase tracking-widest rounded hover:bg-forest-800 transition-colors shadow disabled:opacity-50 flex items-center justify-center space-x-2"
                >
                  <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                  <span>{{ submitting ? $t('landing.bookingModal.submittingBtn') : $t('landing.bookingModal.submitBtn') }}</span>
                </button>
              </div>
            </form>
          </div>

      </div>
    </div>

    <!-- TRACK BOOKING STATUS MODAL -->
    <div
      v-if="trackModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm animate-fade-in"
      @click.self="closeTrackModal()"
    >
      <div class="bg-ivory text-charcoal rounded-md max-w-lg w-full max-h-[92vh] overflow-y-auto shadow-2xl border border-sand/40 relative p-6 sm:p-8">
        <button
          @click="closeTrackModal()"
          class="absolute top-5 right-5 p-1.5 text-taupe hover:text-charcoal bg-white rounded-full border border-sand/30"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="space-y-5">
          <div class="space-y-1 pr-6">
            <span class="text-xs uppercase tracking-[0.25em] text-gold font-bold">TRACK RESERVATION</span>
            <h3 class="font-display text-2xl text-forest">{{ $t('landing.trackModal.title') }}</h3>
            <p class="text-xs text-taupe font-light">{{ $t('landing.trackModal.desc') }}</p>
          </div>

          <form @submit.prevent="submitTrackBooking" class="space-y-4">
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.trackModal.bookingCode') }}</label>
              <input
                v-model="trackForm.booking_number"
                type="text"
                required
                placeholder="BK20260823A1B2"
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest font-mono uppercase"
              />
            </div>

            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.trackModal.contact') }}</label>
              <input
                v-model="trackForm.contact"
                type="text"
                required
                placeholder="guest@example.com"
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>

            <button
              type="submit"
              :disabled="tracking"
              class="w-full py-3 bg-forest text-white text-xs font-semibold uppercase tracking-widest rounded hover:bg-forest-800 transition-colors shadow disabled:opacity-50 flex items-center justify-center space-x-2"
            >
              <span v-if="tracking" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span>{{ tracking ? $t('landing.trackModal.searching') : $t('landing.trackModal.submitBtn') }}</span>
            </button>
          </form>

          <!-- Error Alert -->
          <div v-if="trackError" class="p-3.5 bg-red-50 border border-red-200 rounded text-xs text-red-800 animate-fade-in">
            {{ trackError }}
          </div>

          <!-- RESULT VIEW -->
          <div v-if="trackResult" class="p-5 bg-white rounded border border-sand/40 space-y-4 animate-fade-in shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-sand/20">
              <div>
                <span class="text-[10px] uppercase tracking-wider text-taupe block">{{ $t('landing.trackModal.bookingCode') }}</span>
                <span class="font-mono font-bold text-forest text-lg">{{ trackResult.booking_number }}</span>
              </div>
              <span
                :class="[
                  'px-3 py-1 rounded text-xs font-bold uppercase tracking-wider',
                  trackResult.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                  trackResult.status === 'checked_in' ? 'bg-blue-100 text-blue-800' :
                  trackResult.status === 'checked_out' ? 'bg-gray-100 text-gray-800' :
                  trackResult.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                  'bg-yellow-100 text-yellow-800'
                ]"
              >
                {{
                  trackResult.status === 'confirmed' ? $t('landing.trackModal.confirmed') :
                  trackResult.status === 'checked_in' ? $t('landing.trackModal.checkedIn') :
                  trackResult.status === 'checked_out' ? $t('landing.trackModal.checkedOut') :
                  trackResult.status === 'cancelled' ? $t('landing.trackModal.cancelled') :
                  $t('landing.trackModal.pending')
                }}
              </span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
              <div>
                <span class="text-taupe block text-[11px]">{{ $t('landing.trackModal.guestName') }}</span>
                <span class="font-semibold text-charcoal">{{ trackResult.guest?.name }}</span>
              </div>
              <div>
                <span class="text-taupe block text-[11px]">{{ $t('landing.trackModal.source') }}</span>
                <span class="font-semibold text-forest uppercase tracking-wider text-[10px]">{{ trackResult.source || 'Website' }}</span>
              </div>
              <div>
                <span class="text-taupe block text-[11px]">{{ $t('landing.trackModal.checkIn') }}</span>
                <span class="font-medium text-charcoal">{{ trackResult.check_in_date }}</span>
              </div>
              <div>
                <span class="text-taupe block text-[11px]">{{ $t('landing.trackModal.checkOut') }}</span>
                <span class="font-medium text-charcoal">{{ trackResult.check_out_date }}</span>
              </div>
            </div>

            <!-- Rooms List in Track Modal Result -->
            <div v-if="trackResult.rooms && trackResult.rooms.length > 0" class="pt-2 border-t border-sand/20 space-y-1.5">
              <div class="flex items-center justify-between">
                <span class="text-taupe text-[11px] font-semibold uppercase tracking-wider">{{ $t('landing.bookingModal.bookedRoomsList') }}</span>
                <span v-if="trackResult.rooms.length > 1" class="px-2 py-0.5 bg-forest text-gold text-[10px] font-bold rounded-full">
                  {{ $t('landing.bookingModal.multiRoomBadge', { count: trackResult.rooms.length }) }}
                </span>
              </div>
              <div class="space-y-1">
                <div v-for="rm in trackResult.rooms" :key="rm.id" class="p-2 bg-ivory rounded border border-sand/30 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1">
                  <div>
                    <span class="font-bold text-forest font-mono">Kamar {{ rm.room_number }}</span>
                    <span v-if="rm.roomType?.name || rm.room_type?.name" class="text-taupe text-[11px] ml-1">({{ rm.roomType?.name || rm.room_type?.name }})</span>
                  </div>
                  <span class="font-mono text-charcoal text-[11px]" v-if="rm.pivot?.subtotal">
                    {{ formatCurrency(rm.pivot.subtotal) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="pt-2 border-t border-sand/20 flex items-center justify-between text-xs">
              <div>
                <span class="text-taupe block text-[11px]">{{ $t('landing.trackModal.totalAmount') }}</span>
                <span class="font-bold text-forest text-sm">{{ formatCurrency(trackResult.total_amount) }}</span>
              </div>
              <div class="text-right">
                <span class="text-taupe block text-[11px]">{{ $t('landing.trackModal.paidDeposit') }}</span>
                <span class="font-bold text-gold text-sm">{{ formatCurrency(trackResult.deposit_amount || 0) }}</span>
              </div>
            </div>

            <div v-if="trackResult.notes" class="p-2.5 bg-ivory rounded text-[11px] text-taupe border border-sand/30">
              <strong class="text-charcoal block">{{ $t('landing.trackModal.paymentNotes') }}</strong>
              <span>{{ trackResult.notes }}</span>
            </div>

            <!-- DESTINATION BANK ACCOUNTS FOR TRACK MODAL -->
            <div class="p-3 bg-forest/5 border border-forest/10 rounded text-left text-xs space-y-1.5">
              <p class="font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.bankDestination') }}</p>
              <div class="space-y-1">
                <p v-for="(bank, bIdx) in activeBankAccounts" :key="bIdx" class="text-taupe">
                  <strong>{{ bank.bank_name }}:</strong> <span class="font-mono font-semibold text-charcoal">{{ bank.account_number }}</span> a/n {{ bank.account_holder }}
                </p>
              </div>
            </div>

            <!-- QRIS CODE BOX FOR TRACK MODAL -->
            <div v-if="hasQrisUrl" class="p-3 bg-white border border-sand/40 rounded text-left space-y-2 shadow-xs">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.payViaQris') }}</span>
                <span class="text-[10px] bg-gold text-forest px-2 py-0.5 rounded font-bold uppercase">{{ $t('landing.bookingModal.allEwallets') }}</span>
              </div>
              <div class="flex items-center gap-3">
                <img
                  :src="formattedQrisUrl"
                  alt="QRIS Code"
                  @click="qrisPreviewModalUrl = formattedQrisUrl"
                  class="w-24 h-24 object-contain bg-white p-1.5 rounded border border-sand/30 shadow-xs cursor-pointer hover:scale-105 transition-transform"
                  :title="$t('landing.bookingModal.enlargeQris')"
                />
                <div class="text-xs text-taupe space-y-1">
                  <p class="text-[11px] leading-snug">{{ paymentSettings.qris_notes || 'Pindai QRIS untuk pembayaran langsung.' }}</p>
                  <button
                    type="button"
                    @click="qrisPreviewModalUrl = formattedQrisUrl"
                    class="text-[11px] text-forest font-bold underline hover:text-gold transition-colors inline-flex items-center space-x-1"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>{{ $t('landing.bookingModal.enlargeQris') }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- WA CONFIRMATION & UPLOAD BOX FOR TRACK MODAL -->
            <div class="space-y-2.5 pt-2 border-t border-sand/20">
              <a
                :href="getWhatsAppConfirmUrl(
                  trackResult.booking_number,
                  trackResult.guest?.name,
                  'BCA',
                  trackResult.deposit_amount,
                  ''
                )"
                target="_blank"
                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider rounded transition-colors shadow flex items-center justify-center space-x-2"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                </svg>
                <span>{{ $t('landing.trackModal.waConfirm') }}</span>
              </a>

              <div class="p-3 bg-white border border-sand/40 rounded space-y-2 text-left shadow-sm">
                <label class="block text-[11px] font-bold text-forest uppercase tracking-wider">{{ $t('landing.trackModal.uploadStruk') }}</label>
                <div class="flex items-center gap-2">
                  <input
                    type="file"
                    accept="image/*,application/pdf"
                    @change="handleReceiptFileChange"
                    class="text-[11px] text-taupe flex-1 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-sand/30 file:text-forest hover:file:bg-sand/50"
                  />
                  <button
                    type="button"
                    @click="uploadReceiptFile(trackResult.booking_number, trackResult.guest?.email || trackResult.guest?.phone || trackForm.contact)"
                    :disabled="uploadingReceipt || !receiptFile"
                    class="px-3 py-1.5 bg-forest text-white text-[11px] font-semibold rounded hover:bg-forest-800 disabled:opacity-50 transition-colors whitespace-nowrap"
                  >
                    {{ uploadingReceipt ? $t('landing.bookingModal.uploading') : $t('landing.bookingModal.uploadBtn') }}
                  </button>
                </div>
                <div v-if="receiptUploadSuccess" class="text-[11px] text-emerald-700 font-medium">
                  ✓ {{ receiptUploadSuccess }}
                </div>
                <div v-if="receiptUploadError" class="text-[11px] text-red-600 font-medium">
                  ⚠ {{ receiptUploadError }}
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- HALL RESERVATION MODAL -->
  <div
    v-if="hallBookingModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-fade-in"
    @click.self="closeHallBookingModal"
  >
    <div class="bg-ivory text-charcoal rounded-sm max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-sand/40 relative p-6 sm:p-8">
      <button
        @click="closeHallBookingModal"
        class="absolute top-4 right-4 z-10 p-2 text-charcoal/70 hover:text-charcoal bg-white/80 rounded-full"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- SUCCESS SCREEN -->
      <div v-if="hallBookingSuccessData" class="space-y-6 text-center py-4 animate-fade-in">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto border border-emerald-300 shadow-inner">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <div class="space-y-2">
          <span class="text-[11px] uppercase tracking-[0.25em] text-gold font-bold block">{{ $t('landing.hallModal.successTitle') }}</span>
          <h3 class="font-display text-2xl sm:text-3xl text-forest font-semibold">
            {{ $t('landing.hallModal.thankYou', { name: hallBookingSuccessData.customer_name }) }}
          </h3>
          <p class="text-xs text-taupe font-light max-w-md mx-auto leading-relaxed">
            {{ $t('landing.hallModal.successDesc') }}
          </p>
        </div>

        <!-- SUMMARY BOX -->
        <div class="bg-white p-4 rounded border border-sand/40 text-left text-xs space-y-2 shadow-sm">
          <div class="flex justify-between border-b border-sand/20 pb-2">
            <span class="text-taupe font-medium">{{ $t('landing.hallModal.bookingCode') }}:</span>
            <span class="font-bold text-forest font-mono text-sm bg-sand/30 px-2 py-0.5 rounded">{{ hallBookingSuccessData.booking_number }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-taupe">Hall / Ruang:</span>
            <span class="font-semibold text-charcoal">{{ hallBookingSuccessData.hall?.name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-taupe">Tanggal & Waktu:</span>
            <span class="font-semibold text-charcoal">{{ formatDate(hallBookingSuccessData.event_date) }} ({{ hallBookingSuccessData.start_time }} - {{ hallBookingSuccessData.end_time }})</span>
          </div>
          <div class="flex justify-between">
            <span class="text-taupe">Estimasi Biaya:</span>
            <span class="font-bold text-forest">{{ formatCurrency(hallBookingSuccessData.total_amount) }}</span>
          </div>
        </div>

        <div class="space-y-3 pt-2">
          <div class="p-3.5 bg-forest/5 border border-forest/10 rounded text-left text-xs space-y-1.5">
            <p class="font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.bankDestination') }}</p>
            <div class="space-y-1">
              <p v-for="(bank, bIdx) in activeBankAccounts" :key="bIdx" class="text-taupe">
                <strong>{{ bank.bank_name }}:</strong> <span class="font-mono font-semibold text-charcoal">{{ bank.account_number }}</span> a/n {{ bank.account_holder }}
              </p>
            </div>
          </div>

          <!-- QRIS Box in Hall Success Screen -->
          <div v-if="paymentSettings.qris_url" class="p-3.5 bg-white border border-sand/40 rounded text-left space-y-2 shadow-xs">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.payViaQris') }}</span>
              <span class="text-[10px] bg-gold text-forest px-2 py-0.5 rounded font-bold uppercase">{{ $t('landing.bookingModal.allEwallets') }}</span>
            </div>
            <div class="flex items-center gap-3">
              <img
                :src="paymentSettings.qris_url"
                alt="QRIS Code"
                @click="qrisPreviewModalUrl = paymentSettings.qris_url"
                class="w-24 h-24 object-contain bg-white p-1.5 rounded border border-sand/30 shadow-xs cursor-pointer hover:scale-105 transition-transform"
                :title="$t('landing.bookingModal.enlargeQris')"
              />
              <div class="text-xs text-taupe space-y-1">
                <p class="text-[11px] leading-snug">{{ paymentSettings.qris_notes || 'Pindai QRIS untuk pembayaran langsung.' }}</p>
                <button
                  type="button"
                  @click="qrisPreviewModalUrl = paymentSettings.qris_url"
                  class="text-[11px] text-forest font-bold underline hover:text-gold transition-colors inline-flex items-center space-x-1"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <span>{{ $t('landing.bookingModal.enlargeQris') }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- OPSI 1: WA CONFIRMATION BUTTON -->
          <a
            :href="getWhatsAppHallConfirmUrl(
              hallBookingSuccessData.booking_number,
              hallBookingSuccessData.customer_name,
              hallBookingSuccessData.hall?.name,
              formatDate(hallBookingSuccessData.event_date),
              hallBookingSuccessData.total_amount
            )"
            target="_blank"
            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider rounded transition-colors shadow flex items-center justify-center space-x-2"
          >
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
              <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
            </svg>
            <span>{{ $t('landing.bookingModal.waConfirm') }}</span>
          </a>

          <!-- OPSI 2: UPLOAD STRUK BOX -->
          <div class="p-3 bg-white border border-sand/40 rounded space-y-2 text-left shadow-sm">
            <label class="block text-[11px] font-bold text-forest uppercase tracking-wider">{{ $t('landing.bookingModal.uploadStruk') }}</label>
            <div class="flex items-center gap-2">
              <input
                type="file"
                accept="image/*,application/pdf"
                @change="handleReceiptFileChange"
                class="text-[11px] text-taupe flex-1 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-sand/30 file:text-forest hover:file:bg-sand/50"
              />
              <button
                type="button"
                @click="uploadReceiptFile(hallBookingSuccessData.booking_number, hallBookingSuccessData.customer_email || hallBookingSuccessData.customer_phone)"
                :disabled="uploadingReceipt || !receiptFile"
                class="px-3 py-1.5 bg-forest text-white text-[11px] font-semibold rounded hover:bg-forest-800 disabled:opacity-50 transition-colors whitespace-nowrap"
              >
                {{ uploadingReceipt ? $t('landing.bookingModal.uploading') : $t('landing.bookingModal.uploadBtn') }}
              </button>
            </div>
            <div v-if="receiptUploadSuccess" class="text-[11px] text-emerald-700 font-medium">
              ✓ {{ receiptUploadSuccess }}
            </div>
            <div v-if="receiptUploadError" class="text-[11px] text-red-600 font-medium">
              ⚠ {{ receiptUploadError }}
            </div>
          </div>
        </div>

        <p class="text-xs text-taupe italic">
          {{ $t('landing.hallModal.autoNotice') }}
        </p>

        <button
          @click="closeHallBookingModal"
          class="w-full py-3.5 bg-forest text-white text-xs font-semibold uppercase tracking-widest rounded hover:bg-forest-800 transition-colors shadow"
        >
          {{ $t('landing.hallModal.doneBtn') }}
        </button>
      </div>

      <!-- FORM INPUT -->
      <div v-else class="space-y-6">
        <div class="border-b border-sand/30 pb-4">
          <h3 class="font-display text-2xl text-forest">{{ $t('landing.hallModal.title') }}</h3>
          <p class="text-xs text-taupe font-light mt-1">{{ $t('landing.hallModal.desc') }}</p>
        </div>

        <div v-if="hallBookingErrorMessage" class="p-3 bg-red-50 border border-red-200 rounded text-xs text-red-800">
          ⚠ {{ hallBookingErrorMessage }}
        </div>

        <form @submit.prevent="submitHallBooking" class="space-y-4 text-left">
          <!-- Name & Email -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.fullName') }}</label>
              <input
                v-model="hallBookingForm.customer_name"
                type="text"
                required
                placeholder="John Doe"
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.email') }}</label>
              <input
                v-model="hallBookingForm.customer_email"
                type="email"
                required
                placeholder="john@example.com"
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
          </div>

          <!-- Phone & Company -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.phone') }}</label>
              <input
                v-model="hallBookingForm.customer_phone"
                type="tel"
                required
                placeholder="+62 812 3456 7890"
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.company') }}</label>
              <input
                v-model="hallBookingForm.customer_company"
                type="text"
                placeholder="PT Example Indonesia"
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
          </div>

          <!-- Event Name & Hall Selection -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.eventName') }}</label>
              <input
                v-model="hallBookingForm.event_name"
                type="text"
                required
                :placeholder="$t('landing.hallModal.eventNamePlaceholder')"
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.selectHall') }}</label>
              <select
                v-model="hallBookingForm.hall_id"
                required
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              >
                <option v-for="h in hallsList" :key="h.id" :value="h.id">
                  {{ h.name }} ({{ formatCurrency(h.price_per_hour) }}/jam)
                </option>
              </select>
            </div>
          </div>

          <!-- Event Date & Times -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.eventDate') }}</label>
              <input
                v-model="hallBookingForm.event_date"
                type="date"
                required
                :min="todayDate"
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.startTime') }}</label>
              <input
                v-model="hallBookingForm.start_time"
                type="time"
                required
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.endTime') }}</label>
              <input
                v-model="hallBookingForm.end_time"
                type="time"
                required
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
          </div>

          <!-- Attendees Count & Special Requests -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.attendees') }}</label>
              <input
                v-model.number="hallBookingForm.attendees"
                type="number"
                min="1"
                required
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs uppercase tracking-wider font-semibold text-charcoal mb-1">{{ $t('landing.hallModal.specialRequests') }}</label>
              <input
                v-model="hallBookingForm.special_requests"
                type="text"
                placeholder="Layout U-Shape, Microphone 4 unit, dll."
                class="w-full px-3 py-2 text-sm bg-white border border-sand/40 rounded focus:outline-none focus:border-forest"
              />
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-4 flex items-center justify-end space-x-3">
            <button
              type="button"
              @click="closeHallBookingModal"
              class="px-4 py-2 text-xs uppercase tracking-wider font-semibold text-taupe hover:text-charcoal"
            >
              Tutup
            </button>
            <button
              type="submit"
              :disabled="submittingHallBooking"
              class="px-6 py-3 bg-forest text-white text-xs font-bold uppercase tracking-widest rounded hover:bg-forest-800 transition-colors shadow-lg disabled:opacity-50"
            >
              {{ submittingHallBooking ? $t('landing.hallModal.submittingBtn') : $t('landing.hallModal.submitBtn') }}
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>

  <!-- QRIS LIGHTBOX PREVIEW MODAL -->
  <div
    v-if="qrisPreviewModalUrl"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fade-in"
    @click.self="qrisPreviewModalUrl = null"
  >
    <div class="bg-white rounded-md p-6 max-w-sm w-full text-center space-y-4 shadow-2xl border border-sand/40 relative">
      <button
        @click="qrisPreviewModalUrl = null"
        class="absolute top-3 right-3 p-1.5 text-taupe hover:text-charcoal bg-ivory rounded-full"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <div>
        <span class="text-[10px] uppercase tracking-[0.25em] text-gold font-bold block">{{ $t('landing.bookingModal.qrisSubtitle') }}</span>
        <h3 class="font-display text-xl text-forest font-bold">{{ $t('landing.bookingModal.qrisHeader') }}</h3>
      </div>

      <div class="p-3 bg-ivory rounded border border-sand/30 flex justify-center">
        <img :src="qrisPreviewModalUrl" alt="QRIS Enlarged" class="w-64 h-64 object-contain bg-white p-2 rounded shadow" />
      </div>

      <p class="text-xs text-taupe">
        {{ paymentSettings.qris_notes || 'Pindai kode QRIS menggunakan aplikasi m-Banking atau E-Wallet pilihan Anda.' }}
      </p>

      <button
        @click="qrisPreviewModalUrl = null"
        class="w-full py-2.5 bg-forest text-white text-xs font-bold uppercase tracking-wider rounded hover:bg-forest-800 transition-colors"
      >
        {{ $t('landing.bookingModal.closePreview') }}
      </button>
    </div>
  </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, h } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useI18n } from 'vue-i18n'
import axios from 'axios'

const authStore = useAuthStore()
const { t, locale } = useI18n()

const currentLocale = computed(() => locale.value)
const isScrolled = ref(false)
const mobileMenuOpen = ref(false)
const selectedRoom = ref(null)

const defaultBankAccounts = [
  { bank_name: 'Bank BCA', account_number: '8830-192-800', account_holder: 'PT AURA Hospitality Indonesia', is_active: true },
  { bank_name: 'Bank Mandiri', account_number: '137-00-9918-2200', account_holder: 'PT AURA Hospitality Indonesia', is_active: true }
]

// Dynamic Payment & QRIS Settings State
const paymentSettings = ref({
  bank_accounts: defaultBankAccounts,
  qris_url: null,
  qris_notes: 'Pindai kode QRIS menggunakan m-Banking atau e-Wallet (Gopay, OVO, Dana, LinkAja, ShopeePay) untuk pembayaran.'
})

const activeBankAccounts = computed(() => {
  const accounts = paymentSettings.value?.bank_accounts
  if (Array.isArray(accounts) && accounts.length > 0) {
    const filtered = accounts.filter(b => b.is_active !== false && b.is_active !== 'false' && b.is_active !== 0 && b.is_active !== '0')
    if (filtered.length > 0) return filtered
  }
  return defaultBankAccounts
})

const qrisPreviewModalUrl = ref(null)

const hasQrisUrl = computed(() => {
  return !!(paymentSettings.value?.qris_url || paymentSettings.value?.qris_image_path)
})

const formattedQrisUrl = computed(() => {
  const raw = paymentSettings.value?.qris_url || paymentSettings.value?.qris_image_path
  if (!raw) return ''
  if (raw.startsWith('http://') || raw.startsWith('https://')) return raw
  if (raw.startsWith('storage/')) return `http://localhost:8000/${raw}`
  if (raw.startsWith('/storage/')) return `http://localhost:8000${raw}`
  return `http://localhost:8000/storage/${raw}`
})

// Live Reservation Modal State
const bookingModalOpen = ref(false)
const submitting = ref(false)
const bookingSuccessData = ref(null)
const bookingErrorMessage = ref('')
const roomTypesList = ref([])

// Track Booking Modal State
const trackModalOpen = ref(false)
const tracking = ref(false)
const trackForm = ref({
  booking_number: '',
  contact: ''
})
const trackResult = ref(null)
const trackError = ref('')

// Receipt Upload State
const receiptFile = ref(null)
const uploadingReceipt = ref(false)
const receiptUploadSuccess = ref('')
const receiptUploadError = ref('')

function handleReceiptFileChange(event) {
  receiptFile.value = event.target.files[0] || null
}

async function uploadReceiptFile(bookingNumber, contact) {
  if (!receiptFile.value) return
  uploadingReceipt.value = true
  receiptUploadSuccess.value = ''
  receiptUploadError.value = ''

  const formData = new FormData()
  formData.append('booking_number', bookingNumber || '')
  if (contact) {
    formData.append('contact', contact)
  }
  formData.append('receipt', receiptFile.value)

  try {
    const res = await axios.post('http://localhost:8000/api/public/bookings/upload-receipt', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data && res.data.message) {
      receiptUploadSuccess.value = res.data.message
      receiptFile.value = null
    }
  } catch (err) {
    console.error('Failed to upload receipt:', err)
    const serverErr = err.response?.data?.errors?.receipt?.[0] || 
                      err.response?.data?.errors?.booking_number?.[0] || 
                      err.response?.data?.message
    receiptUploadError.value = serverErr || 'Gagal mengunggah bukti transfer. Pastikan file berupa gambar (JPG, PNG, WEBP) atau PDF (maks 10MB).'
  } finally {
    uploadingReceipt.value = false
  }
}

function getWhatsAppDirectUrl() {
  const hotelWaNumber = paymentSettings.value?.whatsapp_number || import.meta.env.VITE_HOTEL_WA_NUMBER || '6281234567890'
  const message = encodeURIComponent(t('landing.whatsapp.greeting'))
  return `https://wa.me/${hotelWaNumber}?text=${message}`
}

function getWhatsAppConfirmUrl(bookingNumber, guestName, bankName, depositAmount, refNum) {
  const hotelWaNumber = paymentSettings.value?.whatsapp_number || import.meta.env.VITE_HOTEL_WA_NUMBER || '6281234567890'
  const message = encodeURIComponent(
    `Halo Concierge AURA Hotel,\n` +
    `Saya ingin mengonfirmasi pembayaran DP Jaminan Menginap:\n\n` +
    `• Kode Booking: ${bookingNumber || '-'}\n` +
    `• Nama Tamu: ${guestName || '-'}\n` +
    `• Bank Tujuan: ${bankName || 'BCA'}\n` +
    `• DP Terbayar: ${formatCurrency(depositAmount || 0)}\n` +
    (refNum ? `• No. Referensi: ${refNum}\n\n` : `\n`) +
    `Berikut saya lampirkan bukti transfernya. Mohon verifikasinya. Terima kasih!`
  )
  
  return `https://wa.me/${hotelWaNumber}?text=${message}`
}

const todayDate = computed(() => {
  return new Date().toISOString().split('T')[0]
})

const bookingForm = ref({
  name: '',
  email: '',
  phone: '',
  check_in_date: new Date().toISOString().split('T')[0],
  check_out_date: new Date(Date.now() + 2 * 86400000).toISOString().split('T')[0],
  adults: 2,
  children: 0,
  room_type_id: null,
  payment_option: 'pay_at_hotel',
  bank_name: 'BCA',
  sender_name: '',
  reference_number: '',
  special_requests: ''
})

function formatCurrency(val) {
  if (val === null || val === undefined || val === '') return 'Rp 0'
  const num = Number(val)
  if (isNaN(num)) return val
  return 'Rp ' + num.toLocaleString('id-ID')
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  try {
    const cleanStr = String(dateStr).split('T')[0]
    const d = new Date(cleanStr)
    if (isNaN(d.getTime())) return dateStr
    return d.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    })
  } catch (e) {
    return dateStr
  }
}

// Hall Booking & List State
const hallsList = ref([])
const hallsPage = ref(1)
const hallsPerPage = ref(3)
const hallBookingModalOpen = ref(false)
const selectedHall = ref(null)
const submittingHallBooking = ref(false)
const hallBookingErrorMessage = ref('')
const hallBookingSuccessData = ref(null)

const sampleHallImages = [
  'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=800&q=80',
  'https://images.unsplash.com/photo-1431540015161-0bf868a2d407?auto=format&fit=crop&w=800&q=80',
  'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=800&q=80',
  'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80',
  'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=800&q=80',
]

const totalHallsPages = computed(() => {
  if (!hallsList.value.length) return 1
  return Math.ceil(hallsList.value.length / hallsPerPage.value)
})

const paginatedHalls = computed(() => {
  const start = (hallsPage.value - 1) * hallsPerPage.value
  return hallsList.value.slice(start, start + hallsPerPage.value)
})

const displayHalls = computed(() => {
  if (!hallsList.value.length) {
    return [
      { id: 1, name: 'Grand Ballroom A', hall_type: 'Grand Ballroom', capacity: 300, area_sqm: 250, price_per_hour: 2000000, description: 'Grand ballroom perfect for weddings and corporate galas.', image: sampleHallImages[0] },
      { id: 2, name: 'Executive Meeting Room 1', hall_type: 'Meeting Room', capacity: 20, area_sqm: 40, price_per_hour: 300000, description: 'Intimate meeting room ideal for board meetings and team workshops.', image: sampleHallImages[1] },
      { id: 3, name: 'Royal Conference Hall', hall_type: 'Conference Hall', capacity: 100, area_sqm: 120, price_per_hour: 800000, description: 'Professional conference hall equipped with HD projectors and audio.', image: sampleHallImages[2] },
    ]
  }

  return paginatedHalls.value.map((h, idx) => {
    const globalIdx = (hallsPage.value - 1) * hallsPerPage.value + idx
    const imgUrl = h.image_url || sampleHallImages[globalIdx % sampleHallImages.length]
    return {
      ...h,
      formattedPrice: formatCurrency(h.price_per_hour),
      image: imgUrl
    }
  })
})

function nextHallsPage() {
  if (hallsPage.value < totalHallsPages.value) {
    hallsPage.value++
  }
}

function prevHallsPage() {
  if (hallsPage.value > 1) {
    hallsPage.value--
  }
}

const hallBookingForm = ref({
  hall_id: null,
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  customer_company: '',
  event_name: '',
  event_date: new Date(Date.now() + 86400000).toISOString().split('T')[0],
  start_time: '09:00',
  end_time: '17:00',
  attendees: 50,
  payment_option: 'pay_at_hotel',
  special_requests: ''
})

function openHallModal(hall) {
  selectedHall.value = hall
}

function openHallBookingModal(presetHall = null) {
  selectedHall.value = null
  hallBookingSuccessData.value = null
  hallBookingErrorMessage.value = ''

  if (presetHall) {
    hallBookingForm.value.hall_id = presetHall.id
  } else if (hallsList.value.length > 0) {
    hallBookingForm.value.hall_id = hallsList.value[0].id
  } else {
    hallBookingForm.value.hall_id = null
  }

  hallBookingModalOpen.value = true
}

function closeHallBookingModal() {
  hallBookingModalOpen.value = false
  hallBookingSuccessData.value = null
  hallBookingErrorMessage.value = ''
}

async function submitHallBooking() {
  submittingHallBooking.value = true
  hallBookingErrorMessage.value = ''
  hallBookingSuccessData.value = null

  try {
    const res = await axios.post('http://localhost:8000/api/public/hall-bookings', hallBookingForm.value)
    if (res.data && (res.data.data || res.data.booking_number)) {
      const dataPayload = res.data.data || res.data
      const selectedHallObj = hallsList.value.find(h => h.id == hallBookingForm.value.hall_id)
      hallBookingSuccessData.value = {
        ...dataPayload,
        booking_number: dataPayload.booking_number || res.data.booking_number,
        hall: dataPayload.hall || selectedHallObj || { name: 'Event Hall' }
      }
    }
  } catch (err) {
    console.error('Hall booking submission error:', err)
    if (err.response?.data?.errors) {
      const errs = err.response.data.errors
      const firstKey = Object.keys(errs)[0]
      hallBookingErrorMessage.value = errs[firstKey][0] || err.response.data.message
    } else if (err.response?.data?.message) {
      hallBookingErrorMessage.value = err.response.data.message
    } else {
      hallBookingErrorMessage.value = 'Gagal mengirim pemesanan hall. Harap periksa kembali masukan Anda.'
    }
  } finally {
    submittingHallBooking.value = false
  }
}

function getWhatsAppHallConfirmUrl(bookingNumber, customerName, hallName, eventDate, totalAmount) {
  const hotelWaNumber = paymentSettings.value?.whatsapp_number || import.meta.env.VITE_HOTEL_WA_NUMBER || '6281234567890'
  const message = encodeURIComponent(
    `Halo Concierge AURA Hotel,\n` +
    `Saya ingin mengonfirmasi pemesanan Hall/Ruang Acara:\n\n` +
    `• Kode Booking: ${bookingNumber || '-'}\n` +
    `• Nama Pemesan: ${customerName || '-'}\n` +
    `• Ruang Hall: ${hallName || '-'}\n` +
    `• Tanggal Acara: ${eventDate || '-'}\n` +
    `• Estimasi Total: ${formatCurrency(totalAmount || 0)}\n\n` +
    `Mohon bantuan verifikasinya. Terima kasih!`
  )
  
  return `https://wa.me/${hotelWaNumber}?text=${message}`
}

function changeLanguage(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)
}

function handleScroll() {
  isScrolled.value = window.scrollY > 40
}

const socialSettings = ref({
  instagram: 'https://instagram.com/aurahotels',
  twitter: 'https://twitter.com/aurahotels',
  youtube: 'https://youtube.com/@aurahotels',
  facebook: 'https://facebook.com/aurahotels',
  linkedin: 'https://linkedin.com/company/aurahotels',
  threads: 'https://threads.net/@aurahotels',
})

onMounted(async () => {
  window.addEventListener('scroll', handleScroll)
  await fetchRoomTypes()
  await fetchHalls()
  await fetchPaymentSettings()
  await fetchSocialSettings()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

async function fetchSocialSettings() {
  try {
    const res = await axios.get('/api/public/settings/social')
    if (res.data && res.data.data) {
      socialSettings.value = { ...socialSettings.value, ...res.data.data }
    }
  } catch (err) {
    try {
      const resFallback = await axios.get('http://localhost:8000/api/public/settings/social')
      if (resFallback.data && resFallback.data.data) {
        socialSettings.value = { ...socialSettings.value, ...resFallback.data.data }
      }
    } catch (e) {
      console.warn('Could not load social settings, using default URLs.')
    }
  }
}

async function fetchPaymentSettings() {
  try {
    const res = await axios.get('/api/public/settings/payment')
    if (res.data && res.data.data) {
      paymentSettings.value = res.data.data
      if (activeBankAccounts.value.length > 0 && (!bookingForm.value.bank_name || bookingForm.value.bank_name === 'BCA')) {
        bookingForm.value.bank_name = activeBankAccounts.value[0].bank_name
      }
    }
  } catch (err) {
    try {
      const resFallback = await axios.get('http://localhost:8000/api/public/settings/payment')
      if (resFallback.data && resFallback.data.data) {
        paymentSettings.value = resFallback.data.data
        if (activeBankAccounts.value.length > 0 && (!bookingForm.value.bank_name || bookingForm.value.bank_name === 'BCA')) {
          bookingForm.value.bank_name = activeBankAccounts.value[0].bank_name
        }
      }
    } catch (e) {
      console.warn('Could not load payment settings, using default bank details.')
    }
  }
}

async function fetchRoomTypes() {
  try {
    const res = await axios.get('http://localhost:8000/api/public/room-types')
    if (res.data) {
      roomTypesList.value = res.data
    }
  } catch (err) {
    console.error('Failed to load room types:', err)
  }
}

async function fetchHalls() {
  try {
    const res = await axios.get('http://localhost:8000/api/public/halls')
    if (res.data && Array.isArray(res.data)) {
      hallsList.value = res.data
    }
  } catch (err) {
    console.error('Failed to load halls:', err)
  }
}

function openRoomModal(room) {
  selectedRoom.value = room
}

function openBookingModal(presetRoom = null) {
  selectedRoom.value = null
  bookingSuccessData.value = null
  bookingErrorMessage.value = ''
  
  if (presetRoom) {
    if (presetRoom.id && typeof presetRoom.id === 'number') {
      bookingForm.value.room_type_id = presetRoom.id
    } else if (presetRoom.title || presetRoom.name) {
      const roomName = (presetRoom.title || presetRoom.name).toLowerCase()
      const matchedType = roomTypesList.value.find(rt => rt.name.toLowerCase().includes(roomName.split(' ')[0]))
      if (matchedType) {
        bookingForm.value.room_type_id = matchedType.id
      }
    }
  } else {
    bookingForm.value.room_type_id = null
  }
  
  bookingModalOpen.value = true
}

function closeBookingModal() {
  bookingModalOpen.value = false
  bookingSuccessData.value = null
  bookingErrorMessage.value = ''
}

function openTrackModal() {
  trackModalOpen.value = true
  trackResult.value = null
  trackError.value = ''
  fetchPaymentSettings()
}

function closeTrackModal() {
  trackModalOpen.value = false
  trackResult.value = null
  trackError.value = ''
}

async function submitTrackBooking() {
  tracking.value = true
  trackError.value = ''
  trackResult.value = null
  fetchPaymentSettings()
  
  try {
    const res = await axios.get('http://localhost:8000/api/public/bookings/search', {
      params: trackForm.value
    })
    if (res.data && res.data.data) {
      trackResult.value = res.data.data
    }
  } catch (err) {
    console.error('Track booking error:', err)
    if (err.response?.data?.message) {
      trackError.value = err.response.data.message
    } else {
      trackError.value = 'Pemesanan tidak ditemukan. Harap periksa Kode Booking dan Email/No. HP Anda.'
    }
  } finally {
    tracking.value = false
  }
}

async function submitBooking() {
  submitting.value = true
  bookingErrorMessage.value = ''
  
  try {
    const res = await axios.post('http://localhost:8000/api/public/bookings', bookingForm.value)
    if (res.status === 201 || res.data) {
      bookingSuccessData.value = res.data
    }
  } catch (err) {
    console.error('Booking submission error:', err)
    if (err.response?.data?.errors) {
      const errs = err.response.data.errors
      const firstKey = Object.keys(errs)[0]
      bookingErrorMessage.value = errs[firstKey][0] || err.response.data.message
    } else if (err.response?.data?.message) {
      bookingErrorMessage.value = err.response.data.message
    } else {
      bookingErrorMessage.value = 'Gagal mengirim pemesanan. Harap periksa kembali masukan Anda.'
    }
  } finally {
    submitting.value = false
  }
}

// Room Types Pagination State
const roomsPage = ref(1)
const roomsPerPage = ref(3)

const totalRoomsPages = computed(() => {
  const totalItems = roomTypesList.value?.length || 3
  return Math.ceil(totalItems / roomsPerPage.value)
})

const paginatedRoomTypes = computed(() => {
  if (!roomTypesList.value || roomTypesList.value.length === 0) return []
  const start = (roomsPage.value - 1) * roomsPerPage.value
  return roomTypesList.value.slice(start, start + roomsPerPage.value)
})

function nextRoomsPage() {
  if (roomsPage.value < totalRoomsPages.value) {
    roomsPage.value++
  }
}

function prevRoomsPage() {
  if (roomsPage.value > 1) {
    roomsPage.value--
  }
}

// Curated luxury hotel room photos for temporary room images
const sampleRoomImages = [
  'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1000&q=80'
]

// Dynamic room list fetched from /api/public/room-types (paginated 3 per page)
const displayRooms = computed(() => {
  if (!roomTypesList.value || roomTypesList.value.length === 0) {
    return [
      {
        id: 1,
        title: t('landing.rooms.items.deluxe.title') || 'Deluxe Room',
        specs: t('landing.rooms.items.deluxe.specs') || '1 King Bed • Max 2 Guests',
        price: 'Rp 500.000',
        base_price: 500000,
        description: t('landing.rooms.items.deluxe.description') || 'Luxurious room with city views and high-end amenities.',
        image: sampleRoomImages[0]
      },
      {
        id: 2,
        title: t('landing.rooms.items.executive.title') || 'Executive Suite',
        specs: t('landing.rooms.items.executive.specs') || '1 Super King Bed • Max 3 Guests',
        price: 'Rp 800.000',
        base_price: 800000,
        description: t('landing.rooms.items.executive.description') || 'Spacious suite with private lounge access and premium service.',
        image: sampleRoomImages[1]
      },
      {
        id: 3,
        title: t('landing.rooms.items.villa.title') || 'Ocean Villa',
        specs: t('landing.rooms.items.villa.specs') || '2 Bedrooms • Private Pool',
        price: 'Rp 1.500.000',
        base_price: 1500000,
        description: t('landing.rooms.items.villa.description') || 'Exclusive villa featuring private plunge pool and ocean view.',
        image: sampleRoomImages[2]
      }
    ]
  }

  return paginatedRoomTypes.value.map((rt, idx) => {
    const globalIdx = (roomsPage.value - 1) * roomsPerPage.value + idx
    return {
      id: rt.id,
      title: rt.name,
      name: rt.name,
      capacity: rt.capacity,
      specs: rt.capacity ? `Max ${rt.capacity} ${currentLocale.value === 'en' ? 'Guests' : 'Tamu'}` : 'Luxury Room',
      price: formatCurrency(rt.base_price),
      base_price: rt.base_price,
      description: rt.description || 'Kamar mewah dengan pemandangan dan fasilitas modern untuk kenyamanan terbaik Anda.',
      image: sampleRoomImages[globalIdx % sampleRoomImages.length]
    }
  })
})

// Icon components for facilities
const PoolIcon = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M3 15a4 4 0 004 4h10a4 4 0 004-4M3 15a4 4 0 014-4h10a4 4 0 014 4M3 9a4 4 0 014-4h10a4 4 0 014 4' })]) }
const DiningIcon = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M12 6v6m0 0v6m0-6h6m-6 0H6' })]) }
const SpaIcon = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M12 3v1m0 16v1m9-9h-1M4 12H3' })]) }
const FitnessIcon = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.5', d: 'M13 10V3L4 14h7v7l9-11h-7z' })]) }

function getFacilitiesList() {
  return [
    {
      id: 1,
      name: t('landing.facilities.pool.title'),
      description: t('landing.facilities.pool.desc'),
      image: 'https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?auto=format&fit=crop&w=1000&q=80',
      icon: PoolIcon
    },
    {
      id: 2,
      name: t('landing.facilities.dining.title'),
      description: t('landing.facilities.dining.desc'),
      image: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
      icon: DiningIcon
    },
    {
      id: 3,
      name: t('landing.facilities.spa.title'),
      description: t('landing.facilities.spa.desc'),
      image: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=800&q=80',
      icon: SpaIcon
    },
    {
      id: 4,
      name: t('landing.facilities.meeting.title'),
      description: t('landing.facilities.meeting.desc'),
      image: 'https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=1000&q=80',
      icon: FitnessIcon
    }
  ]
}

function getExperiencesList() {
  return [
    {
      id: 1,
      tag: t('landing.experiences.exp1.tag'),
      title: t('landing.experiences.exp1.title'),
      description: t('landing.experiences.exp1.desc'),
      image: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 2,
      tag: t('landing.experiences.exp2.tag'),
      title: t('landing.experiences.exp2.title'),
      description: t('landing.experiences.exp2.desc'),
      image: 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 3,
      tag: t('landing.experiences.exp3.tag'),
      title: t('landing.experiences.exp3.title'),
      description: t('landing.experiences.exp3.desc'),
      image: 'https://images.unsplash.com/photo-1503220317375-aaad61436b1b?auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 4,
      tag: t('landing.experiences.exp4.tag'),
      title: t('landing.experiences.exp4.title'),
      description: t('landing.experiences.exp4.desc'),
      image: 'https://images.unsplash.com/photo-1544055700478-4be289fbecef?auto=format&fit=crop&w=600&q=80'
    }
  ]
}
</script>
