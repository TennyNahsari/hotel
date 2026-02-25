<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Halls Management</h1>
          <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage meeting rooms and event halls</p>
        </div>
        <button
          @click="openAddModal"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + Add Hall
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search halls..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hall Type</label>
            <select
              v-model="filters.hall_type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Types</option>
              <option v-for="type in hallTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Status</option>
              <option value="available">Available</option>
              <option value="maintenance">Maintenance</option>
              <option value="unavailable">Unavailable</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Halls Table -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">Loading halls...</p>
      </div>

      <div v-else-if="halls.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">No halls found</p>
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
                  Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Floor
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Capacity
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Price/Hour
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
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
                  <div class="text-sm text-gray-900">{{ hall.capacity }} pax</div>
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
                    View
                  </button>
                  <button
                    @click="openEditModal(hall)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(hall)"
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

      <!-- Pagination -->
      <div v-if="pagination.total > 15" class="bg-white rounded-lg shadow p-4 flex justify-between items-center">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} halls
        </div>
        <div class="flex gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
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
          {{ isEditing ? 'Edit Hall' : 'Add New Hall' }}
        </h2>

        <form @submit.prevent="saveHall" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="e.g., Ballroom A"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hall Type *</label>
              <select
                v-model="formData.hall_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Type</option>
                <option v-for="type in hallTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Floor</label>
              <input
                v-model="formData.floor"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="e.g., 2nd Floor"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (Persons) *</label>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Area (sqm)</label>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Price per Hour *</label>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
              <select
                v-model="formData.status"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              >
                <option value="available">Available</option>
                <option value="maintenance">Maintenance</option>
                <option value="unavailable">Unavailable</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
              <input
                v-model="formData.image_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="https://..."
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="Describe the hall..."
              ></textarea>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Facilities (JSON)</label>
              <textarea
                v-model="formData.facilities"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                placeholder='{"av_equipment":["Projector","Screen"],"furniture":["Tables","Chairs"],"tech":["WiFi","AC"]}'
              ></textarea>
              <p class="text-xs text-gray-500 mt-1">Enter valid JSON format for facilities</p>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
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
              <p class="text-sm text-gray-500">Type</p>
              <p class="font-medium">{{ viewData.hall_type }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Floor</p>
              <p class="font-medium">{{ viewData.floor || '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Capacity</p>
              <p class="font-medium">{{ viewData.capacity }} persons</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Area</p>
              <p class="font-medium">{{ viewData.area_sqm ? viewData.area_sqm + ' sqm' : '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Price per Hour</p>
              <p class="font-medium text-lg">{{ formatCurrency(viewData.price_per_hour) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Status</p>
              <p class="font-medium capitalize">{{ viewData.status }}</p>
            </div>
          </div>

          <div v-if="viewData.description">
            <p class="text-sm text-gray-500">Description</p>
            <p class="text-gray-700">{{ viewData.description }}</p>
          </div>

          <div v-if="viewData.facilities">
            <p class="text-sm text-gray-500 mb-2">Facilities</p>
            <div class="bg-gray-50 p-4 rounded-lg">
              <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ JSON.stringify(viewData.facilities, null, 2) }}</pre>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t mt-6">
          <button
            @click="closeViewModal"
            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { hallApi } from '@/api'
import LayoutMain from '@/components/LayoutMain.vue'

const router = useRouter()

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
    halls.value = response.data
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      total: response.total,
      from: response.from,
      to: response.to
    }
  } catch (error) {
    console.error('Error fetching halls:', error)
    alert('Failed to fetch halls')
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
  formData.value = {
    name: hall.name,
    hall_type: hall.hall_type,
    floor: hall.floor || '',
    capacity: hall.capacity,
    area_sqm: hall.area_sqm,
    price_per_hour: hall.price_per_hour,
    facilities: hall.facilities ? JSON.stringify(hall.facilities, null, 2) : '',
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
  if (confirm(`Are you sure you want to delete "${hall.name}"?`)) {
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
