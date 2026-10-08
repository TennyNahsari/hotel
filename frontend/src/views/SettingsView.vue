<template>
  <LayoutMain>
    <div class="space-y-6 max-w-6xl mx-auto pb-12">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-md border border-sand/30 shadow-sm">
        <div>
          <span class="text-xs uppercase tracking-[0.25em] text-gold font-bold">KONFIGURASI SISTEM</span>
          <h1 class="font-display text-2xl sm:text-3xl text-forest font-normal mt-1">
            Pengaturan Aplikasi & Tampilan
          </h1>
          <p class="text-xs sm:text-sm text-taupe font-light mt-1">
            Kelola gambar hero slider landing page, rekening bank pembayaran QRIS, dan tautan media sosial hotel.
          </p>
        </div>

        <button
          @click="saveCurrentTabSettings"
          :disabled="saving"
          class="px-5 py-2.5 bg-forest text-white text-xs font-bold uppercase tracking-wider rounded hover:bg-forest-800 transition-all shadow disabled:opacity-50 flex items-center justify-center space-x-2 self-start sm:self-center"
        >
          <span v-if="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
        </button>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex border-b border-sand/40 bg-white rounded-t-md px-4 pt-2 gap-2 overflow-x-auto shadow-xs">
        <button
          @click="activeTab = 'hero'"
          :class="[
            'py-3 px-5 text-xs font-bold uppercase tracking-wider border-b-2 transition-all flex items-center space-x-2 whitespace-nowrap',
            activeTab === 'hero'
              ? 'border-gold text-forest bg-ivory/50 rounded-t-md'
              : 'border-transparent text-taupe hover:text-forest hover:bg-ivory/20'
          ]"
        >
          <span>🖼️ Hero Slider Landing Page</span>
          <span
            v-if="heroSliders.filter(s => s.is_active !== false).length > 0"
            class="px-2 py-0.5 text-[10px] bg-emerald-100 text-emerald-800 rounded-full font-semibold"
          >
            {{ heroSliders.filter(s => s.is_active !== false).length }} Aktif
          </span>
        </button>

        <button
          @click="activeTab = 'payment'"
          :class="[
            'py-3 px-5 text-xs font-bold uppercase tracking-wider border-b-2 transition-all flex items-center space-x-2 whitespace-nowrap',
            activeTab === 'payment'
              ? 'border-gold text-forest bg-ivory/50 rounded-t-md'
              : 'border-transparent text-taupe hover:text-forest hover:bg-ivory/20'
          ]"
        >
          <span>💳 Rekening Bank & QRIS</span>
        </button>

        <button
          @click="activeTab = 'social'"
          :class="[
            'py-3 px-5 text-xs font-bold uppercase tracking-wider border-b-2 transition-all flex items-center space-x-2 whitespace-nowrap',
            activeTab === 'social'
              ? 'border-gold text-forest bg-ivory/50 rounded-t-md'
              : 'border-transparent text-taupe hover:text-forest hover:bg-ivory/20'
          ]"
        >
          <span>🌐 Media Sosial</span>
        </button>
      </div>

      <!-- Alert Notifications -->
      <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-200 rounded text-xs text-emerald-800 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ successMessage }}</span>
        </div>
        <button @click="successMessage = ''" class="text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
      </div>

      <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded text-xs text-red-800 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ errorMessage }}</span>
        </div>
        <button @click="errorMessage = ''" class="text-red-600 hover:text-red-900 font-bold">✕</button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center text-taupe space-y-2 bg-white rounded-b-md shadow-sm border border-sand/30">
        <div class="w-8 h-8 border-3 border-forest border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="text-xs font-medium">Memuat data pengaturan...</p>
      </div>

      <div v-else>
        <!-- ==================== TAB 1: HERO SLIDER LANDING PAGE ==================== -->
        <div v-if="activeTab === 'hero'" class="space-y-6">
          <!-- Banner Information -->
          <div class="bg-gradient-to-r from-forest to-forest-800 text-white p-5 rounded-md shadow-md border border-gold/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1 max-w-2xl">
              <div class="inline-flex items-center space-x-2 px-2.5 py-0.5 rounded-full bg-gold/20 text-gold border border-gold/30 text-[10px] font-bold uppercase tracking-wider">
                <span>⚡ Live Dynamic Slider</span>
              </div>
              <h3 class="font-display text-lg text-white font-semibold">Hero Section Slider Landing Page</h3>
              <p class="text-xs text-sand/90 font-light leading-relaxed">
                Kelola daftar gambar slider yang akan berputar di bagian Hero Section landing page. Jika daftar slider ini disetting dan aktif, sistem akan menampilkan slider interaktif. Jika kosong atau tidak disetting, landing page akan kembali ke tampilan gambar default.
              </p>
            </div>

            <!-- Branch Filter Selector -->
            <div class="bg-white/10 backdrop-blur-md p-3 rounded border border-white/20 flex flex-col gap-1 min-w-[220px]">
              <label class="text-[10px] text-sand uppercase font-bold tracking-wider">Pilih Target Cabang Hotel:</label>
              <select
                v-model="selectedBranchId"
                @change="fetchHeroSliders"
                class="bg-forest border border-gold/40 text-gold text-xs font-bold rounded px-3 py-2 focus:outline-none cursor-pointer"
              >
                <option :value="null" class="bg-forest text-white">🌐 Semua Cabang (Global Default)</option>
                <option v-for="b in branchStore.branches" :key="b.id" :value="b.id" class="bg-forest text-white">
                  🏢 {{ b.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Hero Slider Action Header -->
          <div class="bg-white p-5 rounded-md border border-sand/30 shadow-sm flex items-center justify-between">
            <div>
              <h3 class="font-display text-lg text-forest font-semibold">Daftar Banner Slider</h3>
              <p class="text-xs text-taupe">Total: {{ heroSliders.length }} Slide ({{ heroSliders.filter(s => s.is_active !== false).length }} Aktif)</p>
            </div>

            <button
              @click="openSliderModal()"
              class="px-4 py-2 bg-gold text-forest text-xs font-bold uppercase tracking-wider rounded hover:bg-forest hover:text-white transition-all shadow-sm flex items-center space-x-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Tambah Banner Slider</span>
            </button>
          </div>

          <!-- Sliders Empty State -->
          <div v-if="heroSliders.length === 0" class="bg-white p-12 rounded-md border-2 border-dashed border-sand/60 text-center space-y-3 shadow-xs">
            <div class="w-16 h-16 rounded-full bg-sand/20 text-forest flex items-center justify-center mx-auto">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="space-y-1">
              <h4 class="font-display text-lg text-forest font-semibold">Belum Ada Slider Gambar Dikonfigurasi</h4>
              <p class="text-xs text-taupe max-w-md mx-auto">
                Landing page saat ini menampilkan background default bawaan sistem. Klik tombol "Tambah Banner Slider" untuk membuat slider kustom Anda.
              </p>
            </div>
            <button
              @click="openSliderModal()"
              class="px-5 py-2.5 bg-forest text-white text-xs font-bold uppercase tracking-wider rounded hover:bg-forest-800 shadow"
            >
              + Tambah Slider Pertama
            </button>
          </div>

          <!-- Sliders List Grid -->
          <div v-else class="space-y-4">
            <div
              v-for="(slide, index) in heroSliders"
              :key="slide.id || index"
              :class="[
                'bg-white rounded-md border shadow-sm overflow-hidden transition-all',
                slide.is_active !== false ? 'border-sand/40 hover:border-gold/60' : 'border-gray-200 opacity-60 bg-gray-50'
              ]"
            >
              <div class="p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <!-- Thumbnail Image & Ordering -->
                <div class="flex items-center space-x-4 w-full md:w-auto">
                  <!-- Order Controls -->
                  <div class="flex flex-col space-y-1">
                    <button
                      @click="moveSlideUp(index)"
                      :disabled="index === 0"
                      class="p-1 rounded text-taupe hover:text-forest hover:bg-sand/30 disabled:opacity-20 disabled:hover:bg-transparent"
                      title="Geser Ke Atas"
                    >
                      ▲
                    </button>
                    <span class="text-[10px] font-mono font-bold text-center text-taupe">#{{ index + 1 }}</span>
                    <button
                      @click="moveSlideDown(index)"
                      :disabled="index === heroSliders.length - 1"
                      class="p-1 rounded text-taupe hover:text-forest hover:bg-sand/30 disabled:opacity-20 disabled:hover:bg-transparent"
                      title="Geser Ke Bawah"
                    >
                      ▼
                    </button>
                  </div>

                  <!-- Image Preview -->
                  <div class="w-32 sm:w-40 h-20 sm:h-24 rounded border border-sand/30 bg-gray-100 relative overflow-hidden flex-shrink-0 group">
                    <img
                      :src="slide.image_url || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80'"
                      :alt="slide.title || 'Slider Banner'"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors"></div>
                  </div>

                  <!-- Slide Info -->
                  <div class="space-y-1 min-w-0 flex-1">
                    <div class="flex items-center space-x-2 flex-wrap gap-1">
                      <span v-if="slide.badge" class="px-2 py-0.5 text-[10px] font-bold bg-gold/20 text-amber-900 border border-gold/40 rounded uppercase tracking-wider">
                        {{ slide.badge }}
                      </span>
                      <span
                        :class="[
                          'px-2 py-0.5 text-[10px] font-bold rounded uppercase tracking-wider',
                          isTrue(slide.is_active) ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'
                        ]"
                      >
                        {{ isTrue(slide.is_active) ? '✓ Aktif' : '✕ Non-Aktif' }}
                      </span>
                    </div>

                    <h4 class="font-display text-base text-forest font-semibold truncate">
                      {{ slide.title || '(Tanpa Judul Utama - Menggunakan Teks Default)' }}
                    </h4>

                    <p class="text-xs text-taupe line-clamp-1">
                      {{ slide.subtitle || '(Menggunakan deskripsi default landing page)' }}
                    </p>

                    <div class="pt-1 flex items-center space-x-2 text-[11px] text-charcoal flex-wrap gap-2">
                      <span v-if="slide.button_primary_text" class="inline-flex items-center space-x-1 bg-ivory px-2 py-0.5 rounded border border-sand/30">
                        <span class="font-bold text-forest">Tombol 1:</span>
                        <span>{{ slide.button_primary_text }}</span>
                      </span>
                      <span v-if="slide.button_secondary_text" class="inline-flex items-center space-x-1 bg-ivory px-2 py-0.5 rounded border border-sand/30">
                        <span class="font-bold text-gold">Tombol 2:</span>
                        <span>{{ slide.button_secondary_text }}</span>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Slide Actions -->
                <div class="flex items-center space-x-2 self-end md:self-center">
                  <button
                    @click="toggleSlideActive(slide)"
                    :class="[
                      'px-3 py-1.5 text-xs font-semibold rounded border transition-colors cursor-pointer',
                      isTrue(slide.is_active)
                        ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
                        : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                    ]"
                  >
                    {{ isTrue(slide.is_active) ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                  <button
                    @click="openSliderModal(slide, index)"
                    class="px-3 py-1.5 bg-ivory text-forest border border-sand/40 hover:bg-sand/30 text-xs font-bold uppercase tracking-wider rounded transition-colors"
                  >
                    Edit
                  </button>
                  <button
                    @click="removeSlide(index)"
                    class="p-1.5 text-taupe hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                    title="Hapus Slide"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== TAB 2: REKENING BANK & QRIS ==================== -->
        <div v-else-if="activeTab === 'payment'" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
          <!-- LEFT COLUMN: BANK ACCOUNTS MANAGEMENT (7 Cols) -->
          <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-md border border-sand/30 shadow-sm overflow-hidden">
              <div class="p-5 border-b border-sand/20 flex items-center justify-between bg-ivory/50">
                <div>
                  <h3 class="font-display text-lg text-forest font-semibold">Daftar Rekening Bank</h3>
                  <p class="text-xs text-taupe">Nomor rekening yang ditampilkan di modal pembayaran transfer tamu.</p>
                </div>
                <button
                  @click="openBankModal()"
                  class="px-3 py-1.5 bg-gold text-forest text-xs font-bold uppercase tracking-wider rounded hover:bg-forest hover:text-white transition-all shadow-xs flex items-center space-x-1"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <span>Tambah Bank</span>
                </button>
              </div>

              <!-- Bank Accounts Table / List -->
              <div class="p-5 space-y-3">
                <div v-if="bankAccounts.length === 0" class="py-8 text-center text-xs text-taupe italic">
                  Belum ada rekening bank yang dikonfigurasi. Klik tombol "Tambah Bank" untuk menambahkan.
                </div>

                <div
                  v-for="(bank, index) in bankAccounts"
                  :key="index"
                  class="p-4 rounded-sm border border-sand/30 bg-ivory/30 flex items-center justify-between gap-4 hover:border-gold/50 transition-all"
                >
                  <div class="space-y-1">
                    <div class="flex items-center space-x-2">
                      <span class="font-bold text-forest text-sm">{{ bank.bank_name }}</span>
                      <span
                        :class="[
                          'px-2 py-0.5 text-[10px] font-bold rounded uppercase tracking-wider',
                          bank.is_active !== false ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'
                        ]"
                      >
                        {{ bank.is_active !== false ? 'Aktif' : 'Non-Aktif' }}
                      </span>
                    </div>
                    <div class="text-xs text-charcoal font-mono font-semibold">
                      {{ bank.account_number }}
                    </div>
                    <div class="text-[11px] text-taupe">
                      a/n <span class="text-charcoal font-medium">{{ bank.account_holder }}</span>
                    </div>
                  </div>

                  <div class="flex items-center space-x-2">
                    <button
                      @click="openBankModal(bank, index)"
                      class="p-1.5 text-taupe hover:text-forest hover:bg-sand/30 rounded transition-colors"
                      title="Edit Bank"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="removeBank(index)"
                      class="p-1.5 text-taupe hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                      title="Hapus Bank"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Petunjuk QRIS -->
            <div class="bg-white rounded-md border border-sand/30 p-5 space-y-3 shadow-sm">
              <h3 class="font-display text-base text-forest font-semibold">Petunjuk Pembayaran QRIS</h3>
              <p class="text-xs text-taupe">Catatan atau petunjuk singkat yang akan muncul di bawah gambar QRIS untuk tamu.</p>
              <textarea
                v-model="qrisNotes"
                rows="3"
                class="w-full px-3 py-2 text-xs bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-charcoal"
                placeholder="Contoh: Pindai kode QRIS menggunakan m-Banking atau e-Wallet..."
              ></textarea>
            </div>

            <!-- Nomor WhatsApp Concierge -->
            <div class="bg-white rounded-md border border-sand/30 p-5 space-y-3 shadow-sm">
              <h3 class="font-display text-base text-forest font-semibold">Nomor WhatsApp Concierge & Reservasi</h3>
              <p class="text-xs text-taupe">Nomor WhatsApp hotel yang digunakan untuk tombol ikon melayang (floating widget) dan konfirmasi transaksi tamu.</p>
              <div class="flex items-center space-x-2">
                <span class="px-3 py-2 bg-ivory border border-sand/40 rounded text-xs font-mono font-bold text-forest">+</span>
                <input
                  v-model="whatsappNumber"
                  type="text"
                  class="w-full px-3 py-2 text-xs bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-charcoal font-mono"
                  placeholder="Contoh: 6281234567890"
                />
              </div>
              <p class="text-[10px] text-taupe italic">Gunakan kode negara tanpa tanda + (contoh: 6281234567890 untuk Indonesia).</p>
            </div>
          </div>

          <!-- RIGHT COLUMN: QRIS IMAGE MANAGEMENT (5 Cols) -->
          <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-md border border-sand/30 shadow-sm overflow-hidden p-6 space-y-5">
              <div class="border-b border-sand/20 pb-3">
                <h3 class="font-display text-lg text-forest font-semibold">Gambar QRIS Pembayaran</h3>
                <p class="text-xs text-taupe mt-0.5">Unggah gambar kode QRIS resmi hotel Anda.</p>
              </div>

              <!-- Current QRIS Preview -->
              <div class="space-y-3">
                <span class="block text-xs font-bold text-charcoal uppercase tracking-wider">Pratinjau QRIS Saat Ini</span>

                <div v-if="qrisPreviewUrl || qrisUrl" class="relative group bg-ivory p-4 rounded border border-sand/40 flex flex-col items-center justify-center">
                  <img
                    :src="qrisPreviewUrl || qrisUrl"
                    alt="QRIS Code Preview"
                    class="w-56 h-56 object-contain rounded border border-sand/30 shadow-sm bg-white p-2"
                  />
                  <span v-if="qrisPreviewUrl" class="mt-2 text-[10px] text-amber-700 font-semibold bg-amber-50 px-2.5 py-0.5 rounded border border-amber-200">
                    ⚡ Gambar Baru (Belum Disimpan)
                  </span>
                  <span v-else class="mt-2 text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-200">
                    ✓ QRIS Aktif Berkas Terpasang
                  </span>
                </div>

                <div v-else class="p-8 bg-ivory/50 border-2 border-dashed border-sand/60 rounded text-center space-y-2">
                  <div class="w-12 h-12 rounded-full bg-sand/30 text-taupe flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </div>
                  <p class="text-xs font-semibold text-charcoal">Belum Ada Gambar QRIS</p>
                  <p class="text-[11px] text-taupe">Unggah gambar QRIS (PNG / JPG / WEBP) di bawah ini.</p>
                </div>
              </div>

              <!-- Upload File Input -->
              <div class="space-y-2 pt-2 border-t border-sand/20">
                <label class="block text-xs font-bold text-charcoal uppercase tracking-wider">
                  {{ (qrisUrl || qrisPreviewUrl) ? 'Perbarui / Ganti Gambar QRIS' : 'Unggah Gambar QRIS Baru' }}
                </label>
                <input
                  type="file"
                  accept="image/png, image/jpeg, image/jpg, image/webp"
                  @change="handleQrisFileChange"
                  class="block w-full text-xs text-taupe file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-forest file:text-white hover:file:bg-forest-800 cursor-pointer"
                />
                <p class="text-[10px] text-taupe italic">
                  Format yang didukung: PNG, JPG, JPEG, WEBP. Maksimal 5 MB.
                </p>
              </div>

              <!-- Action Buttons for QRIS -->
              <div class="pt-3 border-t border-sand/20 flex flex-col gap-2">
                <button
                  v-if="qrisUrl || qrisPreviewUrl"
                  type="button"
                  @click="confirmDeleteQris"
                  class="w-full py-2 px-3 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold uppercase tracking-wider rounded transition-colors flex items-center justify-center space-x-1.5"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <span>Hapus Gambar QRIS</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== TAB 3: MEDIA SOSIAL ==================== -->
        <div v-else-if="activeTab === 'social'" class="max-w-2xl mx-auto bg-white rounded-md border border-sand/30 shadow-sm overflow-hidden p-6 space-y-5">
          <div class="border-b border-sand/20 pb-3">
            <h3 class="font-display text-lg text-forest font-semibold">Tautan Media Sosial Hotel</h3>
            <p class="text-xs text-taupe mt-0.5">Tautan akun media sosial resmi yang akan ditampilkan pada bagian Footer Landing Page.</p>
          </div>

          <div class="space-y-4 text-xs">
            <!-- Instagram -->
            <div>
              <label class="block font-semibold text-charcoal mb-1 flex items-center space-x-1.5">
                <span class="text-pink-600 font-bold">📷 Instagram</span>
              </label>
              <input
                v-model="socialForm.instagram"
                type="text"
                class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs font-mono"
                placeholder="https://instagram.com/aurahotels"
              />
            </div>

            <!-- Twitter / X -->
            <div>
              <label class="block font-semibold text-charcoal mb-1 flex items-center space-x-1.5">
                <span class="text-gray-900 font-bold">𝕏 Twitter / X</span>
              </label>
              <input
                v-model="socialForm.twitter"
                type="text"
                class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs font-mono"
                placeholder="https://twitter.com/aurahotels"
              />
            </div>

            <!-- YouTube -->
            <div>
              <label class="block font-semibold text-charcoal mb-1 flex items-center space-x-1.5">
                <span class="text-red-600 font-bold">▶ YouTube</span>
              </label>
              <input
                v-model="socialForm.youtube"
                type="text"
                class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs font-mono"
                placeholder="https://youtube.com/@aurahotels"
              />
            </div>

            <!-- Facebook -->
            <div>
              <label class="block font-semibold text-charcoal mb-1 flex items-center space-x-1.5">
                <span class="text-blue-600 font-bold">📘 Facebook</span>
              </label>
              <input
                v-model="socialForm.facebook"
                type="text"
                class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs font-mono"
                placeholder="https://facebook.com/aurahotels"
              />
            </div>

            <!-- LinkedIn -->
            <div>
              <label class="block font-semibold text-charcoal mb-1 flex items-center space-x-1.5">
                <span class="text-sky-700 font-bold">💼 LinkedIn</span>
              </label>
              <input
                v-model="socialForm.linkedin"
                type="text"
                class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs font-mono"
                placeholder="https://linkedin.com/company/aurahotels"
              />
            </div>

            <!-- Threads -->
            <div>
              <label class="block font-semibold text-charcoal mb-1 flex items-center space-x-1.5">
                <span class="text-black font-bold">🧵 Threads</span>
              </label>
              <input
                v-model="socialForm.threads"
                type="text"
                class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs font-mono"
                placeholder="https://threads.net/@aurahotels"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== HERO SLIDER MODAL (ADD / EDIT) ==================== -->
    <div
      v-if="sliderModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs animate-fade-in overflow-y-auto"
      @click.self="sliderModalOpen = false"
    >
      <div class="bg-white rounded-md max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-sand/40 relative my-8">
        <div class="flex items-center justify-between border-b border-sand/20 pb-3">
          <div>
            <h3 class="font-display text-lg text-forest font-bold">
              {{ editingSliderIndex !== null ? 'Edit Banner Slider Hero' : 'Tambah Banner Slider Hero Baru' }}
            </h3>
            <p class="text-xs text-taupe">Konfigurasi gambar, judul, deskripsi, dan tombol aksi slider.</p>
          </div>
          <button @click="sliderModalOpen = false" class="text-taupe hover:text-charcoal p-1">✕</button>
        </div>

        <form @submit.prevent="saveSliderModal" class="space-y-4 text-xs">
          <!-- Image Upload / URL Selection -->
          <div class="space-y-2 bg-ivory/50 p-4 rounded border border-sand/40">
            <label class="block font-bold text-forest uppercase tracking-wider">1. Gambar Slider Hero *</label>

            <!-- Preview Image -->
            <div class="relative h-44 rounded border border-sand/40 bg-gray-900 overflow-hidden flex items-center justify-center">
              <img
                v-if="sliderForm.image_url"
                :src="sliderForm.image_url"
                alt="Slider Preview"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-center text-gray-400 space-y-1">
                <svg class="w-10 h-10 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-xs">Belum ada gambar terpilih</p>
              </div>

              <!-- Overlay Gradient Text Mockup -->
              <div v-if="sliderForm.image_url" class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/20 flex flex-col justify-end p-4 text-white">
                <span v-if="sliderForm.badge" class="text-[9px] text-gold uppercase font-bold tracking-widest mb-0.5">{{ sliderForm.badge }}</span>
                <h5 class="font-display text-sm font-semibold truncate">{{ sliderForm.title || 'Judul Slider Landing Page' }}</h5>
              </div>
            </div>

            <!-- Upload File or Enter URL Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              <div>
                <label class="block text-[11px] font-semibold text-charcoal mb-1">Unggah Berkas Gambar:</label>
                <input
                  type="file"
                  accept="image/png, image/jpeg, image/jpg, image/webp"
                  @change="handleSliderImageUpload"
                  :disabled="uploadingSliderImage"
                  class="block w-full text-xs text-taupe file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-forest file:text-white hover:file:bg-forest-800 cursor-pointer"
                />
                <span v-if="uploadingSliderImage" class="text-[10px] text-gold font-bold animate-pulse mt-1 block">
                  ⏳ Mengunggah gambar ke server...
                </span>
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-charcoal mb-1">Atau Gunakan Tautan URL Gambar:</label>
                <input
                  v-model="sliderForm.image_url"
                  type="url"
                  placeholder="https://images.unsplash.com/..."
                  class="w-full px-3 py-1.5 bg-white border border-sand/40 rounded focus:outline-none focus:border-forest text-xs"
                />
              </div>
            </div>
          </div>

          <!-- Content Fields -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold text-charcoal mb-1 uppercase tracking-wider">Sub-heading (Badge Upper Text)</label>
              <input
                v-model="sliderForm.badge"
                type="text"
                placeholder="Contoh: SELAMAT DATANG DI AURA"
                class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs"
              />
            </div>

            <div>
              <label class="block font-semibold text-charcoal mb-1 uppercase tracking-wider">Judul Utama (Headline Title)</label>
              <input
                v-model="sliderForm.title"
                type="text"
                placeholder="Contoh: Kemewahan & Kenyamanan Terbaik"
                class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs font-semibold"
              />
            </div>
          </div>

          <div>
            <label class="block font-semibold text-charcoal mb-1 uppercase tracking-wider">Deskripsi Singkat (Subtitle)</label>
            <textarea
              v-model="sliderForm.subtitle"
              rows="2"
              placeholder="Contoh: Rasakan sensasi menginap berkelas dengan layanan ramah dan pemandangan memukau..."
              class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs"
            ></textarea>
          </div>

          <!-- Action Buttons Configuration -->
          <div class="p-4 bg-ivory/30 border border-sand/30 rounded space-y-3">
            <h4 class="font-bold text-forest uppercase tracking-wider text-[11px]">2. Pengaturan Tombol Aksi (Call To Action)</h4>

            <!-- Primary Button -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-[11px] font-semibold text-charcoal mb-1">Teks Tombol 1 Utama:</label>
                <input
                  v-model="sliderForm.button_primary_text"
                  type="text"
                  placeholder="Contoh: Pesan Sekarang"
                  class="w-full px-2.5 py-1.5 bg-white border border-sand/40 rounded text-xs"
                />
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-charcoal mb-1">Aksi Tombol 1:</label>
                <select
                  v-model="sliderForm.button_primary_action"
                  class="w-full px-2.5 py-1.5 bg-white border border-sand/40 rounded text-xs"
                >
                  <option value="booking">🏨 Buka Modal Reservasi Kamar</option>
                  <option value="hall">🏛️ Buka Modal Reservasi Hall</option>
                  <option value="track">🔍 Buka Modal Cek Status</option>
                  <option value="custom">🔗 Tautan Khusus (Custom Link)</option>
                </select>
              </div>

              <div v-if="sliderForm.button_primary_action === 'custom'">
                <label class="block text-[11px] font-semibold text-charcoal mb-1">Target Link 1:</label>
                <input
                  v-model="sliderForm.button_primary_link"
                  type="text"
                  placeholder="#rooms atau https://..."
                  class="w-full px-2.5 py-1.5 bg-white border border-sand/40 rounded text-xs font-mono"
                />
              </div>
            </div>

            <!-- Secondary Button -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-sand/20">
              <div>
                <label class="block text-[11px] font-semibold text-charcoal mb-1">Teks Tombol 2 Sekunder:</label>
                <input
                  v-model="sliderForm.button_secondary_text"
                  type="text"
                  placeholder="Contoh: Pesan Hall"
                  class="w-full px-2.5 py-1.5 bg-white border border-sand/40 rounded text-xs"
                />
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-charcoal mb-1">Aksi Tombol 2:</label>
                <select
                  v-model="sliderForm.button_secondary_action"
                  class="w-full px-2.5 py-1.5 bg-white border border-sand/40 rounded text-xs"
                >
                  <option value="hall">🏛️ Buka Modal Reservasi Hall</option>
                  <option value="booking">🏨 Buka Modal Reservasi Kamar</option>
                  <option value="track">🔍 Buka Modal Cek Status</option>
                  <option value="custom">🔗 Tautan Khusus (Custom Link)</option>
                </select>
              </div>

              <div v-if="sliderForm.button_secondary_action === 'custom'">
                <label class="block text-[11px] font-semibold text-charcoal mb-1">Target Link 2:</label>
                <input
                  v-model="sliderForm.button_secondary_link"
                  type="text"
                  placeholder="#facilities atau https://..."
                  class="w-full px-2.5 py-1.5 bg-white border border-sand/40 rounded text-xs font-mono"
                />
              </div>
            </div>
          </div>

          <!-- Active Toggle -->
          <div class="flex items-center space-x-2 pt-1">
            <input
              id="sliderActiveToggle"
              type="checkbox"
              v-model="sliderForm.is_active"
              class="rounded text-forest focus:ring-forest"
            />
            <label for="sliderActiveToggle" class="text-xs text-charcoal font-medium cursor-pointer">
              Aktifkan Banner Slider ini pada Landing Page
            </label>
          </div>

          <!-- Form Actions -->
          <div class="pt-4 border-t border-sand/20 flex items-center justify-end space-x-3">
            <button
              type="button"
              @click="sliderModalOpen = false"
              class="px-4 py-2 border border-sand/40 text-taupe font-semibold rounded hover:bg-sand/20"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-forest text-white font-bold uppercase tracking-wider rounded hover:bg-forest-800 shadow"
            >
              Simpan Slide
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ==================== BANK ACCOUNT MODAL (ADD / EDIT) ==================== -->
    <div
      v-if="bankModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-fade-in"
      @click.self="bankModalOpen = false"
    >
      <div class="bg-white rounded-md max-w-md w-full p-6 space-y-4 shadow-2xl border border-sand/40 relative">
        <div class="flex items-center justify-between border-b border-sand/20 pb-3">
          <h3 class="font-display text-lg text-forest font-bold">
            {{ editingBankIndex !== null ? 'Edit Rekening Bank' : 'Tambah Rekening Bank' }}
          </h3>
          <button @click="bankModalOpen = false" class="text-taupe hover:text-charcoal p-1">✕</button>
        </div>

        <form @submit.prevent="saveBankModal" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-charcoal mb-1 uppercase tracking-wider">Nama Bank</label>
            <input
              v-model="bankForm.bank_name"
              type="text"
              required
              placeholder="Contoh: Bank BCA, Bank Mandiri"
              class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs"
            />
          </div>

          <div>
            <label class="block font-semibold text-charcoal mb-1 uppercase tracking-wider">Nomor Rekening</label>
            <input
              v-model="bankForm.account_number"
              type="text"
              required
              placeholder="Contoh: 8830-192-800"
              class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs font-mono"
            />
          </div>

          <div>
            <label class="block font-semibold text-charcoal mb-1 uppercase tracking-wider">Atas Nama (Account Holder)</label>
            <input
              v-model="bankForm.account_holder"
              type="text"
              required
              placeholder="Contoh: PT AURA Hospitality Indonesia"
              class="w-full px-3 py-2 bg-ivory/50 border border-sand/40 rounded focus:outline-none focus:border-forest text-xs"
            />
          </div>

          <div class="flex items-center space-x-2 pt-1">
            <input
              id="bankActiveToggle"
              type="checkbox"
              v-model="bankForm.is_active"
              class="rounded text-forest focus:ring-forest"
            />
            <label for="bankActiveToggle" class="text-xs text-charcoal font-medium cursor-pointer">
              Aktifkan Rekening ini pada Tampilan Tamu
            </label>
          </div>

          <div class="pt-3 border-t border-sand/20 flex items-center justify-end space-x-3">
            <button
              type="button"
              @click="bankModalOpen = false"
              class="px-4 py-2 border border-sand/40 text-taupe font-semibold rounded hover:bg-sand/20"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-forest text-white font-bold uppercase tracking-wider rounded hover:bg-forest-800 shadow"
            >
              Simpan Bank
            </button>
          </div>
        </form>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from '../api/axios'
import { settingApi } from '../api'
import { useBranchStore } from '../stores/branch'
import LayoutMain from '../components/LayoutMain.vue'

const branchStore = useBranchStore()

const activeTab = ref('hero') // 'hero' | 'payment' | 'social'
const loading = ref(false)
const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Branch filtering for Hero Sliders
const selectedBranchId = ref(null)

watch(selectedBranchId, () => {
  fetchHeroSliders()
})

// Hero Sliders State
const heroSliders = ref([])
const sliderModalOpen = ref(false)
const editingSliderIndex = ref(null)
const uploadingSliderImage = ref(false)
const sliderForm = ref({
  id: '',
  badge: '',
  title: '',
  subtitle: '',
  image_url: '',
  image_path: '',
  button_primary_text: 'Pesan Sekarang',
  button_primary_action: 'booking',
  button_primary_link: '',
  button_secondary_text: 'Pesan Hall',
  button_secondary_action: 'hall',
  button_secondary_link: '',
  is_active: true,
  sort_order: 1,
})

// Payment & QRIS Settings State
const bankAccounts = ref([])
const qrisNotes = ref('')
const whatsappNumber = ref('6281234567890')
const qrisUrl = ref(null)
const selectedQrisFile = ref(null)
const qrisPreviewUrl = ref(null)
const deleteQrisFlag = ref(false)

// Social Settings State
const socialForm = ref({
  instagram: '',
  twitter: '',
  youtube: '',
  facebook: '',
  linkedin: '',
  threads: '',
})

// Bank Modal State
const bankModalOpen = ref(false)
const editingBankIndex = ref(null)
const bankForm = ref({
  bank_name: '',
  account_number: '',
  account_holder: '',
  is_active: true,
})

// Fetch All Settings
const fetchAllSettings = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const [payRes, socialRes] = await Promise.all([
      axios.get('/api/settings/payment'),
      axios.get('/api/settings/social'),
    ])
    
    // Payment Settings
    const data = payRes.data?.data || {}
    bankAccounts.value = data.bank_accounts || []
    qrisNotes.value = data.qris_notes || ''
    whatsappNumber.value = data.whatsapp_number || '6281234567890'
    qrisUrl.value = data.qris_url || null
    selectedQrisFile.value = null
    qrisPreviewUrl.value = null
    deleteQrisFlag.value = false

    // Social Settings
    const socialData = socialRes.data?.data || {}
    socialForm.value = {
      instagram: socialData.instagram || '',
      twitter: socialData.twitter || '',
      youtube: socialData.youtube || '',
      facebook: socialData.facebook || '',
      linkedin: socialData.linkedin || '',
      threads: socialData.threads || '',
    }

    // Hero Sliders Settings
    await fetchHeroSliders()
  } catch (err) {
    console.error('Failed to fetch settings:', err)
    errorMessage.value = 'Gagal memuat pengaturan. Pastikan koneksi server baik.'
  } finally {
    loading.value = false
  }
}

