<template>
  <LayoutMain>
    <div class="space-y-4 md:space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $t('roomTypes.title') }}</h1>
          <p class="text-gray-600 mt-1 text-xs sm:text-sm md:text-base">{{ $t('roomTypes.subtitle') }}</p>
        </div>
        <button
          @click="openAddModal"
          class="w-full sm:w-auto px-4 py-2 text-sm md:text-base bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + {{ $t('roomTypes.addRoomType') }}
        </button>
      </div>

      <!-- Room Types Table -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">{{ $t('roomTypes.loading') }}</p>
      </div>

      <div v-else-if="roomTypes.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">{{ $t('roomTypes.noRoomTypes') }}</p>
      </div>

      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Mobile Card View -->
        <div class="block md:hidden">
          <div v-for="roomType in roomTypes" :key="roomType.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
            <div class="space-y-3">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ roomType.name }}</div>
                  <div class="text-sm text-gray-500 mt-1">{{ roomType.description || '-' }}</div>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-gray-500">{{ $t('roomTypes.capacity') }}:</span>
                  <span class="font-medium text-gray-900 ml-1">{{ roomType.capacity }} {{ $t('roomTypes.guests') }}</span>
                </div>
                <div>
                  <span class="text-gray-500">{{ $t('roomTypes.rooms') }}:</span>
                  <span class="font-medium text-gray-900 ml-1">{{ roomType.rooms_count || 0 }}</span>
                </div>
                <div class="col-span-2">
                  <span class="text-gray-500">{{ $t('roomTypes.pricePerNight') }}:</span>
                  <span class="font-semibold text-gray-900 ml-1">{{ formatCurrency(roomType.base_price) }}</span>
                </div>
              </div>
              <div class="flex gap-2">
                <button
                  @click="openEditModal(roomType)"
                  class="flex-1 text-xs px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors"
                >
                  {{ $t('roomTypes.edit') }}
                </button>
                <button
                  @click="confirmDelete(roomType)"
                  class="flex-1 text-xs px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors"
                >
                  {{ $t('roomTypes.delete') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('roomTypes.name') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('roomTypes.description') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('roomTypes.capacity') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('roomTypes.pricePerNight') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('roomTypes.rooms') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('roomTypes.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="roomType in roomTypes" :key="roomType.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ roomType.name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-500 max-w-xs truncate">
                    {{ roomType.description || '-' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ roomType.capacity }} {{ $t('roomTypes.guests') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ formatCurrency(roomType.base_price) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ roomType.rooms_count || 0 }} {{ $t('roomTypes.roomsCount') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="openEditModal(roomType)"
                    class="text-blue-600 hover:text-blue-900 mr-4"
                  >
                    {{ $t('roomTypes.edit') }}
                  </button>
                  <button
                    @click="confirmDelete(roomType)"
                    class="text-red-600 hover:text-red-900"
                  >
                    {{ $t('roomTypes.delete') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Room Type Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? $t('roomTypes.editRoomType') : $t('roomTypes.addNewRoomType') }}
        </h2>

        <form @submit.prevent="saveRoomType" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('roomTypes.nameLabel') }}</label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :placeholder="$t('roomTypes.namePlaceholder')"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('roomTypes.descriptionLabel') }}</label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :placeholder="$t('roomTypes.descriptionPlaceholder')"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('roomTypes.priceLabel') }}</label>
              <input
                v-model.number="formData.base_price"
                type="number"
                min="0"
                step="1000"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :placeholder="$t('roomTypes.pricePlaceholder')"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('roomTypes.capacityLabel') }}</label>
              <input
                v-model.number="formData.capacity"
                type="number"
                min="1"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :placeholder="$t('roomTypes.capacityPlaceholder')"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('roomTypes.facilities') }}</label>
              <div class="space-y-2">
                <div v-for="(facility, index) in formData.facilities" :key="index" class="flex gap-2">
                  <input
                    v-model="formData.facilities[index]"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :placeholder="$t('roomTypes.facilityPlaceholder')"
                  />
                  <button
                    type="button"
                    @click="removeFacility(index)"
                    class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
                  >
                    {{ $t('roomTypes.removeFacility') }}
                  </button>
                </div>
                <button
                  type="button"
                  @click="addFacility"
                  class="text-sm text-blue-600 hover:text-blue-700"
                >
                  + {{ $t('roomTypes.addFacility') }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="error" class="rounded-md bg-red-50 p-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              {{ $t('roomTypes.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ saving ? $t('roomTypes.saving') : (isEditing ? $t('roomTypes.update') : $t('roomTypes.create')) }}
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
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $t('roomTypes.deleteRoomType') }}</h2>
        
        <p class="text-gray-600 mb-6">
          {{ $t('roomTypes.deleteConfirm') }} <strong>{{ roomTypeToDelete?.name }}</strong>?
          <span v-if="roomTypeToDelete?.rooms_count > 0" class="block mt-2 text-red-600 font-semibold">
            {{ $t('roomTypes.deleteWarning') }} {{ roomTypeToDelete.rooms_count }} {{ $t('roomTypes.activeRooms') }}
          </span>
        </p>

        <div class="flex gap-3">
          <button
            type="button"
            @click="cancelDelete"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            {{ $t('roomTypes.cancel') }}
          </button>
          <button
            @click="deleteRoomType"
            :disabled="deleting"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
          >
            {{ deleting ? $t('roomTypes.deleting') : $t('roomTypes.delete') }}
          </button>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LayoutMain from '../components/LayoutMain.vue'
