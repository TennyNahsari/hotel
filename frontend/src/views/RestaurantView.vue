<template>
  <LayoutMain>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Restaurant & Cafe</h1>
        <p class="text-gray-600">Manage menu items and create orders</p>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'menu'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'menu'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Menu Items
          </button>
          <button
            @click="activeTab = 'orders'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'orders'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Create Order
          </button>
          <button
            @click="activeTab = 'history'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'history'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Order History
          </button>
        </nav>
      </div>

      <!-- Menu Items Tab -->
      <div v-show="activeTab === 'menu'">
        <!-- Filters and Actions -->
        <div class="mb-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <div class="flex flex-col sm:flex-row gap-4 flex-1">
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search menu..."
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <select
              v-model="filters.category"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Categories</option>
              <option value="food">Food</option>
              <option value="beverage">Beverage</option>
              <option value="snack">Snack</option>
            </select>
            <select
              v-model="filters.is_available"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Status</option>
              <option value="1">Available</option>
              <option value="0">Unavailable</option>
            </select>
          </div>
          <button
            @click="openMenuItemModal()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Add Menu Item
          </button>
        </div>

        <!-- Menu Items Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in menuItems.data" :key="item.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <img 
                    v-if="item.photo" 
                    :src="`${apiUrl}/storage/${item.photo}`" 
                    alt="Menu photo"
                    class="h-12 w-12 rounded object-cover"
                  />
                  <div v-else class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400 text-xs">No photo</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                  <div class="text-sm text-gray-500">{{ item.description }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getCategoryBadgeClass(item.category)">
                    {{ item.category }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  Rp {{ formatNumber(item.price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="item.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ item.is_available ? 'Available' : 'Unavailable' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button @click="openMenuItemModal(item)" class="text-blue-600 hover:text-blue-900 mr-3">
                    Edit
                  </button>
                  <button @click="deleteMenuItem(item)" class="text-red-600 hover:text-red-900">
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="menuItems.meta" class="mt-4 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ menuItems.meta.from }} to {{ menuItems.meta.to }} of {{ menuItems.meta.total }} results
          </div>
          <div class="flex gap-2">
            <button
              v-for="link in menuItems.meta.links"
              :key="link.label"
              @click="changePage(link.url)"
              :disabled="!link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                !link.url && 'opacity-50 cursor-not-allowed'
              ]"
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>

      <!-- Create Order Tab -->
      <div v-show="activeTab === 'orders'">
        <div class="bg-white rounded-lg shadow p-6">
          <!-- Select Booking -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Booking</label>
            <select
              v-model="orderForm.booking_id"
              @change="loadBookingDetails"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
              <option value="">Select a booking...</option>
              <option v-for="booking in bookings" :key="booking.id" :value="booking.id">
                {{ booking.booking_number }} - {{ booking.guest?.name }} (Room {{ booking.rooms?.[0]?.room_number }})
              </option>
            </select>
            <p v-if="bookings.length === 0" class="text-sm text-gray-500 mt-1">
              No checked-in bookings available. Please check in a booking first.
            </p>
          </div>

          <!-- Booking Details (if booking selected) -->
          <div v-if="selectedBooking" class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-2">Booking Details</h3>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div><span class="text-gray-600">Guest:</span> {{ selectedBooking.guest?.name }}</div>
              <div><span class="text-gray-600">Room:</span> {{ selectedBooking.rooms?.[0]?.room_number }}</div>
              <div><span class="text-gray-600">Check-in:</span> {{ formatDate(selectedBooking.check_in_date) }}</div>
              <div><span class="text-gray-600">Check-out:</span> {{ formatDate(selectedBooking.check_out_date) }}</div>
            </div>
          </div>

          <!-- Menu Items Selection -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Add Items to Order</label>
            <p v-if="availableMenuItems.length === 0" class="text-sm text-gray-500 mb-2">
              No available menu items. Please add menu items first.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="item in availableMenuItems"
                :key="item.id"
                class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors"
              >
                <img
                  v-if="item.photo"
                  :src="`${apiUrl}/storage/${item.photo}`"
                  alt="Menu photo"
                  class="w-full h-32 object-cover rounded mb-2"
                />
                <div v-else class="w-full h-32 rounded bg-gray-200 flex items-center justify-center mb-2">
                  <span class="text-gray-400">No photo</span>
                </div>
                <h4 class="font-medium text-gray-900">{{ item.name }}</h4>
                <p class="text-sm text-gray-500 mb-2">{{ item.description }}</p>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-blue-600 font-medium">Rp {{ formatNumber(item.price) }}</span>
                  <span :class="getCategoryBadgeClass(item.category)">
                    {{ item.category }}
                  </span>
                </div>
                <button
                  @click="addItemToCart(item)"
                  class="w-full px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm"
                >
                  Add to Cart
                </button>
              </div>
            </div>
          </div>

          <!-- Cart -->
          <div v-if="orderForm.items.length > 0" class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
            <div class="bg-gray-50 rounded-lg p-4">
              <div v-for="(cartItem, index) in orderForm.items" :key="index" class="flex items-center justify-between mb-3 last:mb-0">
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ getMenuItemName(cartItem.menu_item_id) }}</div>
                  <div class="text-sm text-gray-500">Rp {{ formatNumber(cartItem.price) }} each</div>
                </div>
                <div class="flex items-center gap-3">
                  <button
                    @click="updateQuantity(index, -1)"
                    class="w-8 h-8 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center"
                  >
                    -
                  </button>
                  <span class="w-8 text-center font-medium">{{ cartItem.quantity }}</span>
                  <button
                    @click="updateQuantity(index, 1)"
                    class="w-8 h-8 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center"
                  >
                    +
                  </button>
                  <button
                    @click="removeItemFromCart(index)"
                    class="ml-2 text-red-600 hover:text-red-800"
                  >
                    Remove
                  </button>
                  <div class="ml-4 font-medium text-gray-900 w-32 text-right">
                    Rp {{ formatNumber(cartItem.price * cartItem.quantity) }}
                  </div>
                </div>
              </div>
              <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex justify-between items-center text-lg font-bold">
                  <span>Total:</span>
                  <span class="text-blue-600">Rp {{ formatNumber(calculateTotal()) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Order Notes (Optional)</label>
            <textarea
              v-model="orderForm.notes"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Special requests or notes..."
            ></textarea>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end gap-3">
            <button
              @click="resetOrderForm"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Reset
            </button>
            <button
              @click="submitOrder"
              :disabled="!orderForm.booking_id || orderForm.items.length === 0 || submitting"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submitting ? 'Creating...' : 'Create Order' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Order History Tab -->
      <div v-show="activeTab === 'history'">
        <!-- Filters -->
        <div class="mb-4 flex flex-col sm:flex-row gap-4">
          <input
            v-model="orderFilters.search"
            type="text"
            placeholder="Search by order number or booking..."
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <select
            v-model="orderFilters.status"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="preparing">Preparing</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <button
            @click="loadOrders()"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Refresh
          </button>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in orders.data" :key="order.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ order.order_number }}
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ order.booking?.booking_number }}</div>
                  <div class="text-sm text-gray-500">{{ order.booking?.guest?.name }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ order.order_items?.length || 0 }} items
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  Rp {{ formatNumber(order.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getOrderStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(order.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button
                    v-if="order.status === 'pending'"
                    @click="updateOrderStatus(order.id, 'preparing')"
                    class="text-blue-600 hover:text-blue-900 mr-2"
                  >
                    Prepare
                  </button>
                  <button
                    v-if="order.status === 'preparing'"
                    @click="updateOrderStatus(order.id, 'delivered')"
                    class="text-green-600 hover:text-green-900 mr-2"
                  >
                    Deliver
                  </button>
                  <button
                    v-if="['pending', 'preparing'].includes(order.status)"
                    @click="updateOrderStatus(order.id, 'cancelled')"
                    class="text-red-600 hover:text-red-900"
                  >
                    Cancel
                  </button>
                  <button
                    @click="viewOrderDetails(order)"
                    class="text-gray-600 hover:text-gray-900 ml-2"
                  >
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="orders.meta" class="mt-4 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ orders.meta.from }} to {{ orders.meta.to }} of {{ orders.meta.total }} results
          </div>
          <div class="flex gap-2">
            <button
              v-for="link in orders.meta.links"
              :key="link.label"
              @click="changeOrderPage(link.url)"
              :disabled="!link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                !link.url && 'opacity-50 cursor-not-allowed'
              ]"
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Menu Item Modal -->
    <div
      v-if="showMenuItemModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeMenuItemModal"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
          {{ editingMenuItem ? 'Edit Menu Item' : 'Add Menu Item' }}
        </h2>
        <form @submit.prevent="saveMenuItem">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
            <input
              v-model="menuItemForm.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
            <select
              v-model="menuItemForm.category"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="food">Food</option>
              <option value="beverage">Beverage</option>
              <option value="snack">Snack</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
            <input
              v-model.number="menuItemForm.price"
              type="number"
              step="0.01"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="menuItemForm.description"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            ></textarea>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
            <input
              @change="handlePhotoChange"
              type="file"
              accept="image/*"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <img 
              v-if="menuItemForm.photo && typeof menuItemForm.photo === 'string'" 
              :src="`${apiUrl}/storage/${menuItemForm.photo}`" 
              alt="Current photo"
              class="mt-2 h-32 w-32 object-cover rounded"
            />
          </div>
          <div class="mb-6">
            <label class="flex items-center">
              <input
                v-model="menuItemForm.is_available"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">Available</span>
            </label>
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="closeMenuItemModal"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ submitting ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import LayoutMain from '../components/LayoutMain.vue'
import { menuItemApi, restaurantOrderApi, bookingApi } from '../api'

const apiUrl = import.meta.env.VITE_API_URL || 'https://hotel.tazkia.web.id'

// Tabs
const activeTab = ref('menu')

// Menu Items
const menuItems = ref({ data: [], meta: null })
const filters = reactive({
  search: '',
  category: '',
  is_available: ''
})
const showMenuItemModal = ref(false)
const editingMenuItem = ref(null)
const menuItemForm = reactive({
  name: '',
  category: 'food',
  price: 0,
  description: '',
  photo: null,
  is_available: true
})

// Orders
const bookings = ref([])
const availableMenuItems = ref([])
const selectedBooking = ref(null)
const orderForm = reactive({
  booking_id: '',
  items: [],
  notes: ''
})
const submitting = ref(false)

// Order History
const orders = ref({ data: [], meta: null })
const orderFilters = reactive({
  search: '',
  status: ''
})

// Load menu items
const loadMenuItems = async (url = null) => {
  try {
    const params = { ...filters }
    if (url) {
      const urlObj = new URL(url)
      const page = urlObj.searchParams.get('page')
      if (page) params.page = page
    }
    console.log('Loading menu items with params:', params)
    const response = await menuItemApi.getMenuItems(params)
    console.log('Menu items response:', response)
    menuItems.value = response
    console.log('Menu items loaded:', menuItems.value?.data?.length || 0)
  } catch (error) {
    console.error('Failed to load menu items:', error)
    alert('Failed to load menu items')
  }
}

// Watch filters
watch(filters, () => {
  loadMenuItems()
})

// Pagination
const changePage = (url) => {
  if (url) loadMenuItems(url)
}

// Menu Item Modal
const openMenuItemModal = (item = null) => {
  if (item) {
    editingMenuItem.value = item
    Object.assign(menuItemForm, {
      name: item.name,
      category: item.category,
      price: item.price,
      description: item.description || '',
      photo: item.photo,
      is_available: item.is_available
    })
  } else {
    editingMenuItem.value = null
    Object.assign(menuItemForm, {
      name: '',
      category: 'food',
      price: 0,
      description: '',
      photo: null,
      is_available: true
    })
  }
  showMenuItemModal.value = true
}

const closeMenuItemModal = () => {
  showMenuItemModal.value = false
  editingMenuItem.value = null
}

const handlePhotoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    menuItemForm.photo = file
  }
}

const saveMenuItem = async () => {
  try {
    submitting.value = true
    if (editingMenuItem.value) {
      await menuItemApi.updateMenuItem(editingMenuItem.value.id, menuItemForm)
      alert('Menu item updated successfully')
    } else {
      await menuItemApi.createMenuItem(menuItemForm)
      alert('Menu item created successfully')
    }
    closeMenuItemModal()
    loadMenuItems()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save menu item')
  } finally {
    submitting.value = false
  }
}

const deleteMenuItem = async (item) => {
  if (!confirm(`Delete ${item.name}?`)) return
  try {
    await menuItemApi.deleteMenuItem(item.id)
    alert('Menu item deleted successfully')
    loadMenuItems()
  } catch (error) {
    alert('Failed to delete menu item')
  }
}

// Orders
const loadBookings = async () => {
  try {
    console.log('Loading all bookings for debug...')
    const allBookings = await bookingApi.getBookings({})
    console.log('All bookings:', allBookings.length, allBookings.map(b => ({ id: b.id, number: b.booking_number, status: b.status })))
    
    console.log('Loading bookings with status: checked_in')
    const response = await bookingApi.getBookings({ status: 'checked_in' })
    console.log('Checked-in bookings raw response:', response)
    // bookingApi.getBookings() already returns response.data which is array
    bookings.value = response || []
    console.log('Checked-in bookings loaded:', bookings.value.length, bookings.value)
  } catch (error) {
    console.error('Failed to load bookings:', error, error.response)
    alert('Failed to load bookings: ' + (error.response?.data?.message || error.message))
  }
}

const loadAvailableMenuItems = async () => {
  try {
    console.log('Loading available menu items...')
    const response = await menuItemApi.getMenuItems({ is_available: 1, per_page: 100 })
    console.log('Available menu items raw response:', response)
    // For paginated response, data is in response.data
    availableMenuItems.value = response.data || []
    console.log('Available menu items loaded:', availableMenuItems.value.length, availableMenuItems.value)
  } catch (error) {
    console.error('Failed to load available menu items:', error)
    alert('Failed to load menu items')
  }
}

const loadBookingDetails = async () => {
  if (!orderForm.booking_id) {
    selectedBooking.value = null
    return
  }
  try {
    console.log('Loading booking details for ID:', orderForm.booking_id)
    const response = await bookingApi.getBooking(orderForm.booking_id)
    console.log('Booking details response:', response)
    // bookingApi.getBooking() already returns response.data which is booking object
    selectedBooking.value = response
    console.log('Selected booking loaded:', selectedBooking.value)
  } catch (error) {
    console.error('Failed to load booking details:', error, error.response)
    alert('Failed to load booking details: ' + (error.response?.data?.message || error.message))
    // Reset selection if error
    orderForm.booking_id = ''
    selectedBooking.value = null
  }
}

const addItemToCart = (item) => {
  const existingIndex = orderForm.items.findIndex(i => i.menu_item_id === item.id)
  if (existingIndex !== -1) {
    orderForm.items[existingIndex].quantity++
  } else {
    orderForm.items.push({
      menu_item_id: item.id,
      quantity: 1,
      price: item.price
    })
  }
}

const updateQuantity = (index, delta) => {
  orderForm.items[index].quantity += delta
  if (orderForm.items[index].quantity <= 0) {
    removeItemFromCart(index)
  }
}

const removeItemFromCart = (index) => {
  orderForm.items.splice(index, 1)
}

const getMenuItemName = (menuItemId) => {
  const item = availableMenuItems.value.find(i => i.id === menuItemId)
  return item ? item.name : ''
}

const calculateTotal = () => {
  return orderForm.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
}

const resetOrderForm = () => {
  orderForm.booking_id = ''
  orderForm.items = []
  orderForm.notes = ''
  selectedBooking.value = null
}

const submitOrder = async () => {
  if (!orderForm.booking_id || orderForm.items.length === 0) {
    alert('Please select a booking and add items to the order')
    return
  }
  try {
    submitting.value = true
    await restaurantOrderApi.createOrder(orderForm)
    alert('Order created successfully')
    resetOrderForm()
    activeTab.value = 'history'
    loadOrders()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to create order')
  } finally {
    submitting.value = false
  }
}

// Order History
const loadOrders = async (url = null) => {
  try {
    const params = { ...orderFilters }
    if (url) {
      const urlObj = new URL(url)
      const page = urlObj.searchParams.get('page')
      if (page) params.page = page
    }
    console.log('Loading orders with params:', params)
    const response = await restaurantOrderApi.getOrders(params)
    console.log('Orders response:', response)
    orders.value = response
  } catch (error) {
    console.error('Failed to load orders:', error)
    alert('Failed to load orders')
  }
}

const changeOrderPage = (url) => {
  if (url) loadOrders(url)
}

const updateOrderStatus = async (orderId, status) => {
  if (!confirm(`Change order status to ${status}?`)) return
  try {
    await restaurantOrderApi.updateOrderStatus(orderId, status)
    alert('Order status updated successfully')
    loadOrders()
  } catch (error) {
    alert('Failed to update order status')
  }
}

const viewOrderDetails = (order) => {
  const items = order.order_items?.map(item => 
    `${item.quantity}x ${item.menu_item?.name} @ Rp ${formatNumber(item.price)} = Rp ${formatNumber(item.subtotal)}`
  ).join('\n') || 'No items'
  
  alert(`Order Details\n\nOrder #: ${order.order_number}\nBooking: ${order.booking?.booking_number}\nGuest: ${order.booking?.guest?.name}\nStatus: ${order.status}\n\nItems:\n${items}\n\nTotal: Rp ${formatNumber(order.total_amount)}\n\nNotes: ${order.notes || '-'}`)
}

const getOrderStatusBadgeClass = (status) => {
  const classes = {
    pending: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
    preparing: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800',
    delivered: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
    cancelled: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'
  }
  return classes[status] || classes.pending
}

// Watch order filters
watch(orderFilters, () => {
  loadOrders()
})

// Utilities
const formatNumber = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getCategoryBadgeClass = (category) => {
  const classes = {
    food: 'px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800',
    beverage: 'px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800',
    snack: 'px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800'
  }
  return classes[category] || classes.food
}

// Initialize
onMounted(() => {
  console.log('RestaurantView mounted, initializing...')
  loadMenuItems()
  loadBookings()
  loadAvailableMenuItems()
  loadOrders()
})
</script>
