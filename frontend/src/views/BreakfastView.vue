<template>
  <LayoutMain>
    <div class="space-y-4 md:space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $t('breakfast.title') }}</h1>
          <p class="text-gray-600 mt-1 text-xs sm:text-sm md:text-base">{{ $t('breakfast.subtitle') }}</p>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600">{{ $t('breakfast.totalGuests') }}</p>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ statistics.total_bookings || 0 }}</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 md:p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600">{{ $t('breakfast.totalPortions') }}</p>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ statistics.total_portions || 0 }}</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 md:p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600">{{ $t('breakfast.distributed') }}</p>
              <p class="text-xl sm:text-2xl font-bold text-green-600 mt-1">{{ statistics.taken_portions || 0 }}</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 md:p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600">{{ $t('breakfast.remaining') }}</p>
              <p class="text-xl sm:text-2xl font-bold text-orange-600 mt-1">{{ statistics.remaining_portions || 0 }}</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-3 md:p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('breakfast.search') }}</label>
            <input
              v-model="filters.search"
              @input="loadBookings"
              type="text"
              :placeholder="$t('breakfast.searchPlaceholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('breakfast.status') }}</label>
            <select
              v-model="filters.breakfast_status"
              @change="loadBookings"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">{{ $t('breakfast.allStatus') }}</option>
              <option value="not_taken">{{ $t('breakfast.notTaken') }}</option>
              <option value="taken">{{ $t('breakfast.taken') }}</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 text-sm md:text-base hover:bg-gray-50 transition-colors"
            >
              {{ $t('breakfast.resetFilters') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Bookings List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="p-8 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="text-gray-600 mt-2">{{ $t('breakfast.loading') }}</p>
        </div>

        <div v-else-if="bookings.length === 0" class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <p class="text-gray-500 mt-4">{{ $t('breakfast.noBookings') }}</p>
        </div>

        <div v-else>
          <!-- Mobile Card View -->
          <div class="md:hidden divide-y divide-gray-200">
            <div v-for="booking in bookings" :key="booking.id" class="p-4">
              <div class="space-y-3">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900">{{ booking.guest?.name }}</div>
                    <div class="text-sm text-gray-600">Room {{ booking.rooms?.[0]?.room_number }}</div>
                    <div class="text-sm text-gray-500">{{ booking.rooms?.[0]?.room_type?.name }}</div>
                  </div>
                  <span 
                    :class="getStatusBadgeClass(booking.breakfast_status)"
                    class="px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ getStatusLabel(booking.breakfast_status) }}
                  </span>
                </div>
                
                <div class="bg-blue-50 rounded p-2">
                  <p class="text-sm text-blue-900 font-medium">{{ $t('breakfast.breakfast') }}: 2 {{ $t('breakfast.portions') }}</p>
                </div>

                <button
                  @click="toggleBreakfastStatus(booking)"
                  :disabled="updating === booking.id"
                  :class="booking.breakfast_status === 'taken' 
                    ? 'bg-orange-100 text-orange-700 hover:bg-orange-200' 
                    : 'bg-green-100 text-green-700 hover:bg-green-200'"
                  class="w-full px-4 py-2 rounded-lg font-medium transition-colors disabled:opacity-50"
                >
                  {{ updating === booking.id ? $t('breakfast.updating') : (booking.breakfast_status === 'taken' ? $t('breakfast.markNotTaken') : $t('breakfast.markTaken')) }}
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('breakfast.guest') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('breakfast.room') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('breakfast.roomType') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('breakfast.breakfast') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('breakfast.status') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('breakfast.action') }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ booking.guest?.name }}</div>
                    <div class="text-sm text-gray-500">{{ booking.guest?.phone }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ booking.rooms?.[0]?.room_number }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ booking.rooms?.[0]?.room_type?.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="bg-blue-50 inline-block px-3 py-1 rounded-full">
                      <span class="text-sm font-medium text-blue-900">2 {{ $t('breakfast.portions') }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="getStatusBadgeClass(booking.breakfast_status)"
                      class="px-3 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ getStatusLabel(booking.breakfast_status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button
                      @click="toggleBreakfastStatus(booking)"
                      :disabled="updating === booking.id"
                      :class="booking.breakfast_status === 'taken' 
                        ? 'bg-orange-100 text-orange-700 hover:bg-orange-200' 
                        : 'bg-green-100 text-green-700 hover:bg-green-200'"
                      class="px-4 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                    >
                      {{ updating === booking.id ? $t('breakfast.updating') : (booking.breakfast_status === 'taken' ? $t('breakfast.markNotTaken') : $t('breakfast.markTaken')) }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="px-6 py-4 flex justify-between items-center border-t">
              <div class="text-sm text-gray-700">
                {{ $t('breakfast.showing') }} {{ (pagination.current_page - 1) * pagination.per_page + 1 }} {{ $t('breakfast.to') }} 
                {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} {{ $t('breakfast.of') }} 
                {{ pagination.total }} {{ $t('breakfast.bookings') }}
              </div>
              <div class="flex gap-2">
                <button
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ $t('breakfast.previous') }}
                </button>
                <button
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ $t('breakfast.next') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LayoutMain from '../components/LayoutMain.vue'
import { breakfastApi } from '../api'
import axios from 'axios'

const { t } = useI18n()

const bookings = ref([])
const statistics = ref({})
const loading = ref(false)
const updating = ref(null)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const filters = ref({
  search: '',
  breakfast_status: ''
})

onMounted(async () => {
  try {
    const apiUrl = import.meta.env.VITE_API_URL || 'https://hotel.tazkia.web.id'
    await axios.get(`${apiUrl}/sanctum/csrf-cookie`, {
      withCredentials: true
    })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }

  loadBookings()
  loadStatistics()
})

async function loadBookings(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.breakfast_status) params.breakfast_status = filters.value.breakfast_status

    const response = await breakfastApi.getBreakfasts(params)
    
    // Handle paginated response
    if (response.data && Array.isArray(response.data)) {
      bookings.value = response.data
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total
      }
    } else {
      bookings.value = response
    }
  } catch (err) {
    console.error('Failed to load bookings:', err)
  } finally {
    loading.value = false
  }
}

async function loadStatistics() {
  try {
    statistics.value = await breakfastApi.getStatistics()
  } catch (err) {
    console.error('Failed to load statistics:', err)
  }
}

async function toggleBreakfastStatus(booking) {
  updating.value = booking.id
  
  try {
    const newStatus = booking.breakfast_status === 'taken' ? 'not_taken' : 'taken'
    await breakfastApi.updateStatus(booking.id, newStatus)
    
    // Update local data
    booking.breakfast_status = newStatus
    
    // Reload statistics
    await loadStatistics()
  } catch (err) {
    console.error('Failed to update breakfast status:', err)
    alert(t('breakfast.updateFailed'))
  } finally {
    updating.value = null
  }
}

function changePage(page) {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadBookings(page)
  }
}

function resetFilters() {
  filters.value = {
    search: '',
    breakfast_status: ''
  }
  loadBookings()
}

function getStatusLabel(status) {
  const labels = {
    'not_taken': t('breakfast.notTaken'),
    'taken': t('breakfast.taken')
  }
  return labels[status] || status
}

function getStatusBadgeClass(status) {
  const classes = {
    'not_taken': 'bg-orange-100 text-orange-800',
    'taken': 'bg-green-100 text-green-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
