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
              @input="loadPayments"
              type="text"
              :placeholder="$t('payments.searchPlaceholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
        <div class="mt-3 md:mt-4">
          <button
            @click="exportPayments"
            :disabled="exporting"
            class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white text-sm md:text-base rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ exporting ? $t('payments.exporting') : '📊 ' + $t('payments.exportExcel') }}
          </button>
        </div>
      </div>

      <!-- Payments List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="text-gray-500 mt-2">{{ $t('payments.loading') }}</p>
        </div>

        <div v-else-if="payments.length === 0" class="text-center py-12">
          <p class="text-gray-500">{{ $t('payments.noPayments') }}</p>
        </div>

        <div v-else>
          <!-- Mobile Card View -->
          <div class="block md:hidden">
            <div v-for="payment in payments" :key="payment.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
              <div class="space-y-3">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900">{{ payment.payment_number }}</div>
                    <div class="text-sm text-gray-600">{{ payment.booking?.booking_number || payment.hall_booking?.booking_number }}</div>
                    <div class="text-sm text-gray-600">{{ payment.booking?.guest?.name || payment.hall_booking?.customer_name }}</div>
                  </div>
                  <div class="text-right">
                    <div class="font-semibold text-gray-900">{{ formatCurrency(getPaymentTotal(payment)) }}</div>
                  </div>
                </div>
                <div class="flex flex-wrap gap-2">
                  <span :class="getPaymentTypeBadgeClass(payment.payment_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPaymentTypeLabel(payment.payment_type) }}
                  </span>
                  <span :class="getPaymentMethodBadgeClass(payment.payment_method)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPaymentMethodLabel(payment.payment_method) }}
                  </span>
                </div>
                <div class="text-sm text-gray-600">
                  {{ formatDate(payment.created_at) }}
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    @click="printInvoice(payment)"
                    class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200"
                  >
                    {{ $t('payments.print') }}
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
                    {{ $t('payments.delete') }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination (Mobile) -->
          <div v-if="pagination.last_page > 1" class="md:hidden mt-4 px-4 pb-4">
            <div class="text-sm text-gray-700 mb-2 text-center">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </div>
            <div class="flex gap-2 justify-center">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-4 py-2 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-4 py-2 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.paymentNumber') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.booking') }} / {{ $t('payments.guest') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.type') }} / {{ $t('payments.method') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.amount') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.date') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('payments.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ payment.payment_number }}</div>
                  <div class="text-sm text-gray-500">{{ payment.reference_number || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="payment.booking_id" class="text-sm font-medium text-gray-900">{{ payment.booking?.guest?.name }}</div>
                  <div v-else class="text-sm font-medium text-gray-900">{{ payment.hall_booking?.customer_name }}</div>
                  <div v-if="payment.booking_id" class="text-sm text-gray-500">Room {{ payment.booking?.room?.room_number }}</div>
                  <div v-else class="text-sm text-gray-500">Hall {{ payment.hall_booking?.hall?.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <span :class="getPaymentTypeBadgeClass(payment.payment_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getPaymentTypeLabel(payment.payment_type) }}
                    </span>
                  </div>
                  <div class="mt-1 text-xs text-gray-500">{{ getPaymentMethodLabel(payment.payment_method) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">{{ formatCurrency(getPaymentTotal(payment)) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(payment.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex gap-2">
                    <button
                      @click="printInvoice(payment)"
                      class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200"
                    >
                      {{ $t('payments.print') }}
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
                      {{ $t('payments.delete') }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="pagination.last_page > 1" class="mt-4 flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
              {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of 
              {{ pagination.total }} payments
            </div>
            <div class="flex gap-2">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
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
                {{ $t('payments.roomBooking') }}
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
                {{ $t('payments.hallBooking') }}
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
                placeholder="Search booking number or guest name..."
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
                    {{ booking.guest?.name }} - Room {{ booking.room?.room_number }}
                    <span class="text-xs text-gray-500">({{ getStatusLabel(booking.status) }})</span>
                  </div>
                  <div v-else class="text-sm text-gray-600">
                    {{ booking.customer_name }} - {{ booking.hall?.name }}
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
              placeholder="0.00"
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
              placeholder="0.00"
            />
            <p class="text-xs text-gray-500 mt-1">Auto-filled from restaurant orders</p>
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
              placeholder="0.00"
            />
            <p class="text-xs text-gray-500 mt-1">Auto-filled from laundry orders</p>
          </div>

          <div v-if="formData.amount > 0 || formData.restaurant_charges > 0 || formData.laundry_charges > 0" class="col-span-2 p-3 bg-blue-50 rounded-lg">
            <div class="space-y-1">
              <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">{{ formData.booking_type === 'room' ? $t('payments.roomAmount') : $t('payments.hallAmount') }}:</span>
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
              <div class="border-t border-blue-200 pt-1 mt-1"></div>
              <div class="flex justify-between items-center">
                <span class="font-medium text-gray-700">{{ $t('payments.totalAmount') }}:</span>
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
              placeholder="Transaction ID, transfer number..."
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('payments.notes') }}</label>
            <textarea
              v-model="formData.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Additional notes..."
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
              {{ saving ? $t('payments.saving') : (isEditing ? $t('payments.save') : $t('payments.save')) }}
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
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $t('payments.delete') }}?</h2>
        <p class="text-gray-600 mb-6">
          {{ $t('payments.confirmDelete') }}
        </p>
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
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
          >
            {{ deleting ? $t('payments.saving') : $t('payments.delete') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Print Invoice Template (hidden) -->
    <div ref="invoiceTemplate" class="hidden print:block">
      <div v-if="selectedPayment" class="p-8 bg-white max-w-4xl mx-auto">
        <div class="border-2 border-gray-800 p-8">
          <!-- Header -->
          <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900">{{ $t('payments.invoice').toUpperCase() }}</h1>
            <p class="text-gray-600 mt-2">Payment Receipt</p>
          </div>

          <!-- Company & Customer Info -->
          <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
              <h3 class="font-bold text-gray-900 mb-2">{{ $t('payments.from') }}:</h3>
              <p class="text-gray-700 font-semibold">Your Hotel Name</p>
              <p class="text-gray-600">Jl. Hotel Address</p>
              <p class="text-gray-600">City, Province 12345</p>
              <p class="text-gray-600">Phone: (021) 1234-5678</p>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 mb-2">{{ $t('payments.to') }}:</h3>
              <template v-if="selectedPayment.booking_id">
                <p class="text-gray-700 font-semibold">{{ selectedPayment.booking?.guest?.name }}</p>
                <p class="text-gray-600">{{ selectedPayment.booking?.guest?.email }}</p>
                <p class="text-gray-600">{{ selectedPayment.booking?.guest?.phone }}</p>
              </template>
              <template v-else>
                <p class="text-gray-700 font-semibold">{{ selectedPayment.hall_booking?.customer_name }}</p>
                <p class="text-gray-600">{{ selectedPayment.hall_booking?.customer_email }}</p>
                <p class="text-gray-600">{{ selectedPayment.hall_booking?.customer_phone }}</p>
              </template>
            </div>
          </div>

          <!-- Invoice Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 bg-gray-50 p-4">
            <div>
              <p class="text-sm text-gray-600">Payment Number</p>
              <p class="font-bold text-gray-900">{{ selectedPayment.payment_number }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Payment Date</p>
              <p class="font-bold text-gray-900">{{ formatDate(selectedPayment.created_at) }}</p>
            </div>
            <div v-if="selectedPayment.booking_id">
              <p class="text-sm text-gray-600">Room Number</p>
              <p class="font-bold text-gray-900">{{ selectedPayment.booking?.room?.room_number }}</p>
            </div>
            <div v-else>
              <p class="text-sm text-gray-600">Hall Name</p>
              <p class="font-bold text-gray-900">{{ selectedPayment.hall_booking?.hall?.name }}</p>
            </div>
            <div v-if="selectedPayment.booking_id">
              <p class="text-sm text-gray-600">Room Type</p>
              <p class="font-bold text-gray-900">{{ selectedPayment.booking?.room?.room_type?.name }}</p>
            </div>
            <div v-else>
              <p class="text-sm text-gray-600">Event Type</p>
              <p class="font-bold text-gray-900">{{ selectedPayment.hall_booking?.event_type }}</p>
            </div>
          </div>

          <!-- Payment Details Table -->
          <table class="w-full mb-8">
            <thead>
              <tr class="border-b-2 border-gray-800">
                <th class="text-left py-2 px-4">{{ $t('payments.description') }}</th>
                <th class="text-right py-2 px-4">{{ $t('payments.amount') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-b border-gray-300">
                <td class="py-3 px-4">
                  <p class="font-medium">{{ getPaymentTypeLabel(selectedPayment.payment_type) }}</p>
                  <p class="text-sm text-gray-600">{{ $t('payments.paymentMethod') }}: {{ getPaymentMethodLabel(selectedPayment.payment_method) }}</p>
                  <p v-if="selectedPayment.reference_number" class="text-sm text-gray-600">
                    Ref: {{ selectedPayment.reference_number }}
                  </p>
                </td>
                <td class="py-3 px-4 text-right font-bold">{{ formatCurrency(selectedPayment.amount) }}</td>
              </tr>
              <tr v-if="selectedPayment.restaurant_charges > 0" class="border-b border-gray-300">
                <td class="py-3 px-4">
                  <p class="font-medium">{{ $t('payments.restaurantCharges') }}</p>
                </td>
                <td class="py-3 px-4 text-right font-bold">{{ formatCurrency(selectedPayment.restaurant_charges) }}</td>
              </tr>
              <tr v-if="selectedPayment.laundry_charges > 0" class="border-b border-gray-300">
                <td class="py-3 px-4">
                  <p class="font-medium">{{ $t('payments.laundryCharges') }}</p>
                </td>
                <td class="py-3 px-4 text-right font-bold">{{ formatCurrency(selectedPayment.laundry_charges) }}</td>
              </tr>
              <tr class="border-t-2 border-gray-800">
                <td class="py-3 px-4 text-right font-bold">{{ $t('payments.total').toUpperCase() }}</td>
                <td class="py-3 px-4 text-right font-bold text-xl">{{ formatCurrency(getPaymentTotal(selectedPayment)) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Notes -->
          <div v-if="selectedPayment.notes" class="mb-8">
            <h3 class="font-bold text-gray-900 mb-2">{{ $t('payments.notes') }}:</h3>
            <p class="text-gray-600">{{ selectedPayment.notes }}</p>
          </div>

          <!-- Footer -->
          <div class="text-center mt-8 pt-8 border-t border-gray-300">
            <p class="text-gray-600">Thank you for your payment!</p>
            <p class="text-sm text-gray-500 mt-2">This is a computer-generated receipt.</p>
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
import { paymentApi, bookingApi, hallBookingApi, restaurantOrderApi, laundryOrderApi } from '../api'
import axios from 'axios'

const { t } = useI18n()

const payments = ref([])
const bookings = ref([])
const hallBookings = ref([])
const filteredBookings = ref([])
const selectedPayment = ref(null)
const loading = ref(false)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
})
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const showBookingDropdown = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const deleting = ref(false)
const exporting = ref(false)
const error = ref('')
const paymentToDelete = ref(null)
const invoiceTemplate = ref(null)
const bookingSearch = ref('')

const filters = ref({
  start_date: '',
  end_date: '',
  payment_type: '',
  payment_method: '',
  search: '',
})

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

onMounted(async () => {
  try {
    const apiUrl = import.meta.env.VITE_API_URL || 'https://hotel.tazkia.web.id'
    await axios.get(`${apiUrl}/sanctum/csrf-cookie`, {
      withCredentials: true
    })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }
  
  loadPayments()
  loadBookings()
  loadHallBookings()
})

async function loadPayments(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (filters.value.payment_type) params.payment_type = filters.value.payment_type
    if (filters.value.payment_method) params.payment_method = filters.value.payment_method
    if (filters.value.search) params.search = filters.value.search

    const response = await paymentApi.getPayments(params)
    
    // Handle paginated response
    if (response.data && Array.isArray(response.data)) {
      payments.value = response.data
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total
      }
    } else {
      // Fallback for non-paginated response
      payments.value = response
    }
  } catch (err) {
    console.error('Failed to load payments:', err)
  } finally {
    loading.value = false
  }
}

function changePage(page) {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadPayments(page)
  }
}

async function loadBookings() {
  try {
    // Load all bookings for search
    const allBookings = await bookingApi.getBookings()
    bookings.value = allBookings
    if (formData.value.booking_type === 'room') {
      filteredBookings.value = allBookings
    }
  } catch (err) {
    console.error('Failed to load bookings:', err)
  }
}

async function loadHallBookings() {
  try {
    const allHallBookings = await hallBookingApi.getHallBookings()
    hallBookings.value = allHallBookings
    if (formData.value.booking_type === 'hall') {
      filteredBookings.value = allHallBookings
    }
  } catch (err) {
    console.error('Failed to load hall bookings:', err)
  }
}

function switchBookingType() {
  // Clear selection when switching
  formData.value.booking_id = ''
  formData.value.hall_booking_id = ''
  bookingSearch.value = ''
  
  // Update filtered bookings based on type
  if (formData.value.booking_type === 'room') {
    filteredBookings.value = bookings.value
  } else {
    filteredBookings.value = hallBookings.value
  }
}

function filterBookings() {
  const search = bookingSearch.value.toLowerCase()
  const sourceBookings = formData.value.booking_type === 'room' ? bookings.value : hallBookings.value
  
  if (!search) {
    filteredBookings.value = sourceBookings
  } else {
    filteredBookings.value = sourceBookings.filter(booking => {
      const bookingNumber = booking.booking_number?.toLowerCase() || ''
      const guestName = (booking.guest?.name || booking.customer_name)?.toLowerCase() || ''
      const location = formData.value.booking_type === 'room' 
        ? booking.room?.room_number?.toString() || ''
        : booking.hall?.name?.toLowerCase() || ''
      return bookingNumber.includes(search) || 
             guestName.includes(search) || 
             location.includes(search)
    })
  }
  showBookingDropdown.value = true
}

async function selectBooking(booking) {
  if (formData.value.booking_type === 'room') {
    formData.value.booking_id = booking.id
    formData.value.hall_booking_id = ''
    bookingSearch.value = `${booking.booking_number} - ${booking.guest?.name}`
    
    // Auto-fill restaurant charges for room bookings
    try {
      const charges = await restaurantOrderApi.getBookingCharges(booking.id)
      formData.value.restaurant_charges = parseFloat(charges.restaurant_charges || 0)
    } catch (error) {
      console.error('Failed to load restaurant charges:', error)
      formData.value.restaurant_charges = 0
    }
    
    // Auto-fill laundry charges for room bookings
    try {
      const charges = await laundryOrderApi.getBookingCharges(booking.id)
      formData.value.laundry_charges = parseFloat(charges.laundry_charges || 0)
    } catch (error) {
      console.error('Failed to load laundry charges:', error)
      formData.value.laundry_charges = 0
    }
  } else {
    formData.value.hall_booking_id = booking.id
    formData.value.booking_id = ''
    formData.value.restaurant_charges = 0 // Hall bookings don't have restaurant charges
    formData.value.laundry_charges = 0 // Hall bookings don't have laundry charges
    const guestName = booking.customer_name || booking.guest?.name || 'N/A'
    bookingSearch.value = `${booking.booking_number} - ${guestName}`
  }
  
  // Auto-fill amount with booking total
  if (booking.total_amount) {
    formData.value.amount = parseFloat(booking.total_amount)
  }
  
  showBookingDropdown.value = false
}

function openCreateModal() {
  isEditing.value = false
  formData.value = {
    booking_type: 'room',
    booking_id: '',
    hall_booking_id: '',
    payment_type: 'full',
    payment_method: 'cash',
    amount: 0,
    restaurant_charges: 0,
    reference_number: '',
    notes: '',
  }
  bookingSearch.value = ''
  error.value = ''
  showModal.value = true
}

function openEditModal(payment) {
  isEditing.value = true
  formData.value = {
    id: payment.id,
    booking_id: payment.booking_id,
    payment_type: payment.payment_type,
    payment_method: payment.payment_method,
    amount: payment.amount,
    reference_number: payment.reference_number || '',
    notes: payment.notes || '',
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  showBookingDropdown.value = false
  error.value = ''
}

async function savePayment() {
  saving.value = true
  error.value = ''

  try {
    if (isEditing.value) {
      await paymentApi.updatePayment(formData.value.id, formData.value)
    } else {
      // Create payment payload based on booking type
      const payload = { ...formData.value }
      if (formData.value.booking_type === 'room') {
        payload.hall_booking_id = null
      } else {
        payload.booking_id = null
      }
      // Remove booking_type from payload as it's not a backend field
      delete payload.booking_type
      await paymentApi.createPayment(payload)
    }
    
    closeModal()
    await loadPayments()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save payment'
  } finally {
    saving.value = false
  }
}

function confirmDelete(payment) {
  paymentToDelete.value = payment
  showDeleteConfirm.value = true
}

async function handleDelete() {
  if (!paymentToDelete.value) return
  
  deleting.value = true
  try {
    await paymentApi.deletePayment(paymentToDelete.value.id)
    showDeleteConfirm.value = false
    paymentToDelete.value = null
    await loadPayments()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete payment')
  } finally {
    deleting.value = false
  }
}

async function exportPayments() {
  exporting.value = true
  try {
    const apiUrl = import.meta.env.VITE_API_BASE_URL || 'https://hotel.tazkia.web.id/api'
    
    // Build query parameters
    const params = new URLSearchParams()
    if (filters.value.start_date) params.append('start_date', filters.value.start_date)
    if (filters.value.end_date) params.append('end_date', filters.value.end_date)
    if (filters.value.payment_type) params.append('payment_type', filters.value.payment_type)
    if (filters.value.payment_method) params.append('payment_method', filters.value.payment_method)
    
    const url = `${apiUrl}/payments/export?${params.toString()}`
    
    // Create temporary link and trigger download
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', '')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (err) {
    console.error('Failed to export payments:', err)
    alert('Failed to export payments')
  } finally {
    exporting.value = false
  }
}

async function printInvoice(payment) {
  try {
    // Load full payment details
    selectedPayment.value = await paymentApi.getPayment(payment.id)
    
    // Wait for DOM update
    await new Promise(resolve => setTimeout(resolve, 100))
    
    // Print
    window.print()
  } catch (err) {
    console.error('Failed to load payment details:', err)
    alert('Failed to load payment details for printing')
  }
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

function formatNumber(value) {
  return new Intl.NumberFormat('id-ID').format(value)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getPaymentTotal(payment) {
  const amount = parseFloat(payment.amount || 0)
  const restaurantCharges = parseFloat(payment.restaurant_charges || 0)
  const laundryCharges = parseFloat(payment.laundry_charges || 0)
  return amount + restaurantCharges + laundryCharges
}

function getPaymentTypeBadgeClass(type) {
  const classes = {
    deposit: 'bg-blue-100 text-blue-800',
    partial: 'bg-yellow-100 text-yellow-800',
    full: 'bg-green-100 text-green-800',
    refund: 'bg-red-100 text-red-800',
    extra_charge: 'bg-purple-100 text-purple-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

function getPaymentTypeLabel(type) {
  const labels = {
    deposit: t('payments.deposit'),
    partial: t('payments.partial'),
    full: t('payments.full'),
    refund: t('payments.refund'),
    extra_charge: t('payments.extraCharge'),
  }
  return labels[type] || type
}

function getPaymentMethodBadgeClass(method) {
  const classes = {
    cash: 'bg-green-100 text-green-800',
    transfer: 'bg-blue-100 text-blue-800',
    qris: 'bg-purple-100 text-purple-800',
    card: 'bg-indigo-100 text-indigo-800',
    other: 'bg-gray-100 text-gray-800',
  }
  return classes[method] || 'bg-gray-100 text-gray-800'
}

function getPaymentMethodLabel(method) {
  const labels = {
    cash: t('payments.cash'),
    transfer: t('payments.transfer'),
    qris: t('payments.qris'),
    card: t('payments.card'),
    other: t('payments.other'),
  }
  return labels[method] || method
}

function getStatusLabel(status) {
  const labels = {
    pending: t('dashboard.pending'),
    confirmed: t('dashboard.confirmed'),
    checked_in: t('dashboard.checkedIn'),
    checked_out: t('dashboard.checkedOut'),
    cancelled: t('dashboard.cancelled'),
  }
  return labels[status] || status
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
  if (!e.target.closest('.relative')) {
    showBookingDropdown.value = false
  }
})
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  
  .print\:block,
  .print\:block * {
    visibility: visible;
  }
  
  .print\:block {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
