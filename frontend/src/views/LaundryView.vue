<template>
  <LayoutMain>
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-2xl font-semibold leading-6 text-gray-900">Laundry Service</h1>
          <p class="mt-2 text-sm text-gray-700">Manage laundry orders for hotel guests</p>
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
            Create Order
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
            Order History
          </button>
        </nav>
      </div>

      <!-- Create Order Tab -->
      <div v-show="activeTab === 'create'" class="mt-6">
        <div class="bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">New Laundry Order</h3>
            <form @submit.prevent="submitOrder" class="space-y-4">
              <!-- Booking Selection -->
              <div>
                <label for="booking" class="block text-sm font-medium text-gray-700">Booking</label>
                <select
                  id="booking"
                  v-model="orderForm.booking_id"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                  <option value="">Select Booking</option>
                  <option v-for="booking in bookings" :key="booking.id" :value="booking.id">
                    {{ booking.booking_number }} - {{ booking.guest?.full_name }} (Room {{ booking.room?.room_number }})
                  </option>
                </select>
              </div>

              <!-- Weight (kg) -->
              <div>
                <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                <input
                  id="weight"
                  type="number"
                  v-model.number="orderForm.weight_kg"
                  min="0.1"
                  step="0.1"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="0.0"
                />
              </div>

              <!-- Price per kg -->
              <div>
                <label for="price_per_kg" class="block text-sm font-medium text-gray-700">Price per kg (Rp)</label>
                <input
                  id="price_per_kg"
                  type="number"
                  v-model.number="orderForm.price_per_kg"
                  min="0"
                  step="1000"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="0"
                />
              </div>

              <!-- Total Amount (calculated) -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                <div class="mt-1 block w-full rounded-md bg-gray-50 px-3 py-2 text-gray-900 sm:text-sm border border-gray-300">
                  Rp {{ calculatedTotal.toLocaleString('id-ID') }}
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                <textarea
                  id="notes"
                  v-model="orderForm.notes"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="Additional notes..."
                ></textarea>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ isSubmitting ? 'Creating...' : 'Create Order' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Order History Tab -->
      <div v-show="activeTab === 'history'" class="mt-6">
        <!-- Filters -->
        <div class="mb-4 flex gap-4">
          <input
            v-model="historyFilters.search"
            type="text"
            placeholder="Search by order # or booking #"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <button
            @click="loadOrders"
            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
          >
            Search
          </button>
        </div>

        <!-- Orders Table -->
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Order #</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Booking</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Guest</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Weight (kg)</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Price/kg</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-if="loadingOrders">
                <td colspan="8" class="px-3 py-4 text-center text-sm text-gray-500">Loading...</td>
              </tr>
              <tr v-else-if="orders.length === 0">
                <td colspan="8" class="px-3 py-4 text-center text-sm text-gray-500">No orders found</td>
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
                    Delete
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
                Previous
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing <span class="font-medium">{{ pagination.from }}</span> to <span class="font-medium">{{ pagination.to }}</span> of
                  <span class="font-medium">{{ pagination.total }}</span> results
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
import LayoutMain from '../components/LayoutMain.vue'
import { bookingApi, laundryOrderApi } from '../api'

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
const historyFilters = ref({
  search: '',
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
    bookings.value = response.data
  } catch (error) {
    console.error('Error loading bookings:', error)
    alert('Failed to load bookings')
  }
}

// Submit order
async function submitOrder() {
  if (!orderForm.value.booking_id || !orderForm.value.weight_kg || !orderForm.value.price_per_kg) {
    alert('Please fill in all required fields')
    return
  }

  isSubmitting.value = true
  try {
    await laundryOrderApi.createOrder(orderForm.value)
    alert('Laundry order created successfully')
    
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
    alert(error.response?.data?.message || 'Failed to create order')
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
    alert('Failed to load orders')
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
  if (!confirm('Are you sure you want to delete this order?')) return
  
  try {
    await laundryOrderApi.deleteOrder(orderId)
    alert('Order deleted successfully')
    loadOrders()
  } catch (error) {
    console.error('Error deleting order:', error)
    alert('Failed to delete order')
  }
}

onMounted(() => {
  loadBookings()
  loadOrders()
})
</script>
