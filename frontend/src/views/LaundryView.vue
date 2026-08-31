<template>
  <LayoutMain>
    <div class="px-3 sm:px-4 md:px-6 lg:px-8" @click="handleOutsideClickLaundry">
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
              <!-- Booking Selection Autocomplete -->
              <div class="relative">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                  {{ $t('laundry.booking') }} *
                </label>
                <div class="relative">
                  <input
                    v-model="bookingSearchQuery"
                    @focus="showBookingDropdown = true"
                    type="text"
                    required
                    placeholder="Cari kode booking, nomor kamar, nama tamu, atau hall..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                  <button
                    v-if="orderForm.booking_id || orderForm.hall_booking_id"
                    type="button"
                    @click="clearSelectedBooking"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm font-bold p-1"
                    title="Clear selected booking"
                  >
                    ✕
                  </button>
                </div>

                <!-- Autocomplete Dropdown List -->
                <div
                  v-if="showBookingDropdown && filteredBookingsList.length > 0"
                  class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-64 overflow-y-auto"
                >
                  <!-- Room Bookings Group -->
                  <div v-if="filteredBookingsList.filter(b => b.booking_type === 'room').length > 0">
                    <div class="px-3 py-1.5 bg-blue-50 text-blue-800 font-semibold text-xs uppercase tracking-wider sticky top-0 border-b border-blue-100">
                      🛏️ Booking Kamar
                    </div>
                    <div
                      v-for="b in filteredBookingsList.filter(b => b.booking_type === 'room')"
                      :key="'room-' + b.id"
                      @click="selectBookingItem(b)"
                      class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors"
                    >
                      <div class="flex justify-between items-center">
                        <span class="font-mono font-bold text-gray-900 text-sm">{{ b.booking_number }}</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">
                          {{ b.status === 'checked_in' ? 'Check In' : 'Confirmed' }}
                        </span>
                      </div>
                      <div class="text-xs text-gray-600 mt-1 flex justify-between items-center">
                        <span>👤 {{ b.guest?.name || b.guest?.full_name || b.customer_name || 'Guest' }}</span>
                        <span class="font-medium text-blue-700">Kamar {{ b.rooms?.map(r => r.room_number).join(', ') || b.room?.room_number || '-' }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Hall Bookings Group -->
                  <div v-if="filteredBookingsList.filter(b => b.booking_type === 'hall').length > 0">
                    <div class="px-3 py-1.5 bg-purple-50 text-purple-800 font-semibold text-xs uppercase tracking-wider sticky top-0 border-b border-purple-100">
                      🎪 Booking Hall
                    </div>
                    <div
                      v-for="b in filteredBookingsList.filter(b => b.booking_type === 'hall')"
                      :key="'hall-' + b.id"
                      @click="selectBookingItem(b)"
                      class="px-4 py-2.5 hover:bg-purple-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors"
                    >
                      <div class="flex justify-between items-center">
                        <span class="font-mono font-bold text-gray-900 text-sm">{{ b.booking_number }}</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800 font-medium">
                          {{ b.status === 'checked_in' ? 'Check In' : 'Confirmed' }}
                        </span>
                      </div>
                      <div class="text-xs text-gray-600 mt-1 flex justify-between items-center">
                        <span>👤 {{ b.customer_name || b.guest?.name || b.guest?.full_name || 'Customer' }}</span>
                        <span class="font-medium text-purple-700">Hall: {{ b.hall?.name || '-' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- No match state -->
                <div
                  v-if="showBookingDropdown && bookingSearchQuery.trim() && filteredBookingsList.length === 0"
                  class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl p-4 text-center text-sm text-gray-500"
                >
                  Tidak ditemukan booking kamar atau hall dengan kata kunci "{{ bookingSearchQuery }}"
                </div>
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
                  :disabled="isSubmitting || (!orderForm.booking_id && !orderForm.hall_booking_id)"
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
                    <div class="text-sm text-gray-600">{{ getOrderBookingNumber(order) }}</div>
                  </div>
                  <div class="text-right">
                    <div class="font-semibold text-gray-900">Rp {{ parseFloat(order.total_amount).toLocaleString('id-ID') }}</div>
                  </div>
                </div>
                <div class="text-sm space-y-1">
                  <div>
                    <span class="text-gray-500">{{ $t('laundry.guest') }}:</span>
                    <span class="text-gray-900 ml-1">{{ getOrderGuestName(order) }}</span>
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
                  {{ getOrderBookingNumber(order) }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  {{ getOrderGuestName(order) }}
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
import { bookingApi, hallBookingApi, laundryOrderApi } from '../api'

const { t } = useI18n()
const activeTab = ref('create')

// Create Order Form & Booking Autocomplete
const bookingSearchQuery = ref('')
const showBookingDropdown = ref(false)
const orderForm = ref({
  booking_id: null,
  hall_booking_id: null,
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

// Filtered bookings for autocomplete search (by booking number, room number, guest name, or hall name)
const filteredBookingsList = computed(() => {
  const query = bookingSearchQuery.value.trim().toLowerCase()
  if (!query) return bookings.value

  return bookings.value.filter(b => {
    const bookingNum = (b.booking_number || '').toLowerCase()
    const guestName = (b.guest?.name || b.guest?.full_name || b.customer_name || '').toLowerCase()
    const roomNumbers = b.booking_type === 'room'
      ? (b.rooms?.map(r => r.room_number).join(', ') || b.room?.room_number || '').toString().toLowerCase()
      : ''
    const hallName = b.booking_type === 'hall' ? (b.hall?.name || '').toLowerCase() : ''

    return bookingNum.includes(query) ||
           guestName.includes(query) ||
           roomNumbers.includes(query) ||
           hallName.includes(query)
  })
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

function selectBookingItem(b) {
  if (b.booking_type === 'hall') {
    orderForm.value.hall_booking_id = b.id
    orderForm.value.booking_id = null
    const name = b.customer_name || b.guest?.name || b.guest?.full_name || 'Guest'
    const hall = b.hall?.name || 'Hall'
    bookingSearchQuery.value = `${b.booking_number} — ${name} (${hall})`
  } else {
    orderForm.value.booking_id = b.id
    orderForm.value.hall_booking_id = null
    const name = b.guest?.name || b.guest?.full_name || b.customer_name || 'Guest'
    const rooms = b.rooms?.map(r => r.room_number).join(', ') || b.room?.room_number || '-'
    bookingSearchQuery.value = `${b.booking_number} — ${name} (Kamar ${rooms})`
  }
  showBookingDropdown.value = false
}

function clearSelectedBooking() {
  orderForm.value.booking_id = null
  orderForm.value.hall_booking_id = null
  bookingSearchQuery.value = ''
  showBookingDropdown.value = true
}

function handleOutsideClickLaundry(e) {
  if (!e.target.closest('.relative')) {
    showBookingDropdown.value = false
  }
}

// Load bookings (Room & Hall bookings in confirmed or checked_in status)
async function loadBookings() {
  try {
    const allBookings = []

    // Room bookings (confirmed & checked_in)
    const roomConfirmed = await bookingApi.getBookings({ status: 'confirmed' })
    const roomCheckedIn = await bookingApi.getBookings({ status: 'checked_in' })
    const roomConfirmedData = Array.isArray(roomConfirmed) ? roomConfirmed : (roomConfirmed?.data || [])
    const roomCheckedInData = Array.isArray(roomCheckedIn) ? roomCheckedIn : (roomCheckedIn?.data || [])

    const roomMap = new Map()
    ;[...roomConfirmedData, ...roomCheckedInData].forEach(b => roomMap.set(b.id, b))
    const roomBookings = Array.from(roomMap.values()).map(b => ({ ...b, booking_type: 'room' }))
    allBookings.push(...roomBookings)

    // Hall bookings (confirmed & checked_in)
    const hallConfirmed = await hallBookingApi.getHallBookings({ status: 'confirmed' })
    const hallCheckedIn = await hallBookingApi.getHallBookings({ status: 'checked_in' })
    const hallConfirmedData = Array.isArray(hallConfirmed) ? hallConfirmed : (hallConfirmed?.data || [])
    const hallCheckedInData = Array.isArray(hallCheckedIn) ? hallCheckedIn : (hallCheckedIn?.data || [])

    const hallMap = new Map()
    ;[...hallConfirmedData, ...hallCheckedInData].forEach(b => hallMap.set(b.id, b))
    const hallBookings = Array.from(hallMap.values()).map(b => ({ ...b, booking_type: 'hall' }))
    allBookings.push(...hallBookings)

    bookings.value = allBookings
  } catch (error) {
    console.error('Error loading bookings:', error)
    alert(t('laundry.loadBookingsFailed'))
  }
}

// Submit order
async function submitOrder() {
  if ((!orderForm.value.booking_id && !orderForm.value.hall_booking_id) || !orderForm.value.weight_kg || !orderForm.value.price_per_kg) {
    alert(t('laundry.requiredFields'))
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      weight_kg: orderForm.value.weight_kg,
      price_per_kg: orderForm.value.price_per_kg,
      notes: orderForm.value.notes
    }
    if (orderForm.value.booking_id) payload.booking_id = orderForm.value.booking_id
    if (orderForm.value.hall_booking_id) payload.hall_booking_id = orderForm.value.hall_booking_id

    await laundryOrderApi.createOrder(payload)
    alert(t('laundry.orderCreated'))
    
    // Reset form
    clearSelectedBooking()
    orderForm.value = {
      booking_id: null,
      hall_booking_id: null,
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

function getOrderBookingNumber(order) {
  if (order.hall_booking || order.hallBooking) {
    return (order.hall_booking || order.hallBooking).booking_number + ' (Hall)'
  }
  return order.booking?.booking_number ? order.booking.booking_number + ' (Room)' : '-'
}

function getOrderGuestName(order) {
  if (order.hall_booking || order.hallBooking) {
    const hb = order.hall_booking || order.hallBooking
    return hb.customer_name || hb.guest?.name || hb.guest?.full_name || '-'
  }
  return order.booking?.guest?.name || order.booking?.guest?.full_name || order.booking?.customer_name || '-'
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
    const apiUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
    
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
