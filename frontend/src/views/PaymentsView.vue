<template>
  <LayoutMain>
    <div class="space-y-4 md:space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $t('payments.title') }}</h1>
          <p class="text-gray-600 mt-1 text-xs sm:text-sm md:text-base">{{ $t('payments.subtitle') }}</p>
        </div>
        <button
          @click="openCreateModal"
          class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white text-sm md:text-base rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + {{ $t('payments.newPayment') }}
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-3 md:p-4">
        <!-- Filter Booking Status Tabs -->
        <div class="flex gap-2 mb-4 flex-wrap">
          <button
            v-for="tab in bookingStatusTabs"
            :key="tab.value"
            @click="setBookingStatusFilter(tab.value)"
            :class="[
              'px-3 py-1.5 rounded-full text-xs font-semibold border transition-all',
              filters.booking_status === tab.value
                ? 'bg-indigo-600 text-white border-indigo-600 shadow'
                : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400 hover:text-indigo-600'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
          <div class="flex-1">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('payments.paymentDateFrom') }}</label>
            <input
              v-model="filters.start_date"
              @change="loadPayments"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div class="flex-1">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('payments.paymentDateTo') }}</label>
            <input
              v-model="filters.end_date"
              @change="loadPayments"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div class="flex-1">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('payments.paymentType') }}</label>
            <select
              v-model="filters.payment_type"
              @change="loadPayments"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">{{ $t('payments.allTypes') }}</option>
              <option value="deposit">{{ $t('payments.deposit') }}</option>
              <option value="partial">{{ $t('payments.partial') }}</option>
              <option value="full">{{ $t('payments.full') }}</option>
              <option value="refund">{{ $t('payments.refund') }}</option>
              <option value="extra_charge">{{ $t('payments.extraCharge') }}</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('payments.paymentMethod') }}</label>
            <select
              v-model="filters.payment_method"
              @change="loadPayments"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">{{ $t('payments.allMethods') }}</option>
              <option value="cash">{{ $t('payments.cash') }}</option>
              <option value="transfer">{{ $t('payments.transfer') }}</option>
              <option value="qris">{{ $t('payments.qris') }}</option>
              <option value="card">{{ $t('payments.card') }}</option>
              <option value="other">{{ $t('payments.other') }}</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('payments.search') }}</label>
            <input
              v-model="filters.search"
              @input="debouncedSearch"
              type="text"
              :placeholder="$t('payments.searchPlaceholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
        <div class="mt-3 md:mt-4 flex gap-2 flex-wrap">
          <button
            @click="exportPayments"
            :disabled="exporting"
            class="px-4 py-2 bg-green-600 text-white text-sm md:text-base rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ exporting ? $t('payments.exporting') : '📊 ' + $t('payments.exportExcel') }}
          </button>
          <button
            v-if="filters.booking_status || filters.payment_type || filters.payment_method || filters.search || filters.start_date || filters.end_date"
            @click="clearFilters"
            class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition-colors"
          >
            🗙 {{ $t('payments.resetFilter') }}
          </button>
        </div>
      </div>

      <!-- Summary Banner when filtering by status -->
      <div
        v-if="filters.booking_status && payments.length > 0"
        class="rounded-lg p-4 flex items-center gap-3"
        :class="{
          'bg-emerald-50 border border-emerald-200': filters.booking_status === 'checked_out',
          'bg-purple-50 border border-purple-200': filters.booking_status === 'complete',
          'bg-indigo-50 border border-indigo-200': filters.booking_status === 'checkout_or_complete',
        }"
      >
        <span class="text-2xl">
          {{ filters.booking_status === 'checked_out' ? '🛏️' : filters.booking_status === 'complete' ? '🎪' : '✅' }}
        </span>
        <div>
          <div class="font-semibold text-sm text-gray-800">
            {{ filters.booking_status === 'checked_out' ? $t('payments.roomCheckout') :
               filters.booking_status === 'complete' ? $t('payments.hallCompleted') :
               $t('payments.checkoutAndCompleted') }}
          </div>
          <div class="text-xs text-gray-600">{{ pagination.total }} {{ $t('payments.paymentsFound') }}</div>
        </div>
      </div>

      <!-- Payments List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="text-gray-500 mt-2">{{ $t('payments.loading') }}</p>
        </div>

        <div v-else-if="payments.length === 0" class="text-center py-16">
          <div class="text-5xl mb-4">💳</div>
          <p class="text-gray-500 font-medium">{{ $t('payments.noPayments') }}</p>
          <p class="text-gray-400 text-sm mt-1">{{ $t('payments.noPaymentsSub') }}</p>
        </div>

        <div v-else>
          <!-- Mobile Card View -->
          <div class="block md:hidden">
            <div v-for="payment in payments" :key="payment.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
              <div class="space-y-3">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900 text-sm">{{ payment.payment_number }}</div>
                    <div class="text-xs text-gray-500">{{ payment.reference_number || '' }}</div>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-gray-900 text-sm">{{ formatCurrency(getPaymentTotal(payment)) }}</div>
                    <div class="text-xs text-gray-400">{{ formatDate(payment.created_at) }}</div>
                  </div>
                </div>
                <!-- Booking info -->
                <div class="flex items-center gap-2">
                  <span v-if="payment.booking_id" class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-semibold">🛏️ {{ $t('payments.eposRoom') }}</span>
                  <span v-else class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-semibold">🎪 {{ $t('payments.eposHall') }}</span>
                  <span class="text-sm text-gray-700 font-medium">
                    {{ payment.booking_id
                      ? (payment.booking?.guest?.name || '-')
                      : (payment.hall_booking?.customer_name || '-') }}
                  </span>
                </div>
                <div v-if="payment.booking_id" class="text-xs text-gray-500">
                  {{ $t('payments.eposRoom') }}: {{ getBookingRooms(payment) }}
                </div>
                <div v-else class="text-xs text-gray-500">
                  {{ $t('payments.eposHall') }}: {{ payment.hall_booking?.hall?.name || '-' }}
                </div>
                <div class="flex flex-wrap gap-2">
                  <span :class="getPaymentTypeBadgeClass(payment.payment_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPaymentTypeLabel(payment.payment_type) }}
                  </span>
                  <span :class="getPaymentMethodBadgeClass(payment.payment_method)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPaymentMethodLabel(payment.payment_method) }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    @click="printEpos(payment)"
                    class="text-xs px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 font-semibold flex items-center gap-1"
                  >
                    🖨️ {{ $t('payments.printEpos') }}
                  </button>
                  <button
                    @click="openEditModal(payment)"
                    class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                  >
                    {{ $t('payments.edit') }}
                  </button>
                  <button
                    @click="confirmDelete(payment)"
                    class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
                  >
                    🗑️ {{ $t('payments.delete') }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Mobile Pagination -->
          <div v-if="pagination.last_page > 1" class="md:hidden mt-4 px-4 pb-4">
            <div class="text-sm text-gray-700 mb-2 text-center">
              {{ $t('payments.page') }} {{ pagination.current_page }} {{ $t('payments.of') }} {{ pagination.last_page }}
            </div>
            <div class="flex gap-2 justify-center">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-4 py-2 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                ← {{ $t('payments.prev') }}
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-4 py-2 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ $t('payments.next') }} →
              </button>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.paymentNumber') }}
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.typeAndGuest') }}
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.roomOrHall') }}
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.typeAndMethod') }}
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.amount') }}
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.date') }}
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm font-mono font-medium text-gray-900">{{ payment.payment_number }}</div>
                  <div class="text-xs text-gray-400 mt-0.5">{{ payment.reference_number || '-' }}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="flex items-center gap-1.5 mb-0.5">
                    <span v-if="payment.booking_id" class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-bold">{{ $t('payments.eposRoom') }}</span>
                    <span v-else class="px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-bold">{{ $t('payments.eposHall') }}</span>
                    <span class="text-sm font-medium text-gray-900">
                      {{ payment.booking_id
                        ? (payment.booking?.guest?.name || '-')
                        : (payment.hall_booking?.customer_name || '-') }}
                    </span>
                  </div>
                  <div v-if="!payment.booking_id" class="text-xs text-gray-500">
                    {{ payment.hall_booking?.customer_phone || '' }}
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div v-if="payment.booking_id" class="text-sm text-gray-700">
                    {{ $t('payments.eposRoom') }} {{ getBookingRooms(payment) }}
                  </div>
                  <div v-else class="text-sm text-gray-700">
                    {{ payment.hall_booking?.hall?.name || '-' }}
                  </div>
                  <div v-if="payment.booking_id" class="text-xs text-gray-400">
                    {{ payment.booking?.booking_number }}
                  </div>
                  <div v-else class="text-xs text-gray-400">
                    {{ payment.hall_booking?.booking_number }}
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span :class="getPaymentTypeBadgeClass(payment.payment_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPaymentTypeLabel(payment.payment_type) }}
                  </span>
                  <div class="text-xs text-gray-500 mt-1">{{ getPaymentMethodLabel(payment.payment_method) }}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                  <div class="text-sm font-bold text-gray-900">{{ formatCurrency(getPaymentTotal(payment)) }}</div>
                  <div v-if="payment.restaurant_charges > 0 || payment.laundry_charges > 0" class="text-xs text-gray-400">
                    {{ $t('payments.principal') }}: {{ formatCurrency(payment.amount) }}
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(payment.created_at) }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="flex gap-1.5 items-center">
                    <button
                      @click="printEpos(payment)"
                      :title="$t('payments.printEpos')"
                      class="px-2.5 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 text-xs font-semibold flex items-center gap-1 transition-colors"
                    >
                      🖨️ {{ $t('payments.printEpos') }}
                    </button>
                    <button
                      @click="openEditModal(payment)"
                      class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-xs transition-colors"
                    >
                      ✏️
                    </button>
                    <button
                      @click="confirmDelete(payment)"
                      class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs transition-colors"
                    >
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination Desktop -->
          <div v-if="pagination.last_page > 1" class="px-4 py-4 flex justify-between items-center border-t border-gray-100">
            <div class="text-sm text-gray-700">
              {{ $t('payments.showing') }} {{ (pagination.current_page - 1) * pagination.per_page + 1 }}–{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
              {{ $t('payments.of') }} {{ pagination.total }} {{ $t('payments.paymentsFound') }}
            </div>
            <div class="flex gap-1">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-3 py-1.5 border rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-sm"
              >
                ← {{ $t('payments.prev') }}
              </button>
              <template v-for="page in visiblePages" :key="page">
                <span v-if="page === '...'" class="px-3 py-1.5 text-gray-400">…</span>
                <button
                  v-else
                  @click="changePage(page)"
                  :class="[
                    'px-3 py-1.5 rounded-lg text-sm border transition-colors',
                    page === pagination.current_page
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
              </template>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-1.5 border rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-sm"
              >
                {{ $t('payments.next') }} →
              </button>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Payment Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? $t('payments.editPayment') : $t('payments.createPayment') }}
        </h2>

        <form @submit.prevent="savePayment" class="space-y-4">
          <div v-if="!isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('payments.bookingType') }} *</label>
            <div class="grid grid-cols-2 gap-2 mb-4">
              <button
                type="button"
                @click="formData.booking_type = 'room'; switchBookingType()"
                :class="[
                  'px-4 py-2 rounded-lg font-medium transition-colors',
                  formData.booking_type === 'room'
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                ]"
              >
                🛏️ {{ $t('payments.roomBooking') }}
              </button>
              <button
                type="button"
                @click="formData.booking_type = 'hall'; switchBookingType()"
                :class="[
                  'px-4 py-2 rounded-lg font-medium transition-colors',
                  formData.booking_type === 'hall'
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                ]"
              >
                🎪 {{ $t('payments.hallBooking') }}
              </button>
            </div>
          </div>

          <div v-if="!isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ formData.booking_type === 'room' ? $t('payments.selectBooking') + ' *' : $t('payments.selectHallBooking') + ' *' }}
            </label>
            <div class="relative">
              <input
                v-model="bookingSearch"
                @input="filterBookings"
                @focus="showBookingDropdown = true"
                type="text"
                required
                :placeholder="$t('payments.searchPlaceholder')"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <div
                v-if="showBookingDropdown && filteredBookings.length > 0"
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
              >
                <div
                  v-for="booking in filteredBookings"
                  :key="booking.id"
                  @click="selectBooking(booking)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                >
                  <div class="font-medium text-gray-900">{{ booking.booking_number }}</div>
                  <div v-if="formData.booking_type === 'room'" class="text-sm text-gray-600">
                    {{ booking.guest?.name }} —
                    <span class="text-xs text-gray-500">({{ getStatusLabel(booking.status) }})</span>
                  </div>
                  <div v-else class="text-sm text-gray-600">
                    {{ booking.customer_name || booking.guest?.name }} — {{ booking.hall?.name }}
                    <span class="text-xs text-gray-500">({{ booking.event_type }})</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('payments.paymentType') }} *</label>
            <select
              v-model="formData.payment_type"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="deposit">{{ $t('payments.deposit') }}</option>
              <option value="partial">{{ $t('payments.partial') }}</option>
              <option value="full">{{ $t('payments.full') }}</option>
              <option value="refund">{{ $t('payments.refund') }}</option>
              <option value="extra_charge">{{ $t('payments.extraCharge') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('payments.paymentMethod') }} *</label>
            <select
              v-model="formData.payment_method"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="cash">{{ $t('payments.cash') }}</option>
              <option value="transfer">{{ $t('payments.transfer') }}</option>
              <option value="qris">{{ $t('payments.qris') }}</option>
              <option value="card">{{ $t('payments.card') }}</option>
              <option value="other">{{ $t('payments.other') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ formData.booking_type === 'room' ? $t('payments.roomAmount') : $t('payments.hallAmount') }} *</label>
            <input
              v-model.number="formData.amount"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="0"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('payments.restaurantCharges') }}</label>
            <input
              v-model.number="formData.restaurant_charges"
              type="number"
              step="0.01"
              min="0"
              readonly
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
              placeholder="0"
            />
            <p class="text-xs text-gray-500 mt-1">{{ $t('payments.autoFilledRestaurant') }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('payments.laundryCharges') }}</label>
            <input
              v-model.number="formData.laundry_charges"
              type="number"
              step="0.01"
              min="0"
              readonly
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
              placeholder="0"
            />
            <p class="text-xs text-gray-500 mt-1">{{ $t('payments.autoFilledLaundry') }}</p>
          </div>

          <!-- Total preview -->
          <div v-if="formData.amount > 0 || formData.restaurant_charges > 0 || formData.laundry_charges > 0" class="p-3 bg-blue-50 rounded-lg">
            <div class="space-y-1">
              <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">{{ $t('payments.principal') }}:</span>
                <span class="text-gray-900">Rp {{ formatNumber(parseFloat(formData.amount || 0)) }}</span>
              </div>
              <div v-if="formData.restaurant_charges > 0" class="flex justify-between items-center text-sm">
                <span class="text-gray-600">{{ $t('payments.restaurantCharges') }}:</span>
                <span class="text-gray-900">Rp {{ formatNumber(parseFloat(formData.restaurant_charges || 0)) }}</span>
              </div>
              <div v-if="formData.laundry_charges > 0" class="flex justify-between items-center text-sm">
                <span class="text-gray-600">{{ $t('payments.laundryCharges') }}:</span>
                <span class="text-gray-900">Rp {{ formatNumber(parseFloat(formData.laundry_charges || 0)) }}</span>
              </div>
              <div class="border-t border-blue-200 pt-1 mt-1 flex justify-between items-center">
                <span class="font-semibold text-gray-700">{{ $t('payments.totalAmount') }}:</span>
                <span class="text-lg font-bold text-blue-600">
                  Rp {{ formatNumber(parseFloat(formData.amount || 0) + parseFloat(formData.restaurant_charges || 0) + parseFloat(formData.laundry_charges || 0)) }}
                </span>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('payments.referenceNumber') }}</label>
            <input
              v-model="formData.reference_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="..."
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('payments.notes') }}</label>
            <textarea
              v-model="formData.notes"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="..."
            ></textarea>
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
              {{ $t('payments.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ saving ? $t('payments.saving') : $t('payments.save') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showDeleteConfirm = false"
    >
      <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-2xl">
        <div class="text-center mb-4">
          <div class="text-5xl mb-3">🗑️</div>
          <h2 class="text-xl font-bold text-gray-900">{{ $t('payments.deleteTitle') }}</h2>
          <p class="text-gray-500 mt-2 text-sm">
            {{ $t('payments.deleteMessage', { number: paymentToDelete?.payment_number }) }}
          </p>
          <p class="text-red-500 text-xs mt-1">{{ $t('payments.deleteWarning') }}</p>
        </div>
        <div class="flex gap-3">
          <button
            @click="showDeleteConfirm = false"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            {{ $t('payments.cancel') }}
          </button>
          <button
            @click="handleDelete"
            :disabled="deleting"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 font-semibold"
          >
            {{ deleting ? $t('payments.deleting') : '🗑️ ' + $t('payments.delete') }}
          </button>
        </div>
      </div>
    </div>

    <!-- EPOS Print Loading Overlay -->
    <div v-if="printingEpos" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 shadow-xl flex items-center gap-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
        <span class="text-gray-700 font-medium">{{ $t('payments.preparingEpos') }}</span>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LayoutMain from '../components/LayoutMain.vue'
import { paymentApi, bookingApi, hallBookingApi, restaurantOrderApi, laundryOrderApi } from '../api'
import axios from 'axios'

const { t, locale } = useI18n()

// ─── State ───────────────────────────────────────────────────────────────────
const payments       = ref([])
const bookings       = ref([])
const hallBookings   = ref([])
const filteredBookings = ref([])
const loading        = ref(false)
const pagination     = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const showModal      = ref(false)
const showDeleteConfirm = ref(false)
const showBookingDropdown = ref(false)
const isEditing      = ref(false)
const saving         = ref(false)
const deleting       = ref(false)
const exporting      = ref(false)
const printingEpos   = ref(false)
const error          = ref('')
const paymentToDelete = ref(null)
const bookingSearch  = ref('')

// Debounce timer
let searchTimer = null

// Hotel info (loaded from settings)
const hotelName   = ref('Hotel')
const hotelAddress = ref('')
const hotelPhone  = ref('')

// ─── Booking Status Tabs (Computed for i18n support) ──────────────────────────
const bookingStatusTabs = computed(() => [
  { value: '',                  label: '📋 ' + t('payments.allPayments') },
  { value: 'checked_out',       label: '🛏️ ' + t('payments.roomCheckout') },
  { value: 'complete',          label: '🎪 ' + t('payments.hallCompleted') },
  { value: 'checkout_or_complete', label: '✅ ' + t('payments.checkoutAndCompleted') },
])

// ─── Filters ─────────────────────────────────────────────────────────────────
const filters = ref({
  booking_status: '',
  start_date: '',
  end_date: '',
  payment_type: '',
  payment_method: '',
  search: '',
})

// ─── Form ────────────────────────────────────────────────────────────────────
const formData = ref({
  booking_type: 'room',
  booking_id: '',
  hall_booking_id: '',
  payment_type: 'full',
  payment_method: 'cash',
  amount: 0,
  restaurant_charges: 0,
  laundry_charges: 0,
  reference_number: '',
  notes: '',
})

// ─── Computed ─────────────────────────────────────────────────────────────────
const visiblePages = computed(() => {
  const total = pagination.value.last_page
  const cur   = pagination.value.current_page
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = []
  if (cur <= 4) {
    for (let i = 1; i <= 5; i++) pages.push(i)
    pages.push('...', total)
  } else if (cur >= total - 3) {
    pages.push(1, '...')
    for (let i = total - 4; i <= total; i++) pages.push(i)
  } else {
    pages.push(1, '...', cur - 1, cur, cur + 1, '...', total)
  }
  return pages
})

// ─── Lifecycle ───────────────────────────────────────────────────────────────
onMounted(async () => {
  try {
    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    await axios.get(`${apiUrl}/sanctum/csrf-cookie`, { withCredentials: true })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }

  // Load hotel info
  try {
    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    const res = await axios.get(`${apiUrl}/api/public/settings/payment`)
    const data = res.data?.data || {}
    if (data.bank_accounts?.[0]?.account_holder) {
      hotelName.value = data.bank_accounts[0].account_holder
    }
    if (data.whatsapp_number) {
      hotelPhone.value = '+' + data.whatsapp_number
    }
  } catch (_) {}

  loadPayments()
  loadBookings()
  loadHallBookings()
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
  clearTimeout(searchTimer)
})

// ─── API Calls ───────────────────────────────────────────────────────────────
async function loadPayments(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (filters.value.booking_status) params.booking_status = filters.value.booking_status
    if (filters.value.payment_type)   params.payment_type   = filters.value.payment_type
    if (filters.value.payment_method) params.payment_method = filters.value.payment_method
    if (filters.value.search)         params.search         = filters.value.search
    if (filters.value.start_date)     params.start_date     = filters.value.start_date
    if (filters.value.end_date)       params.end_date       = filters.value.end_date

    const response = await paymentApi.getPayments(params)

    if (response && response.data && Array.isArray(response.data)) {
      payments.value  = response.data
      pagination.value = {
        current_page: response.current_page,
        last_page:    response.last_page,
        per_page:     response.per_page,
        total:        response.total,
      }
    } else if (Array.isArray(response)) {
      payments.value  = response
      pagination.value = { current_page: 1, last_page: 1, per_page: 15, total: response.length }
    }
  } catch (err) {
    console.error('Failed to load payments:', err)
  } finally {
    loading.value = false
  }
}

async function loadBookings() {
  try {
    const allBookings = await bookingApi.getBookings()
    bookings.value = Array.isArray(allBookings) ? allBookings : (allBookings?.data || [])
    if (formData.value.booking_type === 'room') filteredBookings.value = bookings.value
  } catch (err) {
    console.error('Failed to load bookings:', err)
  }
}

async function loadHallBookings() {
  try {
    const allHallBookings = await hallBookingApi.getHallBookings()
    hallBookings.value = Array.isArray(allHallBookings) ? allHallBookings : (allHallBookings?.data || [])
    if (formData.value.booking_type === 'hall') filteredBookings.value = hallBookings.value
  } catch (err) {
    console.error('Failed to load hall bookings:', err)
  }
}

// ─── Filter actions ───────────────────────────────────────────────────────────
function setBookingStatusFilter(status) {
  filters.value.booking_status = status
  loadPayments()
}

function clearFilters() {
  filters.value = { booking_status: '', start_date: '', end_date: '', payment_type: '', payment_method: '', search: '' }
  loadPayments()
}

function debouncedSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => loadPayments(), 400)
}

