<template>
  <LayoutMain>
    <div class="p-3 sm:p-4 md:p-6">
      <!-- Header -->
      <div class="mb-4 md:mb-6">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $t('restaurant.title') }}</h1>
        <p class="text-gray-600 text-xs sm:text-sm md:text-base">{{ $t('restaurant.subtitle') }}</p>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-4 md:mb-6">
        <nav class="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto">
          <button
            @click="activeTab = 'menu'"
            :class="[
              'py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap',
              activeTab === 'menu'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ $t('restaurant.menuItems') }}
          </button>
          <button
            @click="activeTab = 'orders'"
            :class="[
              'py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap',
              activeTab === 'orders'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ $t('restaurant.createOrder') }}
          </button>
          <button
            @click="activeTab = 'history'"
            :class="[
              'py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap',
              activeTab === 'history'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ $t('restaurant.orderHistory') }}
          </button>
        </nav>
      </div>

      <!-- Menu Items Tab -->
      <div v-show="activeTab === 'menu'">
        <!-- Filters and Actions -->
        <div class="mb-4 flex flex-col gap-3 md:gap-4">
          <div class="flex flex-col sm:flex-row gap-3 md:gap-4 flex-1">
            <input
              v-model="filters.search"
              type="text"
              :placeholder="$t('restaurant.searchMenu')"
              class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <select
              v-model="filters.category"
              class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">{{ $t('restaurant.allCategories') }}</option>
              <option value="food">{{ $t('restaurant.food') }}</option>
              <option value="beverage">{{ $t('restaurant.beverage') }}</option>
              <option value="snack">{{ $t('restaurant.snack') }}</option>
              <option value="package">{{ $t('restaurant.package') }}</option>
            </select>
            <select
              v-model="filters.is_available"
              class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">{{ $t('restaurant.allStatus') }}</option>
              <option value="1">{{ $t('restaurant.available') }}</option>
              <option value="0">{{ $t('restaurant.unavailable') }}</option>
            </select>
          </div>
          <button
            @click="openMenuItemModal()"
            class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white text-sm md:text-base rounded-lg hover:bg-blue-700 transition-colors"
          >
            {{ $t('restaurant.addMenuItem') }}
          </button>
        </div>

        <!-- Debug info -->
        <div class="mb-2 text-xs sm:text-sm text-gray-600" v-if="menuItems">
          Total items: {{ menuItems.data?.length || 0 }} | Meta: {{ menuItems.meta ? 'exists' : 'null' }}
        </div>

        <!-- Menu Items Mobile Card View -->
        <div class="block md:hidden bg-white rounded-lg shadow overflow-hidden">
          <div v-if="!menuItems.data || menuItems.data.length === 0" class="p-8 text-center text-gray-500">
            {{ $t('restaurant.noMenuItems') }}
          </div>
          <div v-else>
            <div v-for="item in menuItems.data" :key="item.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
              <div class="space-y-3">
                <div class="flex gap-3">
                  <img 
                    v-if="item.photo" 
                    :src="`${apiUrl}/storage/${item.photo}`" 
                    alt="Menu photo"
                    class="h-16 w-16 rounded object-cover"
                  />
                  <div v-else class="h-16 w-16 rounded bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <span class="text-gray-400 text-xs">{{ $t('restaurant.noPhoto') }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900">{{ item.name }}</div>
                    <div class="text-sm text-gray-500 line-clamp-2">{{ item.description }}</div>
                  </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span :class="getCategoryBadgeClass(item.category)">
                    {{ item.category }}
                  </span>
                  <span :class="item.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ item.is_available ? $t('restaurant.available') : $t('restaurant.unavailable') }}
                  </span>
                </div>
                <div class="font-semibold text-gray-900">Rp {{ formatNumber(item.price) }}</div>
                <div class="flex gap-2">
                  <button @click="openMenuItemModal(item)" class="flex-1 text-xs px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                    {{ $t('restaurant.edit') }}
                  </button>
                  <button @click="deleteMenuItem(item)" class="flex-1 text-xs px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200">
                    {{ $t('restaurant.delete') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Menu Items Desktop Table -->
        <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.photo') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.category') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.price') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.status') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.actions') }}</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="!menuItems.data || menuItems.data.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                  {{ $t('restaurant.noMenuItems') }}
                </td>
              </tr>
              <tr v-for="item in menuItems.data" :key="item.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <img 
                    v-if="item.photo" 
                    :src="`${apiUrl}/storage/${item.photo}`" 
                    alt="Menu photo"
                    class="h-12 w-12 rounded object-cover"
                  />
                  <div v-else class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400 text-xs">{{ $t('restaurant.noPhoto') }}</span>
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
                    {{ item.is_available ? $t('restaurant.available') : $t('restaurant.unavailable') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button @click="openMenuItemModal(item)" class="text-blue-600 hover:text-blue-900 mr-3">
                    {{ $t('restaurant.edit') }}
                  </button>
                  <button @click="deleteMenuItem(item)" class="text-red-600 hover:text-red-900">
                    {{ $t('restaurant.delete') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="menuItems.meta" class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="text-xs sm:text-sm text-gray-700">
            {{ $t('restaurant.showing') }} {{ menuItems.meta.from }} {{ $t('restaurant.to') }} {{ menuItems.meta.to }} {{ $t('restaurant.of') }} {{ menuItems.meta.total }} {{ $t('restaurant.results') }}
          </div>
          <div class="flex gap-2 overflow-x-auto">
            <button
              v-for="link in menuItems.meta.links"
              :key="link.label"
              @click="changePage(link.url)"
              :disabled="!link.url"
              :class="[
                'px-2 sm:px-3 py-1 rounded text-xs sm:text-sm',
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
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('restaurant.selectBooking') }}</label>
            <select
              v-model="orderForm.booking_id"
              @change="loadBookingDetails"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
              <option value="">{{ $t('restaurant.selectABooking') }}</option>
              <optgroup v-if="bookings.filter(b => b.type === 'room').length > 0" :label="$t('restaurant.roomBookings')">
                <option v-for="booking in bookings.filter(b => b.type === 'room')" :key="'room-' + booking.id" :value="'room-' + booking.id">
                  {{ booking.booking_number }} - {{ booking.guest?.name }} ({{ $t('restaurant.room') }} {{ booking.rooms?.[0]?.room_number }})
                </option>
              </optgroup>
              <optgroup v-if="bookings.filter(b => b.type === 'hall').length > 0" :label="$t('restaurant.hallBookings')">
                <option v-for="booking in bookings.filter(b => b.type === 'hall')" :key="'hall-' + booking.id" :value="'hall-' + booking.id">
                  {{ booking.booking_number }} - {{ booking.customer_name }} ({{ booking.hall?.name }})
                </option>
              </optgroup>
            </select>
            <p v-if="bookings.length === 0" class="text-sm text-gray-500 mt-1">
              {{ $t('restaurant.noActiveBookings') }}
            </p>
          </div>

          <!-- Booking Details (if booking selected) -->
          <div v-if="selectedBooking" class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-2">{{ $t('restaurant.bookingDetails') }}</h3>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <template v-if="selectedBooking.type === 'room'">
                <div><span class="text-gray-600">{{ $t('restaurant.guest') }}:</span> {{ selectedBooking.guest?.name }}</div>
                <div><span class="text-gray-600">{{ $t('restaurant.room') }}:</span> {{ selectedBooking.rooms?.[0]?.room_number }}</div>
                <div><span class="text-gray-600">{{ $t('restaurant.checkIn') }}:</span> {{ formatDate(selectedBooking.check_in_date) }}</div>
                <div><span class="text-gray-600">{{ $t('restaurant.checkOut') }}:</span> {{ formatDate(selectedBooking.check_out_date) }}</div>
              </template>
              <template v-else>
                <div><span class="text-gray-600">{{ $t('restaurant.customer') }}:</span> {{ selectedBooking.customer_name }}</div>
                <div><span class="text-gray-600">{{ $t('restaurant.hall') }}:</span> {{ selectedBooking.hall?.name }}</div>
                <div><span class="text-gray-600">{{ $t('restaurant.eventDate') }}:</span> {{ formatDate(selectedBooking.event_date) }}</div>
                <div><span class="text-gray-600">{{ $t('restaurant.duration') }}:</span> {{ selectedBooking.duration }} {{ $t('restaurant.hours') }}</div>
              </template>
            </div>
          </div>

          <!-- Menu Items Selection -->
          <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
              <label class="block text-sm font-medium text-gray-700">{{ $t('restaurant.addItems') }}</label>
              <div class="flex gap-2">
                <button
                  @click="menuCategoryFilter = ''"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                    menuCategoryFilter === '' 
                      ? 'bg-blue-600 text-white' 
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  ]"
                >
                  {{ $t('restaurant.all') }} ({{ availableMenuItems.length }})
                </button>
                <button
                  @click="menuCategoryFilter = 'food'"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                    menuCategoryFilter === 'food' 
                      ? 'bg-green-600 text-white' 
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  ]"
                >
                  🍽️ {{ $t('restaurant.food') }} ({{ availableMenuItems.filter(i => i.category === 'food').length }})
                </button>
                <button
                  @click="menuCategoryFilter = 'beverage'"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                    menuCategoryFilter === 'beverage' 
                      ? 'bg-blue-600 text-white' 
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  ]"
                >
                  🥤 {{ $t('restaurant.beverage') }} ({{ availableMenuItems.filter(i => i.category === 'beverage').length }})
                </button>
                <button
                  @click="menuCategoryFilter = 'snack'"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                    menuCategoryFilter === 'snack' 
                      ? 'bg-yellow-600 text-white' 
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  ]"
                >
                  🍿 {{ $t('restaurant.snack') }} ({{ availableMenuItems.filter(i => i.category === 'snack').length }})
                </button>
                <button
                  @click="menuCategoryFilter = 'package'"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                    menuCategoryFilter === 'package' 
                      ? 'bg-purple-600 text-white' 
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  ]"
                >
                  📦 {{ $t('restaurant.package') }} ({{ availableMenuItems.filter(i => i.category === 'package').length }})
                </button>
              </div>
            </div>
            <p v-if="availableMenuItems.length === 0" class="text-sm text-gray-500 mb-2">
              {{ $t('restaurant.noAvailableItems') }}
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3">
              <div
                v-for="item in filteredMenuItems"
                :key="item.id"
                class="border border-gray-200 rounded-lg p-3 hover:border-blue-500 transition-colors"
              >
                <img
                  v-if="item.photo"
                  :src="`${apiUrl}/storage/${item.photo}`"
                  alt="Menu photo"
                  class="w-full h-24 object-cover rounded mb-2"
                />
                <div v-else class="w-full h-24 rounded bg-gray-200 flex items-center justify-center mb-2">
                  <span class="text-gray-400 text-xs">{{ $t('restaurant.noPhoto') }}</span>
                </div>
                <h4 class="font-medium text-gray-900 text-sm">{{ item.name }}</h4>
                <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ item.description }}</p>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-blue-600 font-medium text-sm">Rp {{ formatNumber(item.price) }}</span>
                  <span :class="getCategoryBadgeClass(item.category)">
                    {{ item.category }}
                  </span>
                </div>
                <button
                  @click="addItemToCart(item)"
                  class="w-full px-2 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-xs font-medium"
                >
                  {{ $t('restaurant.addToCart') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Cart -->
          <div v-if="orderForm.items.length > 0" class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('restaurant.orderItems') }}</h3>
            <div class="bg-gray-50 rounded-lg p-4">
              <div v-for="(cartItem, index) in orderForm.items" :key="index" class="flex items-center justify-between mb-3 last:mb-0">
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ getMenuItemName(cartItem.menu_item_id) }}</div>
                  <div class="text-sm text-gray-500">Rp {{ formatNumber(cartItem.price) }} {{ $t('restaurant.each') }}</div>
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
                    {{ $t('restaurant.remove') }}
                  </button>
                  <div class="ml-4 font-medium text-gray-900 w-32 text-right">
                    Rp {{ formatNumber(cartItem.price * cartItem.quantity) }}
                  </div>
                </div>
              </div>
              <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex justify-between items-center text-lg font-bold">
                  <span>{{ $t('restaurant.total') }}:</span>
                  <span class="text-blue-600">Rp {{ formatNumber(calculateTotal()) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('restaurant.orderNotes') }}</label>
            <textarea
              v-model="orderForm.notes"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="$t('restaurant.specialRequests')"
            ></textarea>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end gap-3">
            <button
              @click="resetOrderForm"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              {{ $t('restaurant.reset') }}
            </button>
            <button
              @click="submitOrder"
              :disabled="!orderForm.booking_id || orderForm.items.length === 0 || submitting"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submitting ? $t('restaurant.creating') : $t('restaurant.createOrder') }}
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
            :placeholder="$t('restaurant.searchOrder')"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <input
            v-model="orderFilters.start_date"
            type="date"
            :placeholder="$t('restaurant.startDate')"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <input
            v-model="orderFilters.end_date"
            type="date"
            :placeholder="$t('restaurant.endDate')"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <select
            v-model="orderFilters.status"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">{{ $t('restaurant.allStatus') }}</option>
            <option value="pending">{{ $t('restaurant.pending') }}</option>
            <option value="preparing">{{ $t('restaurant.preparing') }}</option>
            <option value="delivered">{{ $t('restaurant.delivered') }}</option>
            <option value="cancelled">{{ $t('restaurant.cancelled') }}</option>
          </select>
          <button
            @click="loadOrders()"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
          >
            {{ $t('restaurant.refresh') }}
          </button>
          <button
            @click="exportOrders"
            :disabled="exporting"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ exporting ? $t('restaurant.exporting') : $t('restaurant.exportExcel') }}
          </button>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.orderNumber') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.booking') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.menuItems') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.total') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.status') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.date') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('restaurant.actions') }}</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in orders.data" :key="order.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ order.order_number }}
                </td>
                <td class="px-6 py-4">
                  <template v-if="order.booking_id">
                    <div class="text-sm font-medium text-gray-900">{{ order.booking?.booking_number }}</div>
                    <div class="text-sm text-gray-500">{{ order.booking?.guest?.name }}</div>
                  </template>
                  <template v-else>
                    <div class="text-sm font-medium text-gray-900">{{ order.hall_booking?.booking_number }}</div>
                    <div class="text-sm text-gray-500">{{ order.hall_booking?.customer_name }}</div>
                  </template>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ order.order_items?.length || 0 }} {{ $t('restaurant.items') }}
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
                    {{ $t('restaurant.prepare') }}
                  </button>
                  <button
                    v-if="order.status === 'preparing'"
                    @click="updateOrderStatus(order.id, 'delivered')"
                    class="text-green-600 hover:text-green-900 mr-2"
                  >
                    {{ $t('restaurant.deliver') }}
                  </button>
                  <button
                    v-if="['pending', 'preparing'].includes(order.status)"
                    @click="updateOrderStatus(order.id, 'cancelled')"
                    class="text-red-600 hover:text-red-900"
                  >
                    {{ $t('restaurant.cancel') }}
                  </button>
                  <button
                    @click="viewOrderDetails(order)"
                    class="text-gray-600 hover:text-gray-900 ml-2"
                  >
                    {{ $t('restaurant.view') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="orders.meta" class="mt-4 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            {{ $t('restaurant.showing') }} {{ orders.meta.from }} {{ $t('restaurant.to') }} {{ orders.meta.to }} {{ $t('restaurant.of') }} {{ orders.meta.total }} {{ $t('restaurant.results') }}
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
          {{ editingMenuItem ? $t('restaurant.editMenuItem') : $t('restaurant.addMenuItem') }}
        </h2>
        <form @submit.prevent="saveMenuItem">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('restaurant.name') }} *</label>
            <input
              v-model="menuItemForm.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('restaurant.category') }} *</label>
            <select
              v-model="menuItemForm.category"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="food">{{ $t('restaurant.food') }}</option>
              <option value="beverage">{{ $t('restaurant.beverage') }}</option>
              <option value="snack">{{ $t('restaurant.snack') }}</option>
              <option value="package">{{ $t('restaurant.package') }}</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('restaurant.price') }} *</label>
            <input
              v-model.number="menuItemForm.price"
              type="number"
              step="0.01"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('restaurant.description') }}</label>
            <textarea
              v-model="menuItemForm.description"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            ></textarea>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('restaurant.photo') }}</label>
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
              <span class="ml-2 text-sm text-gray-700">{{ $t('restaurant.available') }}</span>
            </label>
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="closeMenuItemModal"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              {{ $t('breakfast.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ submitting ? $t('restaurant.saving') : $t('restaurant.save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import LayoutMain from '../components/LayoutMain.vue'
import { menuItemApi, restaurantOrderApi, bookingApi, hallBookingApi } from '../api'

const { t } = useI18n()
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
const menuCategoryFilter = ref('')
const selectedBooking = ref(null)
const orderForm = reactive({
  booking_id: '',
  booking_type: 'room', // 'room' or 'hall'
  items: [],
  notes: ''
})
const submitting = ref(false)

// Order History
const orders = ref({ data: [], meta: null })
const orderFilters = reactive({
  search: '',
  status: '',
  start_date: '',
  end_date: ''
})
const exporting = ref(false)

// Computed
const filteredMenuItems = computed(() => {
  if (!menuCategoryFilter.value) {
    return availableMenuItems.value
  }
  return availableMenuItems.value.filter(item => item.category === menuCategoryFilter.value)
})

// Load menu items
const loadMenuItems = async (url = null) => {
  try {
    const params = {}
    // Only include filter params if they have values
    if (filters.search) params.search = filters.search
    if (filters.category) params.category = filters.category
    if (filters.is_available !== '') params.is_available = filters.is_available
    
    if (url) {
      const urlObj = new URL(url)
      const page = urlObj.searchParams.get('page')
      if (page) params.page = page
    }
    console.log('Loading menu items with params:', params)
    const response = await menuItemApi.getMenuItems(params)
    console.log('Menu items response:', response)
    menuItems.value = response
    console.log('Menu items loaded:', menuItems.value?.data?.length || 0, menuItems.value)
  } catch (error) {
    console.error('Failed to load menu items:', error, error.response)
    alert(t('restaurant.loadMenuFailed') + ': ' + (error.response?.data?.message || error.message))
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
      alert(t('restaurant.menuItemUpdated'))
    } else {
      await menuItemApi.createMenuItem(menuItemForm)
      alert(t('restaurant.menuItemCreated'))
    }
    closeMenuItemModal()
    loadMenuItems()
  } catch (error) {
    alert(error.response?.data?.message || t('restaurant.menuItemSaveFailed'))
  } finally {
    submitting.value = false
  }
}

const deleteMenuItem = async (item) => {
  if (!confirm(`${t('restaurant.deleteConfirm')} ${item.name}?`)) return
  try {
    await menuItemApi.deleteMenuItem(item.id)
    alert(t('restaurant.menuItemDeleted'))
    loadMenuItems()
  } catch (error) {
    alert(t('restaurant.menuItemDeleteFailed'))
  }
}

// Orders
const loadBookings = async () => {
  try {
    const allBookings = []
    
    // Load room bookings with checked_in status
    const roomBookings = await bookingApi.getBookings({ status: 'checked_in' })
    const roomBookingsWithType = (roomBookings || []).map(b => ({
      ...b,
      type: 'room'
    }))
    allBookings.push(...roomBookingsWithType)
    
    // Load hall bookings with confirmed status
    const hallBookings = await hallBookingApi.getHallBookings({ status: 'confirmed' })
    const hallBookingsData = hallBookings.data || hallBookings || []
    const hallBookingsWithType = hallBookingsData.map(b => ({
      ...b,
      type: 'hall'
    }))
    allBookings.push(...hallBookingsWithType)
    
    bookings.value = allBookings
    console.log('All bookings loaded:', allBookings.length, allBookings)
  } catch (error) {
    console.error('Failed to load bookings:', error, error.response)
    alert(t('restaurant.loadBookingsFailed') + ': ' + (error.response?.data?.message || error.message))
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
    alert(t('restaurant.loadMenuFailed'))
  }
}

const loadBookingDetails = async () => {
  if (!orderForm.booking_id) {
    selectedBooking.value = null
    return
  }
  try {
    // Parse booking_id to get type and id (format: "room-123" or "hall-456")
    const [type, id] = orderForm.booking_id.split('-')
    orderForm.booking_type = type
    
    let response
    if (type === 'room') {
      response = await bookingApi.getBooking(id)
    } else if (type === 'hall') {
      response = await hallBookingApi.getHallBooking(id)
    }
    
    selectedBooking.value = {
      ...response,
      type: type
    }
    console.log('Selected booking loaded:', selectedBooking.value)
  } catch (error) {
    console.error('Failed to load booking details:', error, error.response)
    alert(t('restaurant.loadBookingDetailsFailed') + ': ' + (error.response?.data?.message || error.message))
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
  orderForm.booking_type = 'room'
  orderForm.items = []
  orderForm.notes = ''
  selectedBooking.value = null
  menuCategoryFilter.value = ''
}

const submitOrder = async () => {
  if (!orderForm.booking_id || orderForm.items.length === 0) {
    alert(t('restaurant.selectBookingAndItems'))
    return
  }
  try {
    submitting.value = true
    
    // Parse booking_id to get actual id (remove type prefix)
    const [type, id] = orderForm.booking_id.split('-')
    
    // Create payload with actual booking id
    const payload = {
      booking_id: type === 'room' ? id : null,
      hall_booking_id: type === 'hall' ? id : null,
      items: orderForm.items,
      notes: orderForm.notes
    }
    
    await restaurantOrderApi.createOrder(payload)
    alert(t('restaurant.orderCreated'))
    resetOrderForm()
    activeTab.value = 'history'
    loadOrders()
  } catch (error) {
    alert(error.response?.data?.message || t('restaurant.orderCreateFailed'))
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
    alert(t('restaurant.loadOrdersFailed'))
  }
}

const changeOrderPage = (url) => {
  if (url) loadOrders(url)
}

const updateOrderStatus = async (orderId, status) => {
  if (!confirm(`${t('restaurant.changeStatus')} ${status}?`)) return
  try {
    await restaurantOrderApi.updateOrderStatus(orderId, status)
    alert(t('restaurant.orderStatusUpdated'))
    loadOrders()
  } catch (error) {
    alert(t('restaurant.orderStatusUpdateFailed'))
  }
}

const exportOrders = async () => {
  exporting.value = true
  try {
    const apiUrl = import.meta.env.VITE_API_BASE_URL || 'https://hotel.tazkia.web.id/api'
    
    // Build query parameters
    const params = new URLSearchParams()
    if (orderFilters.start_date) params.append('start_date', orderFilters.start_date)
    if (orderFilters.end_date) params.append('end_date', orderFilters.end_date)
    if (orderFilters.status) params.append('status', orderFilters.status)
    
    const url = `${apiUrl}/restaurant-orders/export?${params.toString()}`
    
    // Create temporary link and trigger download
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', '')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (err) {
    console.error('Failed to export orders:', err)
    alert(t('restaurant.exportFailed'))
  } finally {
    exporting.value = false
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
    snack: 'px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800',
    package: 'px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800'
  }
  return classes[category] || classes.food
}

// Initialize
onMounted(async () => {
  console.log('RestaurantView mounted, initializing...')
  try {
    await loadMenuItems()
    console.log('Menu items initialization complete')
  } catch (error) {
    console.error('Menu items initialization failed:', error)
  }
  
  try {
    await loadBookings()
    console.log('Bookings initialization complete')
  } catch (error) {
    console.error('Bookings initialization failed:', error)
  }
  
  try {
    await loadAvailableMenuItems()
    console.log('Available menu items initialization complete')
  } catch (error) {
    console.error('Available menu items initialization failed:', error)
  }
  
  try {
    await loadOrders()
    console.log('Orders initialization complete')
  } catch (error) {
    console.error('Orders initialization failed:', error)
  }
  
  console.log('All initialization complete')
})
</script>