// Fetch Hero Sliders by Selected Branch
const fetchHeroSliders = async () => {
  try {
    const res = await settingApi.getHeroSliders({ branch_id: selectedBranchId.value })
    heroSliders.value = res.data || []
  } catch (err) {
    console.error('Failed to fetch hero sliders:', err)
  }
}

// --- HERO SLIDER MANAGEMENT HANDLERS ---
const openSliderModal = (slide = null, index = null) => {
  if (slide && index !== null) {
    editingSliderIndex.value = index
    sliderForm.value = JSON.parse(JSON.stringify(slide))
  } else {
    editingSliderIndex.value = null
    sliderForm.value = {
      id: 'slide_' + Date.now() + '_' + (heroSliders.value.length + 1),
      badge: 'HOTEL & RESORT MEWAH',
      title: 'Kemewahan & Kenyamanan Terbaik',
      subtitle: 'Pengalaman menginap tak terlupakan di AURA Hotel & Resorts.',
      image_url: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=2000&q=85',
      image_path: '',
      button_primary_text: 'Pesan Sekarang',
      button_primary_action: 'booking',
      button_primary_link: '',
      button_secondary_text: 'Pesan Hall',
      button_secondary_action: 'hall',
      button_secondary_link: '',
      is_active: true,
      sort_order: heroSliders.value.length + 1,
    }
  }
  sliderModalOpen.value = true
}

