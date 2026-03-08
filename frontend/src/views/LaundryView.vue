<template>
  <LayoutMain>
    <div class="px-3 sm:px-4 md:px-6 lg:px-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-xl sm:text-2xl md:text-3xl font-semibold leading-6 text-gray-900">{{ $t('laundry.title') }}</h1>
          <p class="mt-2 text-xs sm:text-sm text-gray-700">{{ $t('laundry.subtitle') }}</p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mt-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'create'"
            :class="[
              activeTab === 'create'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
            ]"
          >
            {{ $t('laundry.createOrder') }}
          </button>
          <button
            @click="activeTab = 'history'"
            :class="[
              activeTab === 'history'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
            ]"
          >
            {{ $t('laundry.orderHistory') }}
          </button>
        </nav>
      </div>

      <!-- Create Order Tab -->
      <div v-show="activeTab === 'create'" class="mt-4 md:mt-6">
        <div class="bg-white shadow sm:rounded-lg">
          <div class="px-3 py-4 sm:px-4 sm:py-5 md:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{ $t('laundry.newOrder') }}</h3>
            <form @submit.prevent="submitOrder" class="space-y-3 md:space-y-4">
              <!-- Booking Selection -->
              <div>
                <label for="booking" class="block text-xs sm:text-sm font-medium text-gray-700">{{ $t('laundry.booking') }}</label>
                <select
                  id="booking"
                  v-model="orderForm.booking_id"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                  <option value="">{{ $t('laundry.selectBooking') }}</option>
                  <option v-for="booking in bookings" :key="booking.id" :value="booking.id">
                    {{ booking.booking_number }} - {{ booking.guest?.full_name }} (Room {{ booking.room?.room_number }})
                  </option>
                </select>
              </div>

              <!-- Weight (kg) -->
              <div>
                <label for="weight" class="block text-xs sm:text-sm font-medium text-gray-700">{{ $t('laundry.weight') }}</label>
                <input
                  id="weight"
                  type="number"
                  v-model.number="orderForm.weight_kg"
                  min="0.1"
                  step="0.1"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :placeholder="$t('laundry.weightPlaceholder')"
                />
              </div>

              <!-- Price per kg -->
              <div>
                <label for="price_per_kg" class="block text-xs sm:text-sm font-medium text-gray-700">{{ $t('laundry.pricePerKg') }}</label>
                <input
                  id="price_per_kg"
                  type="number"
                  v-model.number="orderForm.price_per_kg"
                  min="0"
                  step="1000"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :placeholder="$t('laundry.pricePlaceholder')"
                />
              </div>

              <!-- Total Amount (calculated) -->
              <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700">{{ $t('laundry.totalAmount') }}</label>
                <div class="mt-1 block w-full rounded-md bg-gray-50 px-3 py-2 text-gray-900 sm:text-sm border border-gray-300">
                  Rp {{ calculatedTotal.toLocaleString('id-ID') }}
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label for="notes" class="block text-xs sm:text-sm font-medium text-gray-700">{{ $t('laundry.notes') }}</label>
                <textarea
                  id="notes"
                  v-model="orderForm.notes"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :placeholder="$t('laundry.notesPlaceholder')"
                ></textarea>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ isSubmitting ? $t('laundry.creating') : $t('laundry.createOrder') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Order History Tab -->
      <div v-show="activeTab === 'history'" class="mt-4 md:mt-6">
        <!-- Filters -->
        <div class="mb-4 flex flex-col sm:flex-row gap-3 md:gap-4">
          <input
            v-model="historyFilters.search"
            type="text"
            :placeholder="$t('laundry.searchPlaceholder')"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <input
            v-model="historyFilters.start_date"
            type="date"
            :placeholder="$t('laundry.startDate')"
            class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <input
            v-model="historyFilters.end_date"
            type="date"
            :placeholder="$t('laundry.endDate')"
            class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <button
            @click="loadOrders"
            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 whitespace-nowrap"
          >
            {{ $t('laundry.search') }}
          </button>
          <button
            @click="exportOrders"
            :disabled="exporting"
            class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
          >
            {{ exporting ? $t('laundry.exporting') : $t('laundry.exportExcel') }}
          </button>
        </div>

        <!-- Orders Mobile Card View -->
        <div class="block md:hidden bg-white shadow sm:rounded-lg overflow-hidden">
          <div v-if="loadingOrders" class="p-8 text-center text-sm text-gray-500">
            {{ $t('laundry.loading') }}
          </div>
          <div v-else-if="orders.length === 0" class="p-8 text-center text-sm text-gray-500">
            {{ $t('laundry.noOrders') }}
          </div>
          <div v-else>
            <div v-for="order in orders" :key="order.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
              <div class="space-y-2">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900">{{ order.order_number }}</div>
                    <div class="text-sm text-gray-600">{{ order.booking?.booking_number }}</div>
                  </div>
                  <div class="text-right">
                    <div class="font-semibold text-gray-900">Rp {{ parseFloat(order.total_amount).toLocaleString('id-ID') }}</div>
                  </div>
                </div>
                <div class="text-sm space-y-1">
                  <div>
                    <span class="text-gray-500">{{ $t('laundry.guest') }}:</span>
                    <span class="text-gray-900 ml-1">{{ order.booking?.guest?.full_name }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">{{ $t('laundry.weight') }}:</span>
                    <span class="text-gray-900 ml-1">{{ order.weight_kg }} kg</span>
                    <span class="text-gray-500 ml-2">@ Rp {{ parseFloat(order.price_per_kg).toLocaleString('id-ID') }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">{{ $t('laundry.date') }}:</span>
                    <span class="text-gray-900 ml-1">{{ new Date(order.created_at).toLocaleDateString('id-ID') }}</span>
                  </div>
                </div>
                <div class="pt-2">
                  <button
                    @click="deleteOrder(order.id)"
                    class="w-full text-xs px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200"
                  >
                    {{ $t('laundry.delete') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Orders Desktop Table -->
        <div class="hidden md:block bg-white shadow sm:rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $t('laundry.orderNumber') }}</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $t('laundry.booking') }}</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $t('laundry.guest') }}</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $t('laundry.weight') }}</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $t('laundry.pricePerKgShort') }}</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $t('laundry.total') }}</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $t('laundry.date') }}</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $t('laundry.actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-if="loadingOrders">
                <td colspan="8" class="px-3 py-4 text-center text-sm text-gray-500">{{ $t('laundry.loading') }}</td>
              </tr>
              <tr v-else-if="orders.length === 0">
                <td colspan="8" class="px-3 py-4 text-center text-sm text-gray-500">{{ $t('laundry.noOrders') }}</td>
              </tr>
              <tr v-else v-for="order in orders" :key="order.id">
                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                  {{ order.order_number }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  {{ order.booking?.booking_number }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  {{ order.booking?.guest?.full_name }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  {{ order.weight_kg }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  Rp {{ parseFloat(order.price_per_kg).toLocaleString('id-ID') }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  Rp {{ parseFloat(order.total_amount).toLocaleString('id-ID') }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  {{ new Date(order.created_at).toLocaleDateString('id-ID') }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                  <button
                    @click="deleteOrder(order.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    {{ $t('laundry.delete') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="pagination.total > 0" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div class="flex flex-1 justify-between sm:hidden">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ $t('laundry.previous') }}
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ $t('laundry.next') }}
              </button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  {{ $t('laundry.showing') }} <span class="font-medium">{{ pagination.from }}</span> {{ $t('laundry.to') }} <span class="font-medium">{{ pagination.to }}</span> {{ $t('laundry.of') }}
                  <span class="font-medium">{{ pagination.total }}</span> {{ $t('laundry.results') }}
                </p>
              </div>
              <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                  <button
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    @click="changePage(page)"
                    :class="[
                      page === pagination.current_page
                        ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                        : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 text-sm font-semibold'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <button
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LayoutMain from '../components/LayoutMain.vue'
import { bookingApi, laundryOrderApi } from '../api'

const { t } = useI18n()
const activeTab = ref('create')

// Create Order Form
const orderForm = ref({
  booking_id: '',
  weight_kg: '',
  price_per_kg: '',
  notes: ''
})
const isSubmitting = ref(false)
const bookings = ref([])

// Order History
const orders = ref([])
const loadingOrders = ref(false)
const exporting = ref(false)
const historyFilters = ref({
  search: '',
  start_date: '',
  end_date: '',
  page: 1
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

// Calculated total
const calculatedTotal = computed(() => {
  const weight = parseFloat(orderForm.value.weight_kg) || 0
  const price = parseFloat(orderForm.value.price_per_kg) || 0
  return weight * price
})

// Visible pages for pagination
const visiblePages = computed(() => {
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const pages = []
  
  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)
  
  if (end - start < 4) {
    if (start === 1) {
      end = Math.min(last, start + 4)
    } else if (end === last) {
      start = Math.max(1, end - 4)
    }
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

// Load bookings
async function loadBookings() {
  try {
    const response = await bookingApi.getBookings({ status: 'checked_in' })
    bookings.value = response || []
  } catch (error) {
    console.error('Error loading bookings:', error)
    alert(t('laundry.loadBookingsFailed'))
  }
}

// Submit order
async function submitOrder() {
  if (!orderForm.value.booking_id || !orderForm.value.weight_kg || !orderForm.value.price_per_kg) {
    alert(t('laundry.requiredFields'))
    return
  }

  isSubmitting.value = true
  try {
    await laundryOrderApi.createOrder(orderForm.value)
    alert(t('laundry.orderCreated'))
    
    // Reset form
    orderForm.value = {
      booking_id: '',
      weight_kg: '',
      price_per_kg: '',
      notes: ''
    }
    
    // Switch to history tab and reload
    activeTab.value = 'history'
    loadOrders()
  } catch (error) {
    console.error('Error creating order:', error)
    alert(error.response?.data?.message || t('laundry.orderCreateFailed'))
  } finally {
    isSubmitting.value = false
  }
}

// Load orders
async function loadOrders() {
  loadingOrders.value = true
  try {
    const params = {
      page: historyFilters.value.page
    }
    
    if (historyFilters.value.search) {
      params.search = historyFilters.value.search
    }
    
    const response = await laundryOrderApi.getOrders(params)
    orders.value = response.data
    
    // Update pagination
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      per_page: response.per_page,
      total: response.total,
      from: response.from,
      to: response.to
    }
  } catch (error) {
    console.error('Error loading orders:', error)
    alert(t('laundry.loadOrdersFailed'))
  } finally {
    loadingOrders.value = false
  }
}

// Change page
function changePage(page) {
  if (page < 1 || page > pagination.value.last_page) return
  historyFilters.value.page = page
  loadOrders()
}

// Delete order
async function deleteOrder(orderId) {
  if (!confirm(t('laundry.deleteConfirm'))) return
  
  try {
    await laundryOrderApi.deleteOrder(orderId)
    alert(t('laundry.orderDeleted'))
    loadOrders()
  } catch (error) {
    console.error('Error deleting order:', error)
    alert(t('laundry.orderDeleteFailed'))
  }
}

// Export orders
async function exportOrders() {
  exporting.value = true
  try {
    const apiUrl = import.meta.env.VITE_API_BASE_URL || 'https://hotel.tazkia.web.id/api'
    
    // Build query parameters
    const params = new URLSearchParams()
    if (historyFilters.value.start_date) params.append('start_date', historyFilters.value.start_date)
    if (historyFilters.value.end_date) params.append('end_date', historyFilters.value.end_date)
    
    const url = `${apiUrl}/laundry-orders/export?${params.toString()}`
    
    // Create temporary link and trigger download
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', '')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (err) {
    console.error('Failed to export orders:', err)
    alert(t('laundry.exportFailed'))
  } finally {
    exporting.value = false
  }
}

onMounted(() => {
  loadBookings()
  loadOrders()
})
</script>
