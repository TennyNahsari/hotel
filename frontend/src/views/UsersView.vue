<template>
  <LayoutMain>
    <div class="space-y-4 md:space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">Manajemen User & Hak Akses</h1>
          <p class="text-gray-600 mt-1 text-xs sm:text-sm md:text-base">Kelola akun pengguna, peran, serta hak akses wilayah cabang hotel</p>
        </div>
        <button
          @click="openAddModal"
          class="w-full sm:w-auto px-4 py-2 text-sm md:text-base bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap flex items-center justify-center gap-2 shadow-sm"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
          <span>+ Tambah User Baru</span>
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-3 md:p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
          <!-- Search -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Cari User</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nama, email, atau telp..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>

          <!-- Role Filter -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Peran / Role</label>
            <select
              v-model="filters.role_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">Semua Peran</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.display_name || role.name }}
              </option>
            </select>
          </div>

          <!-- Branch Filter -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Hak Akses Cabang</label>
            <select
              v-model="filters.hotel_branch_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">Semua Cabang & Global</option>
              <option value="global">🌐 Global (Semua Cabang)</option>
              <option v-for="b in branchStore.branches" :key="b.id" :value="b.id">
                🏢 {{ b.name }}
              </option>
            </select>
          </div>

          <!-- Active Filter -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status Akun</label>
            <select
              v-model="filters.is_active"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">Semua Status</option>
              <option value="true">Aktif</option>
              <option value="false">Non-aktif</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users List Table / Mobile Cards -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">Memuat daftar user...</p>
      </div>

      <div v-else-if="users.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">Tidak ada data user yang sesuai dengan filter.</p>
      </div>

      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Mobile View -->
        <div class="block md:hidden">
          <div v-for="u in users" :key="u.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
            <div class="space-y-3">
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-forest text-gold font-bold text-sm flex items-center justify-center border border-gold/40 flex-shrink-0">
                    {{ getUserInitials(u.name) }}
                  </div>
                  <div class="min-w-0">
                    <div class="font-semibold text-gray-900 truncate">{{ u.name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ u.email }}</div>
                  </div>
                </div>
                <span
                  :class="u.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="px-2 py-0.5 text-xs font-semibold rounded-full flex-shrink-0"
                >
                  {{ u.is_active ? 'Aktif' : 'Non-aktif' }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-2 text-xs">
                <div>
                  <span class="text-gray-500 block">Peran / Role:</span>
                  <span class="font-medium text-gray-900">{{ u.role?.display_name || u.role?.name || '-' }}</span>
                </div>
                <div>
                  <span class="text-gray-500 block">Cabang Hotel:</span>
                  <span v-if="!u.hotel_branch_id" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-900 border border-amber-200">
                    🌐 Semua Cabang (Global)
                  </span>
                  <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-900 border border-emerald-200">
                    🏢 {{ u.hotel_branch?.name || ('Cabang ID #' + u.hotel_branch_id) }}
                  </span>
                </div>
              </div>

              <div class="flex gap-2 pt-2 border-t border-gray-100">
                <button
                  @click="openEditModal(u)"
                  class="flex-1 text-xs px-3 py-1.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition-colors font-medium"
                >
                  Edit User
                </button>
                <button
                  @click="confirmDelete(u)"
                  class="flex-1 text-xs px-3 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100 transition-colors font-medium"
                  :disabled="u.id === authStore.user?.id"
                >
                  Hapus
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop View Table -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran / Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hak Akses Cabang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-forest text-gold font-bold text-xs flex items-center justify-center border border-gold/40 flex-shrink-0">
                      {{ getUserInitials(u.name) }}
                    </div>
                    <div>
                      <div class="text-sm font-semibold text-gray-900">{{ u.name }}</div>
                      <div class="text-xs text-gray-500 font-mono">ID: #{{ u.id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ u.email }}</div>
                  <div class="text-xs text-gray-500">{{ u.phone || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ u.role?.display_name || u.role?.name || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="!u.hotel_branch_id" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-900 border border-amber-300">
                    <span>🌐</span>
                    <span>Semua Cabang (Global)</span>
                  </span>
                  <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-900 border border-emerald-300">
                    <span>🏢</span>
                    <span>{{ u.hotel_branch?.name || ('Cabang #' + u.hotel_branch_id) }}</span>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="u.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="px-2.5 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ u.is_active ? 'Aktif' : 'Non-aktif' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="openEditModal(u)"
                    class="text-blue-600 hover:text-blue-900 font-semibold mr-4"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(u)"
                    :disabled="u.id === authStore.user?.id"
                    class="text-red-600 hover:text-red-900 font-semibold disabled:opacity-40 disabled:cursor-not-allowed"
                    :title="u.id === authStore.user?.id ? 'Tidak dapat menghapus akun sendiri' : 'Hapus user'"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add / Edit User Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-lg w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? 'Edit User' : 'Tambah User Baru' }}
        </h2>

        <form @submit.prevent="saveUser" class="space-y-4">
          <!-- Nama Lengkap -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
              placeholder="Contoh: Budi Santoso"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email *</label>
            <input
              v-model="formData.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
              placeholder="budi@hotel.com"
            />
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon / WA</label>
            <input
              v-model="formData.phone"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
              placeholder="081234567890"
            />
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Password {{ isEditing ? '(Opsional)' : '*' }}
            </label>
            <input
              v-model="formData.password"
              type="password"
              :required="!isEditing"
              minlength="6"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
              :placeholder="isEditing ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter'"
            />
          </div>

          <!-- Peran / Role -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Peran / Role *</label>
            <select
              v-model="formData.role_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="" disabled>-- Pilih Peran --</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.display_name || role.name }} ({{ role.description }})
              </option>
            </select>
          </div>

          <!-- Hak Akses Cabang -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Hak Akses Cabang Hotel *</label>
            <select
              v-model="formData.hotel_branch_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm font-medium"
            >
              <option :value="null">🌐 Semua Cabang (Global / Akses Penuh)</option>
              <option v-for="b in branchStore.branches" :key="b.id" :value="b.id">
                🏢 {{ b.name }} ({{ b.city }})
              </option>
            </select>
            <p class="text-xs text-gray-500 pt-1 leading-relaxed">
              <span v-if="formData.hotel_branch_id === null" class="text-amber-700 font-semibold">
                🌐 Akses Global: User dapat melihat & mengelola data dari seluruh cabang hotel.
              </span>
              <span v-else class="text-emerald-700 font-semibold">
                🔒 Akses Cabang Khusus: User HANYA dapat melihat & mengelola data cabang ini. Pilihan cabang di dashboard otomatis dikunci.
              </span>
            </p>
          </div>

          <!-- Status Active -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
            <div class="flex items-center gap-4 pt-1">
              <label class="inline-flex items-center cursor-pointer">
                <input type="radio" v-model="formData.is_active" :value="true" class="text-blue-600 focus:ring-blue-500" />
                <span class="ml-2 text-sm text-gray-700">Aktif</span>
              </label>
              <label class="inline-flex items-center cursor-pointer">
                <input type="radio" v-model="formData.is_active" :value="false" class="text-red-600 focus:ring-red-500" />
                <span class="ml-2 text-sm text-gray-700">Non-aktif</span>
              </label>
            </div>
          </div>

          <!-- Error Alert -->
          <div v-if="modalError" class="p-3 bg-red-50 text-red-700 rounded-lg text-sm">
            {{ modalError }}
          </div>

          <!-- Submit Actions -->
          <div class="flex gap-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold disabled:opacity-50"
            >
              {{ saving ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Buat User Baru') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="cancelDelete"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">Hapus User</h2>
        <p class="text-sm text-gray-600 mb-6">
          Apakah Anda yakin ingin menghapus user <strong>{{ userToDelete?.name }}</strong> ({{ userToDelete?.email }})? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="flex gap-3">
          <button
            type="button"
            @click="cancelDelete"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
          >
            Batal
          </button>
          <button
            @click="deleteUser"
            :disabled="deleting"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold disabled:opacity-50"
          >
            {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import LayoutMain from '@/components/LayoutMain.vue'
import { userApi } from '@/api'
import { useBranchStore } from '@/stores/branch'
import { useAuthStore } from '@/stores/auth'

const branchStore = useBranchStore()
const authStore = useAuthStore()

const users = ref([])
const roles = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const modalError = ref('')
const userToDelete = ref(null)

const filters = ref({
  search: '',
  role_id: '',
  hotel_branch_id: '',
  is_active: ''
})

const formData = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  password: '',
  role_id: '',
  hotel_branch_id: null,
  is_active: true
})

onMounted(async () => {
  await Promise.all([
    branchStore.fetchAdminBranches(),
    loadRoles(),
    loadUsers()
  ])
})

watch(filters, () => {
  loadUsers()
}, { deep: true })

async function loadRoles() {
  try {
    roles.value = await userApi.getRoles()
  } catch (err) {
    console.error('Failed to load roles:', err)
  }
}

async function loadUsers() {
  loading.value = true
  try {
    const params = { ...filters.value }
    users.value = await userApi.getUsers(params)
  } catch (err) {
    console.error('Failed to load users:', err)
  } finally {
    loading.value = false
  }
}

function getUserInitials(name) {
  if (!name) return '?'
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

function openAddModal() {
  isEditing.value = false
  modalError.value = ''
  formData.value = {
    id: null,
    name: '',
    email: '',
    phone: '',
    password: '',
    role_id: roles.value.length > 0 ? roles.value[0].id : '',
    hotel_branch_id: null,
    is_active: true
  }
  showModal.value = true
}

function openEditModal(u) {
  isEditing.value = true
  modalError.value = ''
  formData.value = {
    id: u.id,
    name: u.name,
    email: u.email,
    phone: u.phone || '',
    password: '',
    role_id: u.role_id,
    hotel_branch_id: u.hotel_branch_id,
    is_active: u.is_active
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  modalError.value = ''
}

function confirmDelete(u) {
  userToDelete.value = u
  showDeleteConfirm.value = true
}

function cancelDelete() {
  showDeleteConfirm.value = false
  userToDelete.value = null
}

async function saveUser() {
  saving.value = true
  modalError.value = ''

  try {
    const data = { ...formData.value }
    if (isEditing.value) {
      if (!data.password) delete data.password
      await userApi.updateUser(data.id, data)
    } else {
      await userApi.createUser(data)
    }
    closeModal()
    await loadUsers()
  } catch (err) {
    console.error('Failed to save user:', err)
    modalError.value = err.response?.data?.message || err.response?.data?.errors?.email?.[0] || 'Gagal menyimpan data user'
  } finally {
    saving.value = false
  }
}

async function deleteUser() {
  if (!userToDelete.value?.id) return
  deleting.value = true
  try {
    await userApi.deleteUser(userToDelete.value.id)
    showDeleteConfirm.value = false
    userToDelete.value = null
    await loadUsers()
  } catch (err) {
    console.error('Failed to delete user:', err)
    alert(err.response?.data?.message || 'Gagal menghapus user')
  } finally {
    deleting.value = false
  }
}
</script>
