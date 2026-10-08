<template>
  <LayoutMain>
    <div class="space-y-4 md:space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $t('users.title') }}</h1>
          <p class="text-gray-600 mt-1 text-xs sm:text-sm md:text-base">{{ $t('users.subtitle') }}</p>
        </div>
        <button
          @click="openAddModal"
          class="w-full sm:w-auto px-4 py-2 text-sm md:text-base bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap flex items-center justify-center gap-2 shadow-sm"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
          <span>+ {{ $t('users.addUser') }}</span>
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-3 md:p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
          <!-- Search -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.search') }}</label>
            <input
              v-model="filters.search"
              type="text"
              :placeholder="$t('users.searchPlaceholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>

          <!-- Role Filter -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.role') }}</label>
            <select
              v-model="filters.role_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">{{ $t('users.allRoles') }}</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.display_name || role.name }}
              </option>
            </select>
          </div>

          <!-- Branch Filter -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.branchAccess') }}</label>
            <select
              v-model="filters.hotel_branch_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">{{ $t('users.allBranches') }}</option>
              <option value="global">🌐 {{ $t('users.globalAccess') }}</option>
              <option v-for="b in branchStore.branches" :key="b.id" :value="b.id">
                🏢 {{ b.name }}
              </option>
            </select>
          </div>

          <!-- Active Filter -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('users.status') }}</label>
            <select
              v-model="filters.is_active"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">{{ $t('users.allStatus') }}</option>
              <option value="true">{{ $t('users.active') }}</option>
              <option value="false">{{ $t('users.inactive') }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users List Table / Mobile Cards -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">{{ $t('users.loading') }}</p>
      </div>

      <div v-else-if="users.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">{{ $t('users.noUsers') }}</p>
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
                  {{ u.is_active ? $t('users.active') : $t('users.inactive') }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-2 text-xs">
                <div>
                  <span class="text-gray-500 block">{{ $t('users.role') }}:</span>
                  <span class="font-medium text-gray-900">{{ u.role?.display_name || u.role?.name || '-' }}</span>
                </div>
                <div>
                  <span class="text-gray-500 block">{{ $t('users.colBranch') }}:</span>
                  <span v-if="!u.hotel_branch_id" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-900 border border-amber-200">
                    🌐 {{ $t('users.globalAccess') }}
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
                  {{ $t('users.editUser') }}
                </button>
                <button
                  @click="confirmDelete(u)"
                  class="flex-1 text-xs px-3 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100 transition-colors font-medium"
                  :disabled="u.id === authStore.user?.id"
                >
                  {{ $t('users.deleteUser') }}
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.colUser') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.colContact') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.colRole') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.colBranch') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.colStatus') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('users.colActions') }}</th>
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
                      <div class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span>{{ u.name }}</span>
                        <span v-if="u.email === 'owner@hotel.com' || u.role?.name === 'owner'" class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 rounded uppercase">Super Admin</span>
                      </div>
                      <div class="text-xs text-blue-600 font-medium">{{ u.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ u.phone || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ u.role?.display_name || u.role?.name || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="!u.hotel_branch_id" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-900 border border-amber-300">
                    <span>🌐</span>
                    <span>{{ $t('users.globalAccess') }}</span>
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
                    {{ u.is_active ? $t('users.active') : $t('users.inactive') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="openEditModal(u)"
                    class="text-blue-600 hover:text-blue-900 font-semibold mr-4"
                  >
                    {{ $t('users.editUser') }}
                  </button>
                  <button
                    @click="confirmDelete(u)"
                    :disabled="u.id === authStore.user?.id"
                    class="text-red-600 hover:text-red-900 font-semibold disabled:opacity-40 disabled:cursor-not-allowed"
                    :title="u.id === authStore.user?.id ? 'Tidak dapat menghapus akun sendiri' : $t('users.deleteUser')"
                  >
                    {{ $t('users.deleteUser') }}
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
          {{ isEditing ? $t('users.modalEditTitle') : $t('users.modalAddTitle') }}
        </h2>

        <form @submit.prevent="saveUser" class="space-y-4">
          <!-- Nama Lengkap -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.fullName') }} *</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.email') }} *</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.phone') }}</label>
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
              {{ $t('users.password') }} {{ isEditing ? `(${$t('users.passwordHint')})` : '*' }}
            </label>
            <input
              v-model="formData.password"
              type="password"
              :required="!isEditing"
              minlength="6"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
              :placeholder="isEditing ? $t('users.passwordHint') : $t('users.passwordMin')"
            />
          </div>

          <!-- Peran / Role -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.role') }} *</label>
            <select
              v-model="formData.role_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="" disabled>{{ $t('users.selectRole') }}</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.display_name || role.name }} ({{ role.description }})
              </option>
            </select>
          </div>

          <!-- Hak Akses Cabang -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">{{ $t('users.branchAssignment') }} *</label>
            <select
              v-model="formData.hotel_branch_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm font-medium"
            >
              <option :value="null">🌐 {{ $t('users.globalAccess') }}</option>
              <option v-for="b in branchStore.branches" :key="b.id" :value="b.id">
                🏢 {{ b.name }} ({{ b.city }})
              </option>
            </select>
            <p class="text-xs text-gray-500 pt-1 leading-relaxed">
              <span v-if="formData.hotel_branch_id === null" class="text-amber-700 font-semibold">
                {{ $t('users.globalHint') }}
              </span>
              <span v-else class="text-emerald-700 font-semibold">
                {{ $t('users.branchHint') }}
              </span>
            </p>
          </div>

          <!-- Status Active -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.status') }}</label>
            <div class="flex items-center gap-4 pt-1">
              <label class="inline-flex items-center cursor-pointer">
                <input type="radio" v-model="formData.is_active" :value="true" class="text-blue-600 focus:ring-blue-500" />
                <span class="ml-2 text-sm text-gray-700">{{ $t('users.active') }}</span>
              </label>
              <label class="inline-flex items-center cursor-pointer">
                <input type="radio" v-model="formData.is_active" :value="false" class="text-red-600 focus:ring-red-500" />
                <span class="ml-2 text-sm text-gray-700">{{ $t('users.inactive') }}</span>
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
              {{ $t('users.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold disabled:opacity-50"
            >
              {{ saving ? $t('users.saving') : (isEditing ? $t('users.save') : $t('users.create')) }}
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
        <h2 class="text-xl font-bold text-gray-900 mb-3">{{ $t('users.deleteConfirmTitle') }}</h2>
        <p class="text-sm text-gray-600 mb-6">
          {{ $t('users.deleteConfirmDesc', { name: userToDelete?.name, email: userToDelete?.email }) }}
        </p>

        <div class="flex gap-3">
          <button
            type="button"
            @click="cancelDelete"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
          >
            {{ $t('users.cancel') }}
          </button>
          <button
            @click="deleteUser"
            :disabled="deleting"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold disabled:opacity-50"
          >
            {{ deleting ? $t('users.deleting') : $t('users.confirmDelete') }}
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
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.role_id) params.role_id = filters.value.role_id
    if (filters.value.hotel_branch_id) params.hotel_branch_id = filters.value.hotel_branch_id
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active

    const res = await userApi.getUsers(params)
    users.value = Array.isArray(res) ? res : (res?.data || [])
  } catch (err) {
    console.error('Failed to load users:', err)
    users.value = []
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
