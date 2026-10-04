<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-serif font-bold text-forest">Manajemen Cabang Hotel</h1>
          <p class="text-sm text-taupe mt-1">Kelola seluruh lokasi dan unit cabang AURA Hotel Group</p>
        </div>
        <button
          @click="openAddModal"
          class="inline-flex items-center px-4 py-2.5 bg-forest hover:bg-forest/90 text-gold font-semibold rounded-lg text-sm transition-all shadow-sm cursor-pointer"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Tambah Cabang Baru
        </button>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl border border-sand/40 shadow-xs flex items-center space-x-4">
          <div class="w-12 h-12 rounded-lg bg-forest/10 text-forest flex items-center justify-center font-bold text-xl">
            🏢
          </div>
          <div>
            <p class="text-xs text-taupe font-semibold uppercase tracking-wider">Total Cabang</p>
            <p class="text-2xl font-bold text-forest mt-0.5">{{ branches.length }}</p>
          </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-sand/40 shadow-xs flex items-center space-x-4">
          <div class="w-12 h-12 rounded-lg bg-gold/10 text-gold flex items-center justify-center font-bold text-xl">
            🛏️
          </div>
          <div>
            <p class="text-xs text-taupe font-semibold uppercase tracking-wider">Total Kamar Seluruh Cabang</p>
            <p class="text-2xl font-bold text-forest mt-0.5">{{ totalRoomsCount }}</p>
          </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-sand/40 shadow-xs flex items-center space-x-4">
          <div class="w-12 h-12 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-xl">
            🏛️
          </div>
          <div>
            <p class="text-xs text-taupe font-semibold uppercase tracking-wider">Total Hall Seluruh Cabang</p>
            <p class="text-2xl font-bold text-forest mt-0.5">{{ totalHallsCount }}</p>
          </div>
        </div>
      </div>

      <!-- Branch Cards List -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-forest border-t-transparent"></div>
        <p class="text-taupe text-sm mt-3">Memuat data cabang hotel...</p>
      </div>

      <div v-else-if="branches.length === 0" class="bg-white rounded-xl p-8 text-center border border-sand/40">
        <p class="text-taupe">Belum ada cabang hotel yang terdaftar.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="branch in branches"
          :key="branch.id"
          class="bg-white rounded-xl border border-sand/40 overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col"
        >
          <div class="relative h-44 bg-sand/30 overflow-hidden">
            <img
              :src="branch.image || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'"
              :alt="branch.name"
              class="w-full h-full object-cover"
            />
            <div class="absolute top-3 right-3">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-bold tracking-wide shadow-xs',
                  branch.is_active ? 'bg-emerald-500 text-white' : 'bg-gray-400 text-white'
                ]"
              >
                {{ branch.is_active ? 'Aktif' : 'Non-Aktif' }}
              </span>
            </div>
          </div>

          <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gold uppercase tracking-wider">{{ branch.city || 'Indonesia' }}</span>
                <span class="text-xs text-taupe font-mono">Slug: {{ branch.slug }}</span>
              </div>
              <h3 class="text-lg font-serif font-bold text-forest mt-1">{{ branch.name }}</h3>
              <p class="text-xs text-taupe line-clamp-2 mt-2 leading-relaxed">{{ branch.description || 'Tidak ada deskripsi' }}</p>

              <div class="mt-4 pt-4 border-t border-sand/30 space-y-2 text-xs text-charcoal">
                <div class="flex items-center text-taupe">
                  <svg class="w-4 h-4 mr-2 text-forest flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="truncate">{{ branch.address || '-' }}</span>
                </div>
                <div class="flex items-center text-taupe">
                  <svg class="w-4 h-4 mr-2 text-forest flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h32a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18" />
                  </svg>
                  <span>📞 {{ branch.phone || '-' }} | ✉️ {{ branch.email || '-' }}</span>
                </div>
              </div>

              <!-- Metrics -->
              <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
                <div class="bg-ivory p-2 rounded-lg border border-sand/30">
                  <span class="text-taupe block font-medium">Kamar</span>
                  <span class="text-forest font-bold text-sm">{{ branch.rooms_count || 0 }} Unit</span>
                </div>
                <div class="bg-ivory p-2 rounded-lg border border-sand/30">
                  <span class="text-taupe block font-medium">Hall</span>
                  <span class="text-forest font-bold text-sm">{{ branch.halls_count || 0 }} Unit</span>
                </div>
              </div>
            </div>

            <div class="mt-5 pt-4 border-t border-sand/30 flex items-center justify-between gap-2">
              <button
                @click="switchToBranch(branch)"
                class="flex-1 px-3 py-1.5 bg-sand/30 hover:bg-forest hover:text-white text-forest text-xs font-semibold rounded-md transition-colors"
              >
                Pilih Cabang Ini
              </button>
              <button
                @click="openEditModal(branch)"
                class="px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 text-xs font-semibold rounded-md transition-colors"
              >
                Edit
              </button>
              <button
                @click="deleteBranch(branch)"
                class="px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold rounded-md transition-colors"
              >
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4 my-8">
          <div class="flex items-center justify-between border-b border-sand/30 pb-3">
            <h3 class="text-lg font-serif font-bold text-forest">
              {{ isEditing ? 'Edit Cabang Hotel' : 'Tambah Cabang Hotel Baru' }}
            </h3>
            <button @click="showModal = false" class="text-taupe hover:text-charcoal text-xl font-bold">&times;</button>
          </div>

          <form @submit.prevent="saveBranch" class="space-y-3 text-sm">
            <div>
              <label class="block text-xs font-semibold text-charcoal mb-1">Nama Cabang Hotel *</label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="misal: Grand Hotel Merdeka Bali"
                class="w-full px-3 py-2 border border-sand/60 rounded-md focus:ring-2 focus:ring-forest/30 focus:outline-none"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-charcoal mb-1">Kota / Lokasi *</label>
                <input
                  v-model="form.city"
                  type="text"
                  required
                  placeholder="misal: Bali"
                  class="w-full px-3 py-2 border border-sand/60 rounded-md focus:ring-2 focus:ring-forest/30 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-charcoal mb-1">Slug URL</label>
                <input
                  v-model="form.slug"
                  type="text"
                  placeholder="bali (otomatis jika kosong)"
                  class="w-full px-3 py-2 border border-sand/60 rounded-md focus:ring-2 focus:ring-forest/30 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-charcoal mb-1">Alamat Lengkap</label>
              <textarea
                v-model="form.address"
                rows="2"
                placeholder="Jl. Pantai Kuta No. 88, Badung"
                class="w-full px-3 py-2 border border-sand/60 rounded-md focus:ring-2 focus:ring-forest/30 focus:outline-none"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-charcoal mb-1">Nomor Telepon</label>
                <input
                  v-model="form.phone"
                  type="text"
                  placeholder="0361-7770456"
                  class="w-full px-3 py-2 border border-sand/60 rounded-md focus:ring-2 focus:ring-forest/30 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-charcoal mb-1">Email Resmi</label>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="bali@hotel.com"
                  class="w-full px-3 py-2 border border-sand/60 rounded-md focus:ring-2 focus:ring-forest/30 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-charcoal mb-1">URL Foto Cabang</label>
              <input
                v-model="form.image"
                type="url"
                placeholder="https://..."
                class="w-full px-3 py-2 border border-sand/60 rounded-md focus:ring-2 focus:ring-forest/30 focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-charcoal mb-1">Deskripsi Singkat</label>
              <textarea
                v-model="form.description"
                rows="2"
                placeholder="Fasilitas dan keunggulan cabang hotel ini..."
                class="w-full px-3 py-2 border border-sand/60 rounded-md focus:ring-2 focus:ring-forest/30 focus:outline-none"
              ></textarea>
            </div>

            <div class="flex items-center space-x-2 pt-2">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded text-forest focus:ring-forest" />
              <label for="is_active" class="text-xs font-semibold text-charcoal cursor-pointer">Cabang Aktif & Siap Menerima Reservasi</label>
            </div>

            <div class="pt-4 border-t border-sand/30 flex justify-end space-x-2">
              <button
                type="button"
                @click="showModal = false"
                class="px-4 py-2 bg-sand/30 hover:bg-sand/50 text-charcoal text-xs font-semibold rounded-md"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-4 py-2 bg-forest hover:bg-forest/90 text-gold text-xs font-semibold rounded-md shadow-xs"
              >
                {{ saving ? 'Menyimpan...' : 'Simpan Cabang' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import LayoutMain from '../components/LayoutMain.vue'
import { branchApi } from '../api'
import { useBranchStore } from '../stores/branch'

const branchStore = useBranchStore()
const branches = ref([])
const loading = ref(true)
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const saving = ref(false)

const form = ref({
  name: '',
  slug: '',
  city: '',
  address: '',
  phone: '',
  email: '',
  image: '',
  description: '',
  is_active: true,
})

const totalRoomsCount = computed(() => {
  return branches.value.reduce((acc, b) => acc + (b.rooms_count || 0), 0)
})

const totalHallsCount = computed(() => {
  return branches.value.reduce((acc, b) => acc + (b.halls_count || 0), 0)
})

async function fetchBranches() {
  loading.value = true
  try {
    const res = await branchApi.getBranches()
    branches.value = res.data || []
  } catch (err) {
    console.error('Failed to load branches:', err)
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  isEditing.value = false
  editingId.value = null
  form.value = {
    name: '',
    slug: '',
    city: '',
    address: '',
    phone: '',
    email: '',
    image: '',
    description: '',
    is_active: true,
  }
  showModal.value = true
}

function openEditModal(branch) {
  isEditing.value = true
  editingId.value = branch.id
  form.value = {
    name: branch.name,
    slug: branch.slug,
    city: branch.city || '',
    address: branch.address || '',
    phone: branch.phone || '',
    email: branch.email || '',
    image: branch.image || '',
    description: branch.description || '',
    is_active: branch.is_active,
  }
  showModal.value = true
}

async function saveBranch() {
  saving.value = true
  try {
    if (isEditing.value) {
      await branchApi.updateBranch(editingId.value, form.value)
    } else {
      await branchApi.createBranch(form.value)
    }
    showModal.value = false
    await fetchBranches()
    await branchStore.fetchAdminBranches()
  } catch (err) {
    alert('Gagal menyimpan cabang hotel: ' + (err.response?.data?.message || err.message))
  } finally {
    saving.value = false
  }
}

async function deleteBranch(branch) {
  if (confirm(`Apakah Anda yakin ingin menghapus cabang hotel '${branch.name}'?`)) {
    try {
      await branchApi.deleteBranch(branch.id)
      await fetchBranches()
      await branchStore.fetchAdminBranches()
    } catch (err) {
      alert('Gagal menghapus cabang: ' + (err.response?.data?.message || err.message))
    }
  }
}

function switchToBranch(branch) {
  branchStore.selectBranch(branch)
  window.location.reload()
}

onMounted(() => {
  fetchBranches()
})
</script>