import { roomTypeApi } from '../api'
import axios from 'axios'

const { t } = useI18n()

const roomTypes = ref([])
const loading = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const roomTypeToDelete = ref(null)

const formData = ref({
  name: '',
  description: '',
  base_price: null,
  capacity: 2,
  facilities: [],
})

onMounted(async () => {
  // Ensure CSRF cookie is set first
  try {
    const apiUrl = import.meta.env.VITE_API_URL || 'https://hotel.tazkia.web.id'
    await axios.get(`${apiUrl}/sanctum/csrf-cookie`, {
      withCredentials: true
    })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }
  
  loadRoomTypes()
})

async function loadRoomTypes() {
  loading.value = true
  try {
    roomTypes.value = await roomTypeApi.getRoomTypes()
  } catch (err) {
    console.error('Failed to load room types:', err)
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  isEditing.value = false
  formData.value = {
    name: '',
    description: '',
    base_price: null,
    capacity: 2,
    facilities: [],
  }
  error.value = ''
  showModal.value = true
}

function openEditModal(roomType) {
  isEditing.value = true
  formData.value = {
    id: roomType.id,
    name: roomType.name,
    description: roomType.description || '',
    base_price: roomType.base_price,
    capacity: roomType.capacity,
    facilities: Array.isArray(roomType.facilities) ? [...roomType.facilities] : [],
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  error.value = ''
}

function confirmDelete(roomType) {
  roomTypeToDelete.value = { ...roomType }
  showDeleteConfirm.value = true
}

function cancelDelete() {
  showDeleteConfirm.value = false
  roomTypeToDelete.value = null
}

async function deleteRoomType() {
  if (!roomTypeToDelete.value?.id) return
  
  deleting.value = true
  try {
    await roomTypeApi.deleteRoomType(roomTypeToDelete.value.id)
    showDeleteConfirm.value = false
    roomTypeToDelete.value = null
    await loadRoomTypes()
  } catch (err) {
    console.error('Failed to delete room type:', err)
    const message = err.response?.data?.message || t('roomTypes.errorOccurred')
    alert(message)
  } finally {
    deleting.value = false
  }
}

async function saveRoomType() {
  saving.value = true
  error.value = ''

  try {
    // Remove empty facilities
    const data = {
      ...formData.value,
      facilities: formData.value.facilities.filter(f => f.trim() !== '')
    }

    if (isEditing.value) {
      await roomTypeApi.updateRoomType(formData.value.id, data)
    } else {
      await roomTypeApi.createRoomType(data)
    }
    
    closeModal()
    await loadRoomTypes()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save room type'
  } finally {
    saving.value = false
  }
}

function addFacility() {
  formData.value.facilities.push('')
}

function removeFacility(index) {
  formData.value.facilities.splice(index, 1)
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}
</script>
