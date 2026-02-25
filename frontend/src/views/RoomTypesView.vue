<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Room Types Management</h1>
          <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage room types and pricing</p>
        </div>
        <button
          @click="openAddModal"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + Add Room Type
        </button>
      </div>

      <!-- Room Types Table -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">Loading room types...</p>
      </div>

      <div v-else-if="roomTypes.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">No room types found</p>
      </div>

      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Name
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Description
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Capacity
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Price per Night
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Rooms
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
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
                  <div class="text-sm text-gray-900">{{ roomType.capacity }} guests</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ formatCurrency(roomType.base_price) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ roomType.rooms_count || 0 }} rooms</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="openEditModal(roomType)"
                    class="text-blue-600 hover:text-blue-900 mr-4"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(roomType)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
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
          {{ isEditing ? 'Edit Room Type' : 'Add New Room Type' }}
        </h2>

        <form @submit.prevent="saveRoomType" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., Deluxe Room"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Describe the room type..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price per Night *</label>
              <input
                v-model.number="formData.base_price"
                type="number"
                min="0"
                step="1000"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="500000"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (Guests) *</label>
              <input
                v-model.number="formData.capacity"
                type="number"
                min="1"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="2"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Facilities</label>
              <div class="space-y-2">
                <div v-for="(facility, index) in formData.facilities" :key="index" class="flex gap-2">
                  <input
                    v-model="formData.facilities[index]"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="e.g., King Size Bed, AC, TV"
                  />
                  <button
                    type="button"
                    @click="removeFacility(index)"
                    class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
                  >
                    Remove
                  </button>
                </div>
                <button
                  type="button"
                  @click="addFacility"
                  class="text-sm text-blue-600 hover:text-blue-700"
                >
                  + Add Facility
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
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
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
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Delete Room Type?</h2>
        
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete room type <strong>{{ roomTypeToDelete?.name }}</strong>?
          <span v-if="roomTypeToDelete?.rooms_count > 0" class="block mt-2 text-red-600 font-semibold">
            Warning: This room type has {{ roomTypeToDelete.rooms_count }} active room(s).
          </span>
        </p>

        <div class="flex gap-3">
          <button
            type="button"
            @click="cancelDelete"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="deleteRoomType"
            :disabled="deleting"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
          >
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import LayoutMain from '../components/LayoutMain.vue'
import { roomTypeApi } from '../api'
import axios from 'axios'

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
    const message = err.response?.data?.message || 'Failed to delete room type.'
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
