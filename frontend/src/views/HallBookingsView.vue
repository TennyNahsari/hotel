<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Hall Bookings</h1>
          <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage hall reservations and events</p>
        </div>
        <button
          @click="openAddModal"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + New Booking
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search bookings..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hall</label>
            <select
              v-model="filters.hall_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Halls</option>
              <option v-for="hall in availableHalls" :key="hall.id" :value="hall.id">{{ hall.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Event Date</label>
            <input
              v-model="filters.event_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Bookings Table -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">Loading bookings...</p>
      </div>

      <div v-else-if="bookings.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">No bookings found</p>
      </div>

      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Booking #
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Hall
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Event Name
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Customer
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date & Time
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total
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
              <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ booking.booking_number }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ booking.hall?.name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ booking.event_name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ booking.customer_name }}</div>
                  <div class="text-xs text-gray-500">{{ booking.customer_email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(booking.event_date) }}</div>
                  <div class="text-xs text-gray-500">{{ booking.start_time }} - {{ booking.end_time }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ formatCurrency(booking.total_amount) }}</div>
                  <div class="text-xs text-gray-500">{{ booking.duration_hours}} hrs</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="{
                      'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                      'bg-green-100 text-green-800': booking.status === 'confirmed',
                      'bg-gray-100 text-gray-800': booking.status === 'completed',
                      'bg-red-100 text-red-800': booking.status === 'cancelled',
                    }"
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  >
                    {{ booking.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex gap-2">
                    <button
                      v-if="booking.status === 'pending'"
                      @click="confirmBooking(booking)"
                      class="text-green-600 hover:text-green-900"
                      title="Confirm"
                    >
                      Confirm
                    </button>
                    <button
                      v-if="booking.status === 'confirmed'"
                      @click="completeBooking(booking)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Complete"
                    >
                      Complete
                    </button>
                    <button
                      v-if="['pending', 'confirmed'].includes(booking.status)"
                      @click="cancelBooking(booking)"
                      class="text-red-600 hover:text-red-900"
                      title="Cancel"
                    >
                      Cancel
                    </button>
                    <button
                      @click="viewBooking(booking)"
                      class="text-gray-600 hover:text-gray-900"
                      title="View"
                    >
                      View
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 15" class="bg-white rounded-lg shadow p-4 flex justify-between items-center">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} bookings
        </div>
        <div class="flex gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
          >
            Previous
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Booking Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-4xl w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? 'Edit Booking' : 'New Hall Booking' }}
        </h2>

        <form @submit.prevent="saveBooking" class="space-y-6">
          <!-- Hall Selection -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Hall & Schedule</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hall *</label>
                <select
                  v-model="formData.hallId"
                  required
                  @change="onHallChange"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Select Hall</option>
                  <option v-for="hall in availableHalls" :key="hall.id" :value="hall.id">
                    {{ hall.name }} ({{ hall.capacity }} pax - {{ formatCurrency(hall.price_per_hour) }}/hr)
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Date *</label>
                <input
                  v-model="formData.event_date"
                  type="date"
                  required
                  :min="getTodayDate()"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                <input
                  v-model="formData.start_time"
                  type="time"
                  required
                  @change="calculateTotal"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                <input
                  v-model="formData.end_time"
                  type="time"
                  required
                  @change="calculateTotal"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div class="md:col-span-2 bg-blue-50 p-3 rounded-lg" v-if="selectedHall && calculatedDuration">
                <div class="text-sm text-gray-700">
                  <strong>Duration:</strong> {{ calculatedDuration }} hours | 
                  <strong>Total:</strong> {{ formatCurrency(calculatedTotal) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Customer Information -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Customer Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
                <input
                  v-model="formData.customer_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input
                  v-model="formData.customer_email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                <input
                  v-model="formData.customer_phone"
                  type="tel"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                <input
                  v-model="formData.customer_company"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Event Details -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Event Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Name *</label>
                <input
                  v-model="formData.event_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g., Annual Meeting 2026"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Attendees *</label>
                <input
                  v-model.number="formData.attendees"
                  type="number"
                  required
                  min="1"
                  :max="selectedHall?.capacity"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="selectedHall" class="text-xs text-gray-500 mt-1">
                  Max capacity: {{ selectedHall.capacity }} persons
                </p>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Special Requests</label>
                <textarea
                  v-model="formData.special_requests"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  placeholder="Any special requirements..."
                ></textarea>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
                <textarea
                  v-model="formData.notes"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  placeholder="Internal staff notes..."
                ></textarea>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create Booking') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Booking Modal -->
    <div
      v-if="showViewModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeViewModal"
    >
      <div class="bg-white rounded-lg max-w-3xl w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          Booking Details: {{ viewData.booking_number }}
        </h2>

        <div class="space-y-6">
          <div>
            <h3 class="font-semibold text-gray-900 mb-2">Hall Information</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-gray-500">Hall</p>
                <p class="font-medium">{{ viewData.hall?.name }}</p>
              </div>
              <div>
                <p class="text-gray-500">Event Date</p>
                <p class="font-medium">{{ formatDate(viewData.event_date) }}</p>
              </div>
              <div>
                <p class="text-gray-500">Time</p>
                <p class="font-medium">{{ viewData.start_time }} - {{ viewData.end_time }} ({{ viewData.duration_hours }} hrs)</p>
              </div>
              <div>
                <p class="text-gray-500">Status</p>
                <p class="font-medium capitalize">{{ viewData.status }}</p>
              </div>
            </div>
          </div>

          <div>
            <h3 class="font-semibold text-gray-900 mb-2">Event Details</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div class="col-span-2">
                <p class="text-gray-500">Event Name</p>
                <p class="font-medium">{{ viewData.event_name }}</p>
              </div>
              <div>
                <p class="text-gray-500">Attendees</p>
                <p class="font-medium">{{ viewData.attendees }} persons</p>
              </div>
            </div>
          </div>

          <div>
            <h3 class="font-semibold text-gray-900 mb-2">Customer Information</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-gray-500">Name</p>
                <p class="font-medium">{{ viewData.customer_name }}</p>
              </div>
              <div>
                <p class="text-gray-500">Email</p>
                <p class="font-medium">{{ viewData.customer_email }}</p>
              </div>
              <div>
                <p class="text-gray-500">Phone</p>
                <p class="font-medium">{{ viewData.customer_phone }}</p>
              </div>
              <div v-if="viewData.customer_company">
                <p class="text-gray-500">Company</p>
                <p class="font-medium">{{ viewData.customer_company }}</p>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex justify-between items-center text-lg">
              <span class="font-semibold">Total Amount</span>
              <span class="font-bold text-blue-600">{{ formatCurrency(viewData.total_amount) }}</span>
            </div>
          </div>

          <div v-if="viewData.special_requests">
            <h3 class="font-semibold text-gray-900 mb-2">Special Requests</h3>
            <p class="text-gray-700 text-sm">{{ viewData.special_requests }}</p>
          </div>

          <div v-if="viewData.notes">
            <h3 class="font-semibold text-gray-900 mb-2">Internal Notes</h3>
            <p class="text-gray-700 text-sm">{{ viewData.notes }}</p>
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
import { ref, computed, onMounted, watch } from 'vue'
import { hallApi, hallBookingApi } from '@/api'
import LayoutMain from '@/components/LayoutMain.vue'

const bookings = ref([])
const availableHalls = ref([])
const selectedHall = ref(null)
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
  hall_id: '',
  status: '',
  event_date: ''
})