function changePage(page) {
  if (page >= 1 && page <= pagination.value.last_page) loadPayments(page)
}

// ─── Booking search (for create modal) ───────────────────────────────────────
function switchBookingType() {
  formData.value.booking_id      = ''
  formData.value.hall_booking_id = ''
  bookingSearch.value            = ''
  filteredBookings.value = formData.value.booking_type === 'room' ? bookings.value : hallBookings.value
}

function filterBookings() {
  const search = bookingSearch.value.toLowerCase()
  const source = formData.value.booking_type === 'room' ? bookings.value : hallBookings.value

  if (!search) {
    filteredBookings.value = source
  } else {
    filteredBookings.value = source.filter(booking => {
      const num   = booking.booking_number?.toLowerCase() || ''
      const guest = (booking.guest?.name || booking.customer_name)?.toLowerCase() || ''
      const loc   = formData.value.booking_type === 'room'
        ? ''
        : booking.hall?.name?.toLowerCase() || ''
      return num.includes(search) || guest.includes(search) || loc.includes(search)
    })
  }
  showBookingDropdown.value = true
}

async function selectBooking(booking) {
  if (formData.value.booking_type === 'room') {
    formData.value.booking_id      = booking.id
    formData.value.hall_booking_id = ''
    bookingSearch.value = `${booking.booking_number} — ${booking.guest?.name || ''}`

    // Auto-fill restaurant charges
    try {
      const rc = await restaurantOrderApi.getBookingCharges(booking.id)
      formData.value.restaurant_charges = parseFloat(rc.restaurant_charges || 0)
    } catch { formData.value.restaurant_charges = 0 }

    // Auto-fill laundry charges
    try {
      const lc = await laundryOrderApi.getBookingCharges(booking.id)
      formData.value.laundry_charges = parseFloat(lc.laundry_charges || 0)
    } catch { formData.value.laundry_charges = 0 }
  } else {
    formData.value.hall_booking_id = booking.id
    formData.value.booking_id      = ''
    formData.value.restaurant_charges = 0
    formData.value.laundry_charges    = 0
    const guest = booking.customer_name || booking.guest?.name || 'N/A'
    bookingSearch.value = `${booking.booking_number} — ${guest}`
  }

  if (booking.total_amount) formData.value.amount = parseFloat(booking.total_amount)
  showBookingDropdown.value = false
}