const handleSliderImageUpload = async (e) => {
  const file = e.target.files[0]
  if (!file) return

  if (file.size > 10 * 1024 * 1024) {
    alert('Ukuran berkas gambar terlalu besar (maksimal 10MB).')
    return
  }

  uploadingSliderImage.value = true
  try {
    const res = await settingApi.uploadHeroSliderImage(file)
    if (res.image_url) {
      sliderForm.value.image_url = res.image_url
      sliderForm.value.image_path = res.image_path
    }
  } catch (err) {
    console.error('Upload slider image failed:', err)
    alert('Gagal mengunggah gambar. Pastikan format berkas sesuai.')
  } finally {
    uploadingSliderImage.value = false
  }
}

const isTrue = (val) => val === true || val === 'true' || val === 1 || val === '1'

const toggleSlideActive = async (slide) => {
  slide.is_active = !isTrue(slide.is_active)
  await saveCurrentTabSettings()
}

const saveSliderModal = async () => {
  if (!sliderForm.value.image_url) {
    alert('Harap masukkan URL atau unggah gambar slider.')
    return
  }

  if (editingSliderIndex.value !== null) {
    heroSliders.value[editingSliderIndex.value] = { ...sliderForm.value }
  } else {
    heroSliders.value.push({ ...sliderForm.value })
  }
  sliderModalOpen.value = false
  await saveCurrentTabSettings()
}