const formData = ref({
  hallId: '',
  event_date: '',
  start_time: '',
  end_time: '',
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  customer_company: '',
  event_name: '',
  attendees: null,
  special_requests: '',
  notes: ''
})

const viewData = ref({})
const editingId = ref(null)
const calculatedDuration = ref(0)
const calculatedTotal = ref(0)

// Fetch bookings
const fetchBookings = async (page = 1) => {
  loading.value = true
  try {
    const params = { page, per_page: 15, ...filters.value }
    const response = await hallBookingApi.getHallBookings(params)
    console.log('Hall Bookings API Response:', response)
    
    // Handle both array and paginated response
    if (Array.isArray(response)) {
      bookings.value = response
      pagination.value = {
        current_page: 1,
        last_page: 1,
        total: response.length,
        from: 1,
        to: response.length
      }
    } else {
      bookings.value = response.data || response
      pagination.value = {
        current_page: response.current_page || 1,
        last_page: response.last_page || 1,
        total: response.total || bookings.value.length,
        from: response.from || 1,
        to: response.to || bookings.value.length
      }
    }
  } catch (error) {
    console.error('Error fetching bookings:', error)
    alert('Failed to fetch bookings')
    bookings.value = []
  } finally {
    loading.value = false
  }
}

// Fetch halls
const fetchHalls = async () => {
  try {
    const response = await hallApi.getHalls({ status: 'available', per_page: 100 })
    availableHalls.value = response.data
  } catch (error) {
    console.error('Error fetching halls:', error)
  }
}