// ─── Modal helpers ────────────────────────────────────────────────────────────
function openCreateModal() {
  isEditing.value = false
  formData.value  = {
    booking_type: 'room', booking_id: '', hall_booking_id: '',
    payment_type: 'full', payment_method: 'cash',
    amount: 0, restaurant_charges: 0, laundry_charges: 0,
    reference_number: '', notes: '',
  }
  bookingSearch.value = ''
  error.value = ''
  showModal.value = true
}

function openEditModal(payment) {
  isEditing.value = true
  formData.value  = {
    id:               payment.id,
    booking_id:       payment.booking_id,
    booking_type:     payment.booking_id ? 'room' : 'hall',
    payment_type:     payment.payment_type,
    payment_method:   payment.payment_method,
    amount:           payment.amount,
    restaurant_charges: payment.restaurant_charges || 0,
    laundry_charges:  payment.laundry_charges || 0,
    reference_number: payment.reference_number || '',
    notes:            payment.notes || '',
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  showBookingDropdown.value = false
  error.value = ''
}

// ─── Save / Delete ────────────────────────────────────────────────────────────
async function savePayment() {
  saving.value = true
  error.value  = ''
  try {
    if (isEditing.value) {
      await paymentApi.updatePayment(formData.value.id, formData.value)
    } else {
      const payload = { ...formData.value }
      if (formData.value.booking_type === 'room') payload.hall_booking_id = null
      else payload.booking_id = null
      delete payload.booking_type
      await paymentApi.createPayment(payload)
    }
    closeModal()
    await loadPayments()
  } catch (err) {
    error.value = err.response?.data?.message || t('payments.saveFailed')
  } finally {
    saving.value = false
  }
}

function confirmDelete(payment) {
  paymentToDelete.value   = payment
  showDeleteConfirm.value = true
}

async function handleDelete() {
  if (!paymentToDelete.value) return
  deleting.value = true
  try {
    await paymentApi.deletePayment(paymentToDelete.value.id)
    showDeleteConfirm.value = false
    paymentToDelete.value   = null
    await loadPayments()
  } catch (err) {
    alert(err.response?.data?.message || t('payments.deleteFailed'))
  } finally {
    deleting.value = false
  }
}

// ─── Export ───────────────────────────────────────────────────────────────────
async function exportPayments() {
  exporting.value = true
  try {
    const apiUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
    const params = new URLSearchParams()
    if (filters.value.start_date)   params.append('start_date',   filters.value.start_date)
    if (filters.value.end_date)     params.append('end_date',     filters.value.end_date)
    if (filters.value.payment_type) params.append('payment_type', filters.value.payment_type)
    if (filters.value.payment_method) params.append('payment_method', filters.value.payment_method)
    const link = document.createElement('a')
    link.href = `${apiUrl}/payments/export?${params.toString()}`
    link.setAttribute('download', '')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (err) {
    alert(t('payments.exportFailed'))
  } finally {
    exporting.value = false
  }
}

// ─── EPOS THERMAL PRINT ───────────────────────────────────────────────────────
async function printEpos(payment) {
  printingEpos.value = true
  let fullPayment = payment

  try {
    // Load full payment detail for complete data
    fullPayment = await paymentApi.getPayment(payment.id)
  } catch (err) {
    console.warn('Using cached payment data for print', err)
  } finally {
    printingEpos.value = false
  }

  openEposWindow(fullPayment)
}

function openEposWindow(payment) {
  const isRoom = !!payment.booking_id
  const guestName  = isRoom
    ? (payment.booking?.guest?.name || '-')
    : (payment.hall_booking?.customer_name || '-')
  const guestPhone = isRoom
    ? (payment.booking?.guest?.phone || '')
    : (payment.hall_booking?.customer_phone || '')
  const location = isRoom
    ? `${t('payments.eposRoom')}: ${getBookingRooms(payment)}`
    : `${t('payments.eposHall')}: ${payment.hall_booking?.hall?.name || '-'}`
  const bookingNum = isRoom
    ? (payment.booking?.booking_number || '-')
    : (payment.hall_booking?.booking_number || '-')

  const checkIn  = isRoom ? formatDateShort(payment.booking?.check_in_date)  : formatDateShort(payment.hall_booking?.event_date)
  const checkOut = isRoom ? formatDateShort(payment.booking?.check_out_date) : null
  const nights   = isRoom ? (payment.booking?.nights || '') : null

  const amount     = parseFloat(payment.amount || 0)
  const restaurant = parseFloat(payment.restaurant_charges || 0)
  const laundry    = parseFloat(payment.laundry_charges || 0)
  const total      = amount + restaurant + laundry

  const nowLocale = locale.value === 'id' ? 'id-ID' : 'en-US'
  const now = new Date().toLocaleString(nowLocale, {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })

  // Build receipt rows
  const rows = [
    { label: isRoom ? t('payments.eposRoomCharge') : t('payments.eposHallCharge'), value: formatCurrency(amount) },
    ...(restaurant > 0 ? [{ label: t('payments.eposRestaurant'), value: formatCurrency(restaurant) }] : []),
    ...(laundry    > 0 ? [{ label: t('payments.eposLaundry'),    value: formatCurrency(laundry) }] : []),
  ]
  const itemRows = rows.map(r => {
    const label = r.label.padEnd(18)
    const val   = r.value.padStart(14)
    return `<div class="row"><span>${label}</span><span>${val}</span></div>`
  }).join('')

  const html = `<!DOCTYPE html>
<html lang="${locale.value}">
<head>
<meta charset="UTF-8"/>
<title>${t('payments.eposReceiptHeader')} — ${payment.payment_number}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 11px;
    width: 80mm;
    padding: 4mm 3mm;
    background: #fff;
    color: #000;
  }
  .center  { text-align: center; }
  .bold    { font-weight: bold; }
  .big     { font-size: 14px; }
  .xlg     { font-size: 16px; }
  .sep     { border-top: 1px dashed #000; margin: 3px 0; }
  .sep2    { border-top: 2px solid #000; margin: 3px 0; }
  .row     { display: flex; justify-content: space-between; margin: 1px 0; }
  .row span:first-child { flex: 1; }
  .row span:last-child  { text-align: right; }
  .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 13px; margin: 2px 0; }
  .label-sm  { font-size: 9px; color: #555; }
  .mt2 { margin-top: 4px; }
  .mb2 { margin-bottom: 4px; }
  @media print {
    body { width: 80mm; }
    @page { size: 80mm auto; margin: 0; }
  }
</style>
</head>
<body>
  <div class="center bold xlg mb2">${hotelName.value}</div>
  ${hotelAddress.value ? `<div class="center label-sm">${hotelAddress.value}</div>` : ''}
  ${hotelPhone.value ? `<div class="center label-sm">Tel: ${hotelPhone.value}</div>` : ''}

  <div class="sep2 mt2"></div>

  <div class="center bold big mb2">${t('payments.eposReceiptHeader')}</div>
  <div class="center label-sm mb2">${now}</div>

  <div class="sep"></div>

  <div class="row"><span class="label-sm">${t('payments.eposReceiptNo')}</span><span class="bold">${payment.payment_number}</span></div>
  <div class="row"><span class="label-sm">${t('payments.eposBookingNo')}</span><span>${bookingNum}</span></div>

  <div class="sep mt2 mb2"></div>

  <div><span class="label-sm">${t('payments.eposGuest')}</span></div>
  <div class="bold">${guestName}</div>
  ${guestPhone ? `<div class="label-sm">${guestPhone}</div>` : ''}

  <div class="mt2"><span class="label-sm">${t('payments.eposType')}</span></div>
  <div>${isRoom ? '🛏  ' + t('payments.roomBooking') : '🎪  ' + t('payments.hallBooking')}</div>
  <div>${location}</div>

  ${checkIn ? `
  <div class="mt2">
    <div class="row">
      <span class="label-sm">${isRoom ? t('payments.eposCheckIn') : t('payments.eposEventDate')}</span>
      <span class="label-sm">${isRoom ? t('payments.eposCheckOut') : ''}</span>
    </div>
    <div class="row">
      <span>${checkIn}</span>
      ${checkOut ? `<span>${checkOut}</span>` : ''}
    </div>
    ${nights ? `<div class="label-sm">${nights} ${t('payments.eposNights')}</div>` : ''}
  </div>` : ''}

  <div class="sep2 mt2 mb2"></div>

  <div class="bold mb2">${t('payments.eposDetails')}</div>
  ${itemRows}

  <div class="sep2 mt2"></div>
  <div class="total-row">
    <span>${t('payments.eposTotal')}</span>
    <span>${formatCurrency(total)}</span>
  </div>
  <div class="sep2 mb2"></div>

  <div class="row mt2">
    <span class="label-sm">${t('payments.eposMethod')}</span>
    <span class="bold">${getPaymentMethodLabel(payment.payment_method).toUpperCase()}</span>
  </div>
  <div class="row">
    <span class="label-sm">${t('payments.eposTypeLabel')}</span>
    <span>${getPaymentTypeLabel(payment.payment_type)}</span>
  </div>
  ${payment.reference_number ? `<div class="row"><span class="label-sm">${t('payments.eposRef')}</span><span>${payment.reference_number}</span></div>` : ''}
  ${payment.notes ? `<div class="sep mt2"></div><div class="label-sm">${t('payments.eposNotes')}: ${payment.notes}</div>` : ''}

  <div class="sep2 mt2"></div>

  <div class="center mt2">${t('payments.eposThankYou')}</div>
  <div class="center label-sm">${t('payments.eposProofNotice')}</div>
  <div class="center label-sm mt2">*** ${payment.payment_number} ***</div>
</body>
</html>`

  const win = window.open('', '_blank', 'width=340,height=600,scrollbars=yes')
  if (!win) {
    alert(t('payments.popupBlocked'))
    return
  }
  win.document.write(html)
  win.document.close()
  setTimeout(() => {
    win.focus()
    win.print()
    // Auto-close after print dialog
    setTimeout(() => win.close(), 500)
  }, 300)
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function getBookingRooms(payment) {
  if (!payment.booking) return '-'
  const rooms = payment.booking.rooms
  if (!rooms || rooms.length === 0) return '-'
  return rooms.map(r => r.room_number).join(', ')
}

function formatCurrency(amount) {
  const numLocale = locale.value === 'id' ? 'id-ID' : 'en-US'
  return new Intl.NumberFormat(numLocale, {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
  }).format(amount || 0)
}

function formatNumber(value) {
  const numLocale = locale.value === 'id' ? 'id-ID' : 'en-US'
  return new Intl.NumberFormat(numLocale).format(value)
}

function formatDate(date) {
  if (!date) return '-'
  const dateLocale = locale.value === 'id' ? 'id-ID' : 'en-US'
  return new Date(date).toLocaleDateString(dateLocale, {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

function formatDateShort(date) {
  if (!date) return null
  const dateLocale = locale.value === 'id' ? 'id-ID' : 'en-US'
  return new Date(date).toLocaleDateString(dateLocale, {
    day: '2-digit', month: 'short', year: 'numeric'
  })
}

function getPaymentTotal(payment) {
  return parseFloat(payment.amount || 0)
    + parseFloat(payment.restaurant_charges || 0)
    + parseFloat(payment.laundry_charges || 0)
}

function getPaymentTypeBadgeClass(type) {
  const classes = {
    deposit:      'bg-blue-100 text-blue-800',
    partial:      'bg-yellow-100 text-yellow-800',
    full:         'bg-green-100 text-green-800',
    refund:       'bg-red-100 text-red-800',
    extra_charge: 'bg-purple-100 text-purple-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

function getPaymentTypeLabel(type) {
  const labels = {
    deposit:      t('payments.deposit'),
    partial:      t('payments.partial'),
    full:         t('payments.full'),
    refund:       t('payments.refund'),
    extra_charge: t('payments.extraCharge'),
  }
  return labels[type] || type
}

function getPaymentMethodBadgeClass(method) {
  const classes = {
    cash:     'bg-green-100 text-green-800',
    transfer: 'bg-blue-100 text-blue-800',
    qris:     'bg-purple-100 text-purple-800',
    card:     'bg-indigo-100 text-indigo-800',
    other:    'bg-gray-100 text-gray-800',
  }
  return classes[method] || 'bg-gray-100 text-gray-800'
}

function getPaymentMethodLabel(method) {
  const labels = {
    cash:     t('payments.cash'),
    transfer: t('payments.transfer'),
    qris:     'QRIS',
    card:     t('payments.card'),
    other:    t('payments.other'),
  }
  return labels[method] || method
}

function getStatusLabel(status) {
  const labels = {
    pending:      t('dashboard.pending'),
    confirmed:    t('dashboard.confirmed'),
    checked_in:   t('dashboard.checkedIn'),
    checked_out:  t('dashboard.checkedOut'),
    cancelled:    t('dashboard.cancelled'),
    complete:     t('payments.hallCompleted'),
  }
  return labels[status] || status
}

// Close booking dropdown when clicking outside
function handleOutsideClick(e) {
  if (!e.target.closest('.relative')) {
    showBookingDropdown.value = false
  }
}
document.addEventListener('click', handleOutsideClick)
</script>