const removeSlide = async (index) => {
  if (confirm('Hapus slide banner ini?')) {
    heroSliders.value.splice(index, 1)
    await saveCurrentTabSettings()
  }
}

const moveSlideUp = async (index) => {
  if (index > 0) {
    const temp = heroSliders.value[index]
    heroSliders.value[index] = heroSliders.value[index - 1]
    heroSliders.value[index - 1] = temp
    await saveCurrentTabSettings()
  }
}

const moveSlideDown = async (index) => {
  if (index < heroSliders.value.length - 1) {
    const temp = heroSliders.value[index]
    heroSliders.value[index] = heroSliders.value[index + 1]
    heroSliders.value[index + 1] = temp
    await saveCurrentTabSettings()
  }
}

// --- QRIS & BANK HANDLERS ---
const handleQrisFileChange = (e) => {
  const file = e.target.files[0]
  if (!file) return

  if (file.size > 5 * 1024 * 1024) {
    errorMessage.value = 'Ukuran berkas QRIS terlalu besar (maksimal 5MB).'
    e.target.value = ''
    return
  }

  selectedQrisFile.value = file
  deleteQrisFlag.value = false
  qrisPreviewUrl.value = URL.createObjectURL(file)
}

const confirmDeleteQris = () => {
  if (confirm('Apakah Anda yakin ingin menghapus gambar QRIS? Berkas gambar di server akan dihapus permanen saat disimpan.')) {
    selectedQrisFile.value = null
    qrisPreviewUrl.value = null
    qrisUrl.value = null
    deleteQrisFlag.value = true
  }
}