// Open add modal
const openAddModal = () => {
  isEditing.value = false
  resetForm()
  showModal.value = true
}

// Reset form
const resetForm = () => {
  formData.value = {
    hallId: '',
    event_date: '',
    start_time: '',
    end_time: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    customer_company: '',
    event_name: '',
    attendees: null,
    special_requests: '',
    notes: ''
  }
  selectedHall.value = null
  calculatedDuration.value = 0
  calculatedTotal.value = 0
}

// Close modal
const closeModal = () => {
  showModal.value = false
  editingId.value = null
}

// On hall change
const onHallChange = () => {
  selectedHall.value = availableHalls.value.find(h => h.id == formData.value.hallId)
  calculateTotal()
}

// Calculate duration and total
const calculateTotal = () => {
  if (!formData.value.start_time || !formData.value.end_time || !selectedHall.value) {
    return
  }

  const start = new Date(`2000-01-01 ${formData.value.start_time}`)
  const end = new Date(`2000-01-01 ${formData.value.end_time}`)
  const hours = (end - start) / (1000 * 60 * 60)
  
  if (hours > 0) {
    calculatedDuration.value = hours.toFixed(2)
    calculatedTotal.value = hours * selectedHall.value.price_per_hour
  }
}

// Save booking
const saveBooking = async () => {
  if (!selectedHall.value) {
    alert('Please select a hall')
    return
  }

  if (formData.value.attendees > selectedHall.value.capacity) {
    alert(`Attendees cannot exceed hall capacity of ${selectedHall.value.capacity}`)
    return
  }

  saving.value = true
  try {
    const data = {
      hall_id: formData.value.hallId,
      event_date: formData.value.event_date,
      start_time: formData.value.start_time,
      end_time: formData.value.end_time,
      customer_name: formData.value.customer_name,
      customer_email: formData.value.customer_email,
      customer_phone: formData.value.customer_phone,
      customer_company: formData.value.customer_company,
      event_name: formData.value.event_name,
      attendees: formData.value.attendees,
      special_requests: formData.value.special_requests,
      notes: formData.value.notes
    }

    if (isEditing.value) {
      await hallBookingApi.updateHallBooking(editingId.value, data)
      alert('Booking updated successfully')
    } else {
      await hallBookingApi.createHallBooking(data)
      alert('Booking created successfully')
    }

    closeModal()
    fetchBookings(pagination.value.current_page)
  } catch (error) {
    console.error('Error saving booking:', error)
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      alert(errors.join('\n'))
    } else {
      alert('Failed to save booking')
    }
  } finally {
    saving.value = false
  }
}

// Confirm booking
const confirmBooking = async (booking) => {
  if (confirm(`Confirm booking ${booking.booking_number}?`)) {
    try {
      await hallBookingApi.confirmHallBooking(booking.id)
      alert('Booking confirmed successfully')
      fetchBookings(pagination.value.current_page)
    } catch (error) {
      console.error('Error confirming booking:', error)
      alert('Failed to confirm booking')
    }
  }
}

// Complete booking
const completeBooking = async (booking) => {
  if (confirm(`Mark booking ${booking.booking_number} as completed?`)) {
    try {
      await hallBookingApi.completeHallBooking(booking.id)
      alert('Booking completed successfully')
      fetchBookings(pagination.value.current_page)
    } catch (error) {
      console.error('Error completing booking:', error)
      alert('Failed to complete booking')
    }
  }
}

// Cancel booking
const cancelBooking = async (booking) => {
  if (confirm(`Cancel booking ${booking.booking_number}?`)) {
    try {
      await hallBookingApi.cancelHallBooking(booking.id)
      alert('Booking cancelled successfully')
      fetchBookings(pagination.value.current_page)
    } catch (error) {
      console.error('Error cancelling booking:', error)
      alert('Failed to cancel booking')
    }
  }
}

// View booking
const viewBooking = (booking) => {
  viewData.value = booking
  showViewModal.value = true
}

// Close view modal
const closeViewModal = () => {
  showViewModal.value = false
  viewData.value = {}
}

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchBookings(page)
  }
}

// Get today date
const getTodayDate = () => {
  return new Date().toISOString().split('T')[0]
}

// Format date
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
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
  fetchBookings(1)
}, { deep: true })

// Initial fetch
onMounted(() => {
  fetchBookings()
  fetchHalls()
})
</script>
