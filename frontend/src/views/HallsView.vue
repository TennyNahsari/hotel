<template>
  <LayoutMain>
    <div class="space-y-4 md:space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $t('halls.title') }}</h1>
          <p class="text-gray-600 mt-1 text-xs sm:text-sm md:text-base">{{ $t('halls.subtitle') }}</p>
        </div>
        <button
          @click="openAddModal"
          class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-blue-600 text-white text-sm md:text-base rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + {{ $t('halls.addHall') }}
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-3 md:p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('halls.search') }}</label>
            <input
              v-model="filters.search"
              type="text"
              :placeholder="$t('halls.searchPlaceholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('halls.hallType') }}</label>
            <select
              v-model="filters.hall_type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">{{ $t('halls.allTypes') }}</option>
              <option v-for="type in hallTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('halls.status') }}</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">{{ $t('halls.allStatus') }}</option>
              <option value="available">{{ $t('halls.available') }}</option>
              <option value="maintenance">{{ $t('halls.maintenance') }}</option>
              <option value="unavailable">{{ $t('halls.unavailable') }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Halls Table -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">{{ $t('halls.loading') }}</p>
      </div>

      <div v-else-if="halls.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">{{ $t('halls.noHalls') }}</p>
      </div>

      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Mobile Card View -->
        <div class="block md:hidden">
          <div v-for="hall in halls" :key="hall.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
            <div class="space-y-3">
              <div class="flex justify-between items-start">
                <div>
                  <div class="font-medium text-gray-900">{{ hall.name }}</div>
                  <div class="text-sm text-gray-600">{{ hall.hall_type }}</div>
                </div>
                <span
                  :class="{
                    'bg-green-100 text-green-800': hall.status === 'available',
                    'bg-yellow-100 text-yellow-800': hall.status === 'maintenance',
                    'bg-red-100 text-red-800': hall.status === 'unavailable',
                  }"
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ hall.status }}
                </span>
              </div>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-gray-500">{{ $t('halls.floor') }}:</span>
                  <span class="text-gray-900 ml-1">{{ hall.floor || '-' }}</span>
                </div>
                <div>
                  <span class="text-gray-500">{{ $t('halls.capacity') }}:</span>
                  <span class="text-gray-900 ml-1">{{ hall.capacity }} {{ $t('halls.pax') }}</span>
                </div>
              </div>
              <div class="text-sm">
                <span class="text-gray-500">{{ $t('halls.pricePerHour') }}:</span>
                <span class="font-semibold text-gray-900 ml-1">{{ formatCurrency(hall.price_per_hour) }}</span>
              </div>
              <div class="flex flex-wrap gap-2 pt-2">
                <button
                  @click="viewHall(hall)"
                  class="flex-1 text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                >
                  {{ $t('halls.view') }}
                </button>
                <button
                  @click="openEditModal(hall)"
                  class="flex-1 text-xs px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                >
                  {{ $t('halls.edit') }}
                </button>
                <button
                  @click="confirmDelete(hall)"
                  class="flex-1 text-xs px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200"
                >
                  {{ $t('halls.delete') }}
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
                  {{ $t('halls.name') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('halls.type') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('halls.floor') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('halls.capacity') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('halls.pricePerHour') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('halls.status') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('halls.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="hall in halls" :key="hall.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ hall.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ hall.hall_type }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ hall.floor || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ hall.capacity }} {{ $t('halls.pax') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ formatCurrency(hall.price_per_hour) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="{
                      'bg-green-100 text-green-800': hall.status === 'available',
                      'bg-yellow-100 text-yellow-800': hall.status === 'maintenance',
                      'bg-red-100 text-red-800': hall.status === 'unavailable',
                    }"
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  >
                    {{ hall.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="viewHall(hall)"
                    class="text-gray-600 hover:text-gray-900 mr-3"
                  >
                    {{ $t('halls.view') }}
                  </button>
                  <button
                    @click="openEditModal(hall)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    {{ $t('halls.edit') }}
                  </button>
                  <button
                    @click="confirmDelete(hall)"
                    class="text-red-600 hover:text-red-900"
                  >
                    {{ $t('halls.delete') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 15" class="bg-white rounded-lg shadow p-3 md:p-4 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="text-xs sm:text-sm text-gray-700">
          {{ $t('halls.showing') }} {{ pagination.from }} {{ $t('halls.to') }} {{ pagination.to }} {{ $t('halls.of') }} {{ pagination.total }} {{ $t('halls.halls') }}
        </div>
        <div class="flex gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ $t('halls.previous') }}
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ $t('halls.next') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Hall Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-3xl w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? $t('halls.editHall') : $t('halls.addNewHall') }}
        </h2>

        <form @submit.prevent="saveHall" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.name') }} *</label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                :placeholder="$t('halls.namePlaceholder')"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.hallType') }} *</label>
              <select
                v-model="formData.hall_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              >
                <option value="">{{ $t('halls.selectType') }}</option>
                <option v-for="type in hallTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.floor') }}</label>
              <input
                v-model="formData.floor"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                :placeholder="$t('halls.floorPlaceholder')"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.capacityPersons') }} *</label>
              <input
                v-model.number="formData.capacity"
                type="number"
                min="1"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="50"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.areaSqm') }}</label>
              <input
                v-model.number="formData.area_sqm"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="100.00"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.pricePerHour') }} *</label>
              <input
                v-model.number="formData.price_per_hour"
                type="number"
                min="0"
                step="10000"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="500000"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.status') }} *</label>
              <select
                v-model="formData.status"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              >
                <option value="available">{{ $t('halls.available') }}</option>
                <option value="maintenance">{{ $t('halls.maintenance') }}</option>
                <option value="unavailable">{{ $t('halls.unavailable') }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.imageUrl') }}</label>
              <input
                v-model="formData.image_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="https://..."
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.description') }}</label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                :placeholder="$t('halls.descriptionPlaceholder')"
              ></textarea>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('halls.facilities') }}</label>
              <textarea
                v-model="formData.facilities"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                placeholder='{"av_equipment":["Projector","Screen"],"furniture":["Tables","Chairs"],"tech":["WiFi","AC"]}'
              ></textarea>
              <p class="text-xs text-gray-500 mt-1">{{ $t('halls.facilitiesHint') }}</p>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              {{ $t('halls.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? $t('halls.saving') : (isEditing ? $t('halls.update') : $t('halls.create')) }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Hall Modal -->
    <div
      v-if="showViewModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeViewModal"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ viewData.name }}
        </h2>

        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">{{ $t('halls.type') }}</p>
              <p class="font-medium">{{ viewData.hall_type }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">{{ $t('halls.floor') }}</p>
              <p class="font-medium">{{ viewData.floor || '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">{{ $t('halls.capacity') }}</p>
              <p class="font-medium">{{ viewData.capacity }} {{ $t('halls.persons') }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">{{ $t('halls.area') }}</p>
              <p class="font-medium">{{ viewData.area_sqm ? viewData.area_sqm + ' ' + $t('halls.sqm') : '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">{{ $t('halls.pricePerHour') }}</p>
              <p class="font-medium text-lg">{{ formatCurrency(viewData.price_per_hour) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">{{ $t('halls.status') }}</p>
              <p class="font-medium capitalize">{{ viewData.status }}</p>
            </div>
          </div>

          <div v-if="viewData.description">
            <p class="text-sm text-gray-500">{{ $t('halls.description') }}</p>
            <p class="text-gray-700">{{ viewData.description }}</p>
          </div>

          <div v-if="viewData.facilities">
            <p class="text-sm text-gray-500 mb-2">{{ $t('halls.facilities') }}</p>
            <div class="bg-gray-50 p-4 rounded-lg">
              <div v-if="parsedFacilities" class="space-y-3">
                <div v-for="(items, category) in parsedFacilities" :key="category">
                  <p class="font-semibold text-gray-700 mb-1 capitalize">{{ String(category).replace(/_/g, ' ') }}</p>
                  <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                    <li v-for="(item, idx) in items" :key="idx">{{ item }}</li>
                  </ul>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500">{{ $t('halls.noFacilities') }}</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t mt-6">
          <button
            @click="closeViewModal"
            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            {{ $t('halls.close') }}
          </button>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { hallApi } from '@/api'
import LayoutMain from '@/components/LayoutMain.vue'

const router = useRouter()
const { t } = useI18n()

const halls = ref([])
const hallTypes = ref([])
const loading = ref(false)
const showModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  search: '',
  hall_type: '',
  status: ''
})

const formData = ref({
  name: '',
  hall_type: '',
  floor: '',
  capacity: null,
  area_sqm: null,
  price_per_hour: null,
  facilities: '',
  description: '',
  image_url: '',
  status: 'available'
})

const viewData = ref({})
const editingId = ref(null)

// Parse facilities JSON for display
const parsedFacilities = computed(() => {
  if (!viewData.value.facilities) return null
  
  let facilities = viewData.value.facilities
  
  try {
    // If it's a string, try to parse it
    if (typeof facilities === 'string') {
      facilities = JSON.parse(facilities)
      
      // If still a string after first parse (double-encoded), parse again
      if (typeof facilities === 'string') {
        facilities = JSON.parse(facilities)
      }
    }
    
    // Make sure we have an object
    if (typeof facilities === 'object' && facilities !== null && !Array.isArray(facilities)) {
      return facilities
    }
    
    return null
  } catch (e) {
    console.error('Error parsing facilities:', e)
    return null
  }
})

// Fetch halls
const fetchHalls = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: 15,
      ...filters.value
    }
    const response = await hallApi.getHalls(params)
    console.log('Halls API Response:', response)
    
    // Handle both array and paginated response
    if (Array.isArray(response)) {
      halls.value = response
      pagination.value = {
        current_page: 1,
        last_page: 1,
        total: response.length,
        from: 1,
        to: response.length
      }
    } else {
      halls.value = response.data || response
      pagination.value = {
        current_page: response.current_page || 1,
        last_page: response.last_page || 1,
        total: response.total || halls.value.length,
        from: response.from || 1,
        to: response.to || halls.value.length
      }
    }
  } catch (error) {
    console.error('Error fetching halls:', error)
    alert('Failed to fetch halls')
    halls.value = []
  } finally {
    loading.value = false
  }
}

// Fetch hall types
const fetchHallTypes = async () => {
  try {
    const response = await hallApi.getHallTypes()
    hallTypes.value = response
  } catch (error) {
    console.error('Error fetching hall types:', error)
  }
}

// Open add modal
const openAddModal = () => {
  isEditing.value = false
  formData.value = {
    name: '',
    hall_type: '',
    floor: '',
    capacity: null,
    area_sqm: null,
    price_per_hour: null,
    facilities: '',
    description: '',
    image_url: '',
    status: 'available'
  }
  showModal.value = true
}

// Open edit modal
const openEditModal = (hall) => {
  isEditing.value = true
  editingId.value = hall.id
  
  // Parse facilities properly
  let facilitiesStr = ''
  if (hall.facilities) {
    if (typeof hall.facilities === 'string') {
      // If it's already a string, try to parse and re-stringify for formatting
      try {
        const parsed = JSON.parse(hall.facilities)
        facilitiesStr = JSON.stringify(parsed, null, 2)
      } catch (e) {
        // If parsing fails, use as-is
        facilitiesStr = hall.facilities
      }
    } else {
      // If it's an object, stringify it
      facilitiesStr = JSON.stringify(hall.facilities, null, 2)
    }
  }
  
  formData.value = {
    name: hall.name,
    hall_type: hall.hall_type,
    floor: hall.floor || '',
    capacity: hall.capacity,
    area_sqm: hall.area_sqm,
    price_per_hour: hall.price_per_hour,
    facilities: facilitiesStr,
    description: hall.description || '',
    image_url: hall.image_url || '',
    status: hall.status
  }
  showModal.value = true
}

// Close modal
const closeModal = () => {
  showModal.value = false
  editingId.value = null
}

// View hall
const viewHall = (hall) => {
  viewData.value = hall
  showViewModal.value = true
}

// Close view modal
const closeViewModal = () => {
  showViewModal.value = false
  viewData.value = {}
}

// Save hall
const saveHall = async () => {
  // Validate JSON
  let facilities = null
  if (formData.value.facilities && formData.value.facilities.trim()) {
    try {
      facilities = JSON.parse(formData.value.facilities)
    } catch (e) {
      alert('Invalid JSON format for facilities')
      return
    }
  }

  saving.value = true
  try {
    const data = {
      ...formData.value,
      facilities: facilities ? JSON.stringify(facilities) : null
    }

    if (isEditing.value) {
      await hallApi.updateHall(editingId.value, data)
      alert('Hall updated successfully')
    } else {
      await hallApi.createHall(data)
      alert('Hall created successfully')
    }

    closeModal()
    fetchHalls(pagination.value.current_page)
  } catch (error) {
    console.error('Error saving hall:', error)
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      alert(errors.join('\n'))
    } else {
      alert('Failed to save hall')
    }
  } finally {
    saving.value = false
  }
}

// Confirm delete
const confirmDelete = (hall) => {
  if (confirm(`${t('halls.confirmDelete')} "${hall.name}"?`)) {
    deleteHall(hall.id)
  }
}

// Delete hall
const deleteHall = async (id) => {
  try {
    await hallApi.deleteHall(id)
    alert('Hall deleted successfully')
    fetchHalls(pagination.value.current_page)
  } catch (error) {
    console.error('Error deleting hall:', error)
    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else {
      alert('Failed to delete hall')
    }
  }
}

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchHalls(page)
  }
}

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

// Watch filters
watch(filters, () => {
  fetchHalls(1)
}, { deep: true })

// Initial fetch
onMounted(() => {
  fetchHalls()
  fetchHallTypes()
})
</script>
