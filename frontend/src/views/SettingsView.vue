<template>
  <LayoutMain>
    <div class="space-y-6 max-w-6xl mx-auto pb-12">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-md border border-sand/30 shadow-sm">
        <div>
          <span class="text-xs uppercase tracking-[0.25em] text-gold font-bold">KONFIGURASI SISTEM</span>
          <h1 class="font-display text-2xl sm:text-3xl text-forest font-normal mt-1">
            Pengaturan Pembayaran & QRIS
          </h1>
          <p class="text-xs sm:text-sm text-taupe font-light mt-1">
            Kelola daftar nomor rekening bank dan berkas gambar kode QRIS yang ditampilkan kepada tamu saat pemesanan.
          </p>
        </div>

        <button
          @click="saveSettings"
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

      <!-- Alert Notification -->
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
      <div v-if="loading" class="p-12 text-center text-taupe space-y-2">
        <div class="w-8 h-8 border-3 border-forest border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="text-xs font-medium">Memuat pengaturan pembayaran...</p>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8">
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

          <!-- SOCIAL MEDIA LINKS CARD -->
          <div class="bg-white rounded-md border border-sand/30 shadow-sm overflow-hidden p-6 space-y-4">
            <div class="border-b border-sand/20 pb-3">
              <h3 class="font-display text-lg text-forest font-semibold">Media Sosial Hotel</h3>
              <p class="text-xs text-taupe mt-0.5">Tautan akun media sosial yang akan ditampilkan pada Footer halaman utama (Landing Page).</p>
            </div>

            <div class="space-y-3 text-xs">
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
    </div>

    <!-- BANK ACCOUNT MODAL (ADD / EDIT) -->
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
import { ref, onMounted } from 'vue'
import LayoutMain from '../components/LayoutMain.vue'
import axios from 'axios'

const loading = ref(true)
const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const bankAccounts = ref([])
const qrisNotes = ref('')
const whatsappNumber = ref('6281234567890')
const qrisUrl = ref(null)
const selectedQrisFile = ref(null)
const qrisPreviewUrl = ref(null)
const deleteQrisFlag = ref(false)

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

const fetchSettings = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const [payRes, socialRes] = await Promise.all([
      axios.get('/api/settings/payment'),
      axios.get('/api/settings/social'),
    ])
    const data = payRes.data?.data || {}
    bankAccounts.value = data.bank_accounts || []
    qrisNotes.value = data.qris_notes || ''
    whatsappNumber.value = data.whatsapp_number || '6281234567890'
    qrisUrl.value = data.qris_url || null
    selectedQrisFile.value = null
    qrisPreviewUrl.value = null
    deleteQrisFlag.value = false

    const socialData = socialRes.data?.data || {}
    socialForm.value = {
      instagram: socialData.instagram || '',
      twitter: socialData.twitter || '',
      youtube: socialData.youtube || '',
      facebook: socialData.facebook || '',
      linkedin: socialData.linkedin || '',
      threads: socialData.threads || '',
    }
  } catch (err) {
    console.error('Failed to fetch settings:', err)
    errorMessage.value = 'Gagal memuat pengaturan. Pastikan koneksi server baik.'
  } finally {
    loading.value = false
  }
}

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

// Bank Modal Handlers
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

// Save All Settings
const saveSettings = async () => {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const formData = new FormData()
    formData.append('bank_accounts', JSON.stringify(bankAccounts.value))
    formData.append('qris_notes', qrisNotes.value)
    formData.append('whatsapp_number', whatsappNumber.value)

    if (deleteQrisFlag.value) {
      formData.append('delete_qris', '1')
    } else if (selectedQrisFile.value) {
      formData.append('qris_image', selectedQrisFile.value)
    }

    const [payRes, socialRes] = await Promise.all([
      axios.post('/api/settings/payment', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }),
      axios.post('/api/settings/social', socialForm.value),
    ])

    successMessage.value = 'Pengaturan sistem & media sosial berhasil diperbarui!'
    
    // Refresh settings view data
    const updated = payRes.data?.data || {}
    bankAccounts.value = updated.bank_accounts || []
    qrisNotes.value = updated.qris_notes || ''
    whatsappNumber.value = updated.whatsapp_number || '6281234567890'
    qrisUrl.value = updated.qris_url || null
    selectedQrisFile.value = null
    qrisPreviewUrl.value = null
    deleteQrisFlag.value = false

    const updatedSocial = socialRes.data?.data || {}
    socialForm.value = {
      instagram: updatedSocial.instagram || '',
      twitter: updatedSocial.twitter || '',
      youtube: updatedSocial.youtube || '',
      facebook: updatedSocial.facebook || '',
      linkedin: updatedSocial.linkedin || '',
      threads: updatedSocial.threads || '',
    }
  } catch (err) {
    console.error('Failed to save settings:', err)
    errorMessage.value = err.response?.data?.message || 'Gagal menyimpan pengaturan.'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>