const openBankModal = (bank = null, index = null) => {
  if (bank && index !== null) {
    editingBankIndex.value = index
    bankForm.value = { ...bank }
  } else {
    editingBankIndex.value = null
    bankForm.value = {
      bank_name: '',
      account_number: '',
      account_holder: '',
      is_active: true,
    }
  }
  bankModalOpen.value = true
}

const saveBankModal = () => {
  if (editingBankIndex.value !== null) {
    bankAccounts.value[editingBankIndex.value] = { ...bankForm.value }
  } else {
    bankAccounts.value.push({ ...bankForm.value })
  }
  bankModalOpen.value = false
}

const removeBank = (index) => {
  if (confirm('Hapus nomor rekening bank ini?')) {
    bankAccounts.value.splice(index, 1)
  }
}

// --- SAVE CURRENT TAB SETTINGS ---
const saveCurrentTabSettings = async () => {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    if (activeTab.value === 'hero') {
      // Re-assign sort orders
      heroSliders.value.forEach((slide, idx) => {
        slide.sort_order = idx + 1
      })

      const res = await settingApi.updateHeroSliders({
        sliders: heroSliders.value,
        branch_id: selectedBranchId.value,
      })

      if (res.data) {
        heroSliders.value = res.data
      }
      successMessage.value = 'Pengaturan Hero Slider Landing Page berhasil disimpan!'
    } else if (activeTab.value === 'payment') {
      const formData = new FormData()
      formData.append('bank_accounts', JSON.stringify(bankAccounts.value))
      formData.append('qris_notes', qrisNotes.value)
      formData.append('whatsapp_number', whatsappNumber.value)

      if (deleteQrisFlag.value) {
        formData.append('delete_qris', '1')
      } else if (selectedQrisFile.value) {
        formData.append('qris_image', selectedQrisFile.value)
      }

      const payRes = await axios.post('/api/settings/payment', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })

      const updated = payRes.data?.data || {}
      bankAccounts.value = updated.bank_accounts || []
      qrisNotes.value = updated.qris_notes || ''
      whatsappNumber.value = updated.whatsapp_number || '6281234567890'
      qrisUrl.value = updated.qris_url || null
      selectedQrisFile.value = null
      qrisPreviewUrl.value = null
      deleteQrisFlag.value = false

      successMessage.value = 'Pengaturan Rekening Bank & QRIS berhasil diperbarui!'
    } else if (activeTab.value === 'social') {
      const socialRes = await axios.post('/api/settings/social', socialForm.value)
      const updatedSocial = socialRes.data?.data || {}
      socialForm.value = {
        instagram: updatedSocial.instagram || '',
        twitter: updatedSocial.twitter || '',
        youtube: updatedSocial.youtube || '',
        facebook: updatedSocial.facebook || '',
        linkedin: updatedSocial.linkedin || '',
        threads: updatedSocial.threads || '',
      }
      successMessage.value = 'Pengaturan Tautan Media Sosial berhasil diperbarui!'
    }
  } catch (err) {
    console.error('Failed to save settings:', err)
    errorMessage.value = err.response?.data?.message || 'Gagal menyimpan perubahan pengaturan.'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await branchStore.fetchAdminBranches()
  if (branchStore.activeBranchId && selectedBranchId.value === null) {
    selectedBranchId.value = branchStore.activeBranchId
  }
  await fetchAllSettings()
})
</script>
