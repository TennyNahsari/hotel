<template>
  <LayoutMain>
    <div class="space-y-4 md:space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $t('bookings.title') }}</h1>
          <p class="text-gray-600 mt-1 text-xs sm:text-sm md:text-base">{{ $t('bookings.subtitle') }}</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button
            @click="loadBookings"
            :disabled="loading"
            class="px-3 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"
            title="Refresh Data Booking"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Refresh</span>
          </button>
          <button
            @click="openCreateModal"
            class="w-full sm:w-auto px-4 py-2 text-sm md:text-base bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
          >
            + {{ $t('bookings.newBooking') }}
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-3 md:p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
          <div class="lg:col-span-2">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.search') }}</label>
            <input
              v-model="filters.search"
              @input="handleFilterChange"
              type="text"
              :placeholder="$t('bookings.searchPlaceholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.status') }}</label>
            <select
              v-model="filters.status"
              @change="handleFilterChange"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">{{ $t('bookings.allStatus') }}</option>
              <option value="pending">{{ $t('bookings.pending') }}</option>
              <option value="confirmed">{{ $t('bookings.confirmed') }}</option>
              <option value="checked_in">{{ $t('bookings.checkedIn') }}</option>
              <option value="checked_out">{{ $t('bookings.checkedOut') }}</option>
              <option value="cancelled">{{ $t('bookings.cancelled') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.checkInFrom') }}</label>
            <input
              v-model="filters.start_date"
              @change="handleFilterChange"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.checkInTo') }}</label>
            <input
              v-model="filters.end_date"
              @change="handleFilterChange"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
        <div class="mt-3 md:mt-4 flex justify-end">
          <button
            @click="exportBookings"
            :disabled="exporting"
            class="w-full sm:w-auto px-4 py-2 text-sm md:text-base bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <svg v-if="exporting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ exporting ? $t('bookings.exporting') : $t('bookings.exportExcel') }}
          </button>
        </div>
      </div>

      <!-- Bookings Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="text-gray-500 mt-2">{{ $t('bookings.loading') }}</p>
        </div>

        <div v-else-if="bookings.length === 0" class="text-center py-12">
          <p class="text-gray-500">{{ $t('bookings.noBookings') }}</p>
        </div>

        <div v-else>
          <!-- Mobile Card View -->
          <div class="block md:hidden">
            <div v-for="booking in bookings" :key="booking.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
              <div class="space-y-3">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-mono font-bold text-gray-900 flex items-center gap-1.5">
                      <span>{{ booking.booking_number }}</span>
                      <span
                        :class="booking.source === 'website' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700'"
                        class="px-1.5 py-0.5 text-[10px] rounded font-sans font-semibold"
                      >
                        {{ booking.source === 'website' ? '🌐 Website' : 'Walk-In' }}
                      </span>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">{{ booking.guest?.name }}</div>
                    <div v-if="booking.guest?.phone" class="text-xs text-emerald-700 font-medium">
                      💬 WA: <a :href="'https://wa.me/' + booking.guest?.phone.replace(/[^0-9]/g, '')" target="_blank" class="hover:underline font-mono">{{ booking.guest?.phone }}</a>
                    </div>
                  </div>
                  <span :class="getStatusBadgeClass(booking.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(booking.status) }}
                  </span>
                </div>
                <div class="text-sm text-gray-600 space-y-1.5 border-t border-b border-gray-100 py-2 my-1">
                  <!-- Rooms List for Mobile Card -->
                  <div v-if="booking.rooms && booking.rooms.length > 0" class="space-y-1">
                    <div v-if="booking.rooms.length > 1" class="mb-1">
                      <span class="px-1.5 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-bold rounded border border-purple-200">
                        Multi-Room ({{ booking.rooms.length }} Kamar)
                      </span>
                    </div>
                    <div v-for="room in booking.rooms" :key="room.id" class="text-xs bg-gray-50 p-2 rounded border border-gray-200 flex flex-col gap-0.5">
                      <div class="flex items-center justify-between font-medium">
                        <span class="font-bold text-gray-900">Kamar {{ room.room_number }}</span>
                        <span v-if="room.pivot?.subtotal" class="font-mono text-xs text-gray-700 font-semibold">{{ formatCurrency(room.pivot.subtotal) }}</span>
                      </div>
                      <div class="text-[11px] text-gray-600 font-mono">
                        📅 {{ formatDate(room.pivot?.check_in_date || booking.check_in_date) }} - {{ formatDate(room.pivot?.check_out_date || booking.check_out_date) }}
                      </div>
                    </div>
                  </div>
                  <div class="text-xs text-gray-500 pt-0.5">{{ booking.nights }} {{ $t('bookings.nights') }} • {{ booking.adults }} {{ $t('bookings.adults') }}, {{ booking.children }} {{ $t('bookings.children') }}</div>
                  <div class="font-bold text-gray-900 text-base pt-0.5">{{ formatCurrency(booking.total_amount) }}</div>
                  <div v-if="getBookingRefNumber(booking)" class="text-xs text-purple-700 font-mono font-medium">
                    💳 {{ getBookingRefNumber(booking) }}
                  </div>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-if="booking.status === 'pending'"
                    @click="handleConfirm(booking.id)"
                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                  >
                    {{ $t('bookings.confirmAction') }}
                  </button>
                  <button
                    v-if="booking.status === 'confirmed'"
                    @click="handleCheckIn(booking.id)"
                    class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200"
                  >
                    {{ $t('bookings.checkInAction') }}
                  </button>
                  <button
                    v-if="booking.status === 'checked_in'"
                    @click="handleCheckOut(booking.id)"
                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                  >
                    {{ $t('bookings.checkOutAction') }}
                  </button>
                  <button
                    v-if="['pending', 'confirmed'].includes(booking.status)"
                    @click="openEditModal(booking)"
                    class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                  >
                    {{ $t('bookings.editAction') }}
                  </button>
                  <button
                    v-if="['pending', 'confirmed'].includes(booking.status)"
                    @click="confirmCancel(booking)"
                    class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200"
                  >
                    {{ $t('bookings.cancelAction') }}
                  </button>
                  <button
                    v-if="['cancelled', 'checked_out'].includes(booking.status)"
                    @click="confirmDeleteBooking(booking)"
                    class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
                  >
                    {{ $t('bookings.deleteAction') }}
                  </button>
                  <button
                    @click="viewBooking(booking)"
                    class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                  >
                    Detail
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('bookings.booking') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('bookings.guest') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('bookings.checkInOut') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('bookings.rooms') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('bookings.total') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('bookings.statusActions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 font-mono">{{ booking.booking_number }}</div>
                  <div class="text-xs text-gray-500">{{ booking.nights }} {{ $t('bookings.nights') }}</div>
                  <div class="mt-1">
                    <span
                      :class="booking.source === 'website' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-gray-100 text-gray-700 border-gray-200'"
                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold border"
                    >
                      <span>{{ booking.source === 'website' ? '🌐 Website' : '🏨 Walk-In' }}</span>
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ booking.guest?.name }}
                  </div>
                  <div v-if="booking.guest?.phone" class="text-xs text-emerald-700 font-medium mt-0.5 flex items-center gap-1">
                    <span>💬 WA:</span>
                    <a :href="'https://wa.me/' + booking.guest?.phone.replace(/[^0-9]/g, '')" target="_blank" class="hover:underline font-mono">
                      {{ booking.guest?.phone }}
                    </a>
                  </div>
                  <div v-if="booking.guest?.email" class="text-xs text-gray-500">
                    {{ booking.guest?.email }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ formatDate(booking.check_in_date) }}</div>
                  <div class="text-sm text-gray-500">{{ formatDate(booking.check_out_date) }}</div>
                  <div v-if="booking.rooms && booking.rooms.length > 1" class="text-[10px] text-purple-700 font-medium mt-0.5">
                    (Rentang Keseluruhan)
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="space-y-1.5 min-w-[200px]">
                    <div v-if="booking.rooms && booking.rooms.length > 1" class="mb-1">
                      <span class="px-1.5 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-bold rounded border border-purple-200">
                        Multi-Room ({{ booking.rooms.length }} Kamar)
                      </span>
                    </div>
                    <div v-for="room in booking.rooms" :key="room.id" class="text-xs bg-gray-50 p-2 rounded border border-gray-200 flex flex-col gap-1">
                      <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-900">Kamar {{ room.room_number }}</span>
                        <span v-if="room.pivot?.subtotal" class="font-mono text-xs text-gray-700 font-semibold">{{ formatCurrency(room.pivot.subtotal) }}</span>
                      </div>
                      <div class="text-[11px] text-gray-600 font-mono flex items-center gap-1 bg-white px-1.5 py-0.5 rounded border border-gray-100">
                        <span>📅 {{ formatDate(room.pivot?.check_in_date || booking.check_in_date) }} - {{ formatDate(room.pivot?.check_out_date || booking.check_out_date) }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">{{ booking.adults }} {{ $t('bookings.adults') }}, {{ booking.children }} {{ $t('bookings.children') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ formatCurrency(booking.total_amount) }}</div>
                  <div v-if="booking.deposit_amount > 0" class="text-xs text-gray-500">
                    {{ $t('bookings.deposit') }}: {{ formatCurrency(booking.deposit_amount) }}
                  </div>
                  <div v-if="getBookingRefNumber(booking)" class="mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-semibold bg-purple-50 text-purple-700 border border-purple-200 whitespace-nowrap">
                      💳 {{ getBookingRefNumber(booking) }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="mb-2 space-y-1">
                    <div class="flex items-center gap-1">
                      <span v-if="booking.status === 'pending' && booking.payment_due_at && new Date() > new Date(booking.payment_due_at)" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                        Telat Bayar
                      </span>
                      <span v-else :class="getStatusBadgeClass(booking.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                        {{ getStatusLabel(booking.status) }}
                      </span>
                    </div>
                    <div v-if="booking.payment_due_at" class="text-[11px] font-mono text-gray-500">
                      🕒 Batas Bayar: {{ formatDateTime(booking.payment_due_at) }}
                    </div>
                  </div>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-if="booking.status === 'pending'"
                      @click="handleConfirm(booking.id)"
                      class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                      :title="$t('bookings.confirmAction')"
                    >
                      {{ $t('bookings.confirmAction') }}
                    </button>
                    <button
                      v-if="booking.status === 'confirmed'"
                      @click="handleCheckIn(booking.id)"
                      class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200"
                      :title="$t('bookings.checkInAction')"
                    >
                      {{ $t('bookings.checkInAction') }}
                    </button>
                    <button
                      v-if="booking.status === 'checked_in'"
                      @click="handleCheckOut(booking.id)"
                      class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                      :title="$t('bookings.checkOutAction')"
                    >
                      {{ $t('bookings.checkOutAction') }}
                    </button>
                    <button
                      v-if="booking.status === 'pending'"
                      @click="openEditModal(booking)"
                      class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                      :title="$t('bookings.editAction')"
                    >
                      {{ $t('bookings.editAction') }}
                    </button>
                    <button
                      v-if="['pending', 'confirmed'].includes(booking.status)"
                      @click="confirmCancel(booking)"
                      class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200"
                      :title="$t('bookings.cancelAction')"
                    >
                      {{ $t('bookings.cancelAction') }}
                    </button>
                    <button
                      v-if="['pending', 'cancelled'].includes(booking.status)"
                      @click="confirmDeleteBooking(booking)"
                      class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
                      :title="$t('bookings.deleteAction')"
                    >
                      {{ $t('bookings.deleteAction') }}
                    </button>
                    <button
                      @click="viewBooking(booking)"
                      class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                      :title="$t('bookings.viewAction')"
                    >
                      Detail
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-sm text-gray-700">
              {{ $t('bookings.showing') }}
              <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
              {{ $t('bookings.to') }}
              <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
              {{ $t('bookings.of') }}
              <span class="font-medium">{{ pagination.total }}</span>
              {{ $t('bookings.results') }}
            </div>
            <div class="flex gap-1">
              <button
                @click="changePage(1)"
                :disabled="pagination.current_page === 1"
                class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
              >
                ««
              </button>
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
              >
                «
              </button>
              
              <template v-for="page in getPageNumbers()" :key="page">
                <button
                  v-if="page !== '...'"
                  @click="changePage(page)"
                  :class="[
                    'px-3 py-1 border rounded text-sm',
                    pagination.current_page === page
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'border-gray-300 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
                <span v-else class="px-2 py-1 text-sm text-gray-500">...</span>
              </template>

              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
              >
                »
              </button>
              <button
                @click="changePage(pagination.last_page)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
              >
                »»
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Booking Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? $t('bookings.editBooking') : $t('bookings.createBooking') }}
        </h2>

        <form @submit.prevent="saveBooking" class="space-y-4">
          <!-- Guest Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.guestLabel') }}</label>
            <select
              v-model="formData.guest_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">{{ $t('bookings.guestPlaceholder') }}</option>
              <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                {{ guest.name }} - {{ guest.email || guest.phone }}
              </option>
            </select>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.checkInLabel') }}</label>
              <input
                v-model="formData.check_in_date"
                type="date"
                required
                :min="today"
                @change="checkAvailability"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.checkOutLabel') }}</label>
              <input
                v-model="formData.check_out_date"
                type="date"
                required
                :min="formData.check_in_date"
                @change="checkAvailability"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <!-- Guests Count -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.adultsLabel') }}</label>
              <input
                v-model.number="formData.adults"
                type="number"
                min="1"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.childrenLabel') }}</label>
              <input
                v-model.number="formData.children"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <!-- Available Rooms -->
          <div v-if="availableRooms.length > 0">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('bookings.selectRoomsLabel') }}</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-2">
              <label
                v-for="room in availableRooms"
                :key="room.id"
                class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer"
              >
                <input
                  type="checkbox"
                  :value="room.id"
                  v-model="formData.room_ids"
                  class="mr-2"
                />
                <span class="text-sm">
                  {{ room.room_number }} - {{ room.room_type?.name }}
                  ({{ formatCurrency(room.room_type?.base_price) }}/{{ $t('bookings.roomNight') }})
                </span>
              </label>
            </div>
          </div>
          <div v-else-if="formData.check_in_date && formData.check_out_date" class="text-sm text-amber-600">
            {{ $t('bookings.noRoomsAvailable') }}
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('bookings.specialRequests') }}</label>
            <textarea
              v-model="formData.special_requests"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :placeholder="$t('bookings.specialRequestsPlaceholder')"
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
              {{ $t('bookings.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="saving || formData.room_ids.length === 0"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ saving ? $t('bookings.saving') : (isEditing ? $t('bookings.updateBooking') : $t('bookings.createBooking')) }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div
      v-if="showCancelConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showCancelConfirm = false"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">{{ $t('bookings.cancelBookingTitle') }}</h2>
        <p class="text-gray-600 mb-6 text-sm md:text-base">
          {{ $t('bookings.cancelBookingMessage') }} <strong>{{ bookingToCancel?.booking_number }}</strong>?
          {{ $t('bookings.cancelBookingInfo') }}
        </p>
        <div class="flex gap-3">
          <button
            @click="showCancelConfirm = false"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            {{ $t('bookings.noKeepIt') }}
          </button>
          <button
            @click="handleCancel"
            :disabled="cancelling"
            class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50"
          >
            {{ cancelling ? $t('bookings.cancelling') : $t('bookings.yesCancelIt') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showDeleteConfirm = false"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">{{ $t('bookings.deleteBookingTitle') }}</h2>
        <p class="text-gray-600 mb-6 text-sm md:text-base">
          {{ $t('bookings.deleteBookingMessage') }} <strong>{{ bookingToDelete?.booking_number }}</strong>?
          {{ $t('bookings.deleteCannotUndo') }}
        </p>
        <div class="flex gap-3">
          <button
            @click="showDeleteConfirm = false"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            {{ $t('bookings.cancel') }}
          </button>
          <button
            @click="handleDeleteBooking"
            :disabled="deleting"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
          >
            {{ deleting ? $t('bookings.deleting') : $t('bookings.deleteAction') }}
          </button>
        </div>
      </div>
    </div>

    <!-- View Booking Details Modal -->
    <div
      v-if="showViewModal && selectedBookingDetail"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeViewModal"
    >
      <div class="bg-white rounded-lg max-w-xl w-full p-6 shadow-2xl relative">
        <button
          @click="closeViewModal"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="space-y-4">
          <div class="border-b pb-3 flex justify-between items-start">
            <div>
              <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Detail Reservasi</span>
              <div class="flex items-center gap-2 mt-0.5">
                <h2 class="text-2xl font-bold text-gray-900 font-mono">{{ selectedBookingDetail.booking_number }}</h2>
                <span
                  :class="selectedBookingDetail.source === 'website' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-gray-100 text-gray-700 border-gray-200'"
                  class="px-2 py-0.5 text-xs font-semibold rounded border"
                >
                  {{ selectedBookingDetail.source === 'website' ? '🌐 Website' : '🏨 Walk-In' }}
                </span>
              </div>
            </div>
            <span :class="getStatusBadgeClass(selectedBookingDetail.status)" class="px-3 py-1 text-xs font-bold rounded-full uppercase">
              {{ getStatusLabel(selectedBookingDetail.status) }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-gray-500 text-xs block">Nama Tamu</span>
              <span class="font-semibold text-gray-900">{{ selectedBookingDetail.guest?.name || '-' }}</span>
            </div>
            <div>
              <span class="text-gray-500 text-xs block">Kontak / WhatsApp</span>
              <span v-if="selectedBookingDetail.guest?.phone" class="font-medium text-emerald-700 block">
                💬 WA: <a :href="'https://wa.me/' + selectedBookingDetail.guest?.phone.replace(/[^0-9]/g, '')" target="_blank" class="hover:underline font-mono">{{ selectedBookingDetail.guest?.phone }}</a>
              </span>
              <span v-if="selectedBookingDetail.guest?.email" class="text-xs text-gray-600 block">
                {{ selectedBookingDetail.guest?.email }}
              </span>
            </div>
            <div>
              <span class="text-gray-500 text-xs block">Tanggal Check-In</span>
              <span class="font-medium text-gray-900">{{ formatDate(selectedBookingDetail.check_in_date) }}</span>
            </div>
            <div>
              <span class="text-gray-500 text-xs block">Tanggal Check-Out</span>
              <span class="font-medium text-gray-900">{{ formatDate(selectedBookingDetail.check_out_date) }}</span>
            </div>
            <div>
              <span class="text-gray-500 text-xs block">Durasi & Tamu</span>
              <span class="font-medium text-gray-900">{{ selectedBookingDetail.nights }} Malam • {{ selectedBookingDetail.adults }} Dewasa, {{ selectedBookingDetail.children }} Anak</span>
            </div>
            <div class="col-span-2">
              <span class="text-gray-500 text-xs block mb-1">Rincian Kamar & Tanggal Menginap</span>
              <div class="font-medium text-gray-900 space-y-2">
                <template v-if="selectedBookingDetail.rooms && selectedBookingDetail.rooms.length > 0">
                  <div v-if="selectedBookingDetail.rooms.length > 1" class="mb-1">
                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-xs font-bold rounded border border-purple-200">
                      Multi-Room ({{ selectedBookingDetail.rooms.length }} Kamar)
                    </span>
                  </div>
                  <div class="grid grid-cols-1 gap-2">
                    <div v-for="room in selectedBookingDetail.rooms" :key="room.id" class="text-xs bg-gray-50 p-2.5 rounded-lg border border-gray-200 space-y-1">
                      <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-900 text-sm">Kamar {{ room.room_number }} <span class="font-normal text-gray-500 text-xs">({{ room.room_type?.name || room.roomType?.name }})</span></span>
                        <span v-if="room.pivot?.subtotal" class="font-mono text-blue-700 font-bold text-sm">{{ formatCurrency(room.pivot.subtotal) }}</span>
                      </div>
                      <div class="flex items-center gap-2 text-gray-600 font-mono text-xs pt-1 border-t border-gray-200/60">
                        <span>🗓️ Check-In: <strong>{{ formatDate(room.pivot?.check_in_date || selectedBookingDetail.check_in_date) }}</strong></span>
                        <span>→</span>
                        <span>Check-Out: <strong>{{ formatDate(room.pivot?.check_out_date || selectedBookingDetail.check_out_date) }}</strong></span>
                      </div>
                    </div>
                  </div>
                </template>
                <template v-else>-</template>
              </div>
            </div>
          </div>

          <div class="border-t border-b py-3 my-2 bg-gray-50 p-3 rounded-lg space-y-2">
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-600">Total Biaya Kamar</span>
              <span class="font-bold text-gray-900 text-base">{{ formatCurrency(selectedBookingDetail.total_amount) }}</span>
            </div>
            <div v-if="selectedBookingDetail.deposit_amount > 0" class="flex justify-between items-center text-sm">
              <span class="text-gray-600">DP Jaminan Terbayar</span>
              <span class="font-bold text-blue-700">{{ formatCurrency(selectedBookingDetail.deposit_amount) }}</span>
            </div>

            <!-- Reference Number -->
            <div v-if="getBookingRefNumber(selectedBookingDetail)" class="flex justify-between items-center text-sm pt-1 border-t border-gray-200">
              <span class="text-gray-600 font-medium">Nomor Referensi Transfer / Pembayaran</span>
              <span class="font-mono font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded text-xs">
                {{ getBookingRefNumber(selectedBookingDetail) }}
              </span>
            </div>

            <!-- Receipt Link -->
            <div v-if="getBookingReceiptUrl(selectedBookingDetail)" class="flex justify-between items-center text-sm pt-1.5 border-t border-gray-200">
              <span class="text-gray-600 font-medium">Foto Struk Transfer</span>
              <a
                :href="getBookingReceiptUrl(selectedBookingDetail)"
                target="_blank"
                class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded border border-blue-200 transition-colors"
              >
                🖼️ Buka Struk Transfer (Foto/PDF)
              </a>
            </div>
          </div>

          <div v-if="selectedBookingDetail.special_requests" class="text-xs text-gray-600">
            <strong class="text-gray-800 block">Permintaan Khusus:</strong>
            <p class="bg-gray-50 p-2 rounded border border-gray-200 mt-1">{{ selectedBookingDetail.special_requests }}</p>
          </div>

          <div class="flex justify-end pt-2">
            <button
              @click="closeViewModal"
              class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-semibold rounded-lg hover:bg-gray-300"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import LayoutMain from '../components/LayoutMain.vue'
import { bookingApi, guestApi, roomApi } from '../api'
import axios from 'axios'

const { t } = useI18n()

const bookings = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})
const guests = ref([])
const availableRooms = ref([])
const loading = ref(false)
const exporting = ref(false)
const showModal = ref(false)
const showCancelConfirm = ref(false)
const showDeleteConfirm = ref(false)
const showViewModal = ref(false)
const selectedBookingDetail = ref(null)
const isEditing = ref(false)
const saving = ref(false)
const cancelling = ref(false)
const deleting = ref(false)
const error = ref('')
const bookingToCancel = ref(null)
const bookingToDelete = ref(null)

const filters = ref({
  search: '',
  status: '',
  start_date: '',
  end_date: '',
  page: 1,
})

const formData = ref({
  guest_id: '',
  check_in_date: '',
  check_out_date: '',
  adults: 1,
  children: 0,
  room_ids: [],
  special_requests: '',
})

const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

onMounted(async () => {
  // Ensure CSRF cookie
  try {
    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    await axios.get(`${apiUrl}/sanctum/csrf-cookie`, {
      withCredentials: true
    })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }
  
  loadBookings()
  loadGuests()
})

async function loadBookings() {
  loading.value = true
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.start_date) params.start_date = filters.value.start_date
    if (filters.value.end_date) params.end_date = filters.value.end_date
    if (filters.value.page) params.page = filters.value.page

    const response = await bookingApi.getBookings(params)
    
    // Handle paginated response
    if (response.data) {
      bookings.value = response.data
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total
      }
    } else {
      // Fallback for non-paginated response
      bookings.value = response
    }
  } catch (err) {
    console.error('Failed to load bookings:', err)
  } finally {
    loading.value = false
  }
}

function changePage(page) {
  filters.value.page = page
  loadBookings()
}

function handleFilterChange() {
  filters.value.page = 1 // Reset to first page when filtering
  loadBookings()
}

async function loadGuests() {
  try {
    guests.value = await guestApi.getGuests()
  } catch (err) {
    console.error('Failed to load guests:', err)
  }
}

async function checkAvailability() {
  if (!formData.value.check_in_date || !formData.value.check_out_date) return
  
  try {
    availableRooms.value = await bookingApi.checkAvailability({
      check_in_date: formData.value.check_in_date,
      check_out_date: formData.value.check_out_date,
    })
  } catch (err) {
    console.error('Failed to check availability:', err)
  }
}

async function exportBookings() {
  exporting.value = true
  try {
    const params = {}
    if (filters.value.start_date) params.start_date = filters.value.start_date
    if (filters.value.end_date) params.end_date = filters.value.end_date
    if (filters.value.status) params.status = filters.value.status
    
    // Build query string
    const queryString = new URLSearchParams(params).toString()
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
    const url = `${apiBaseUrl}/bookings/export${queryString ? '?' + queryString : ''}`
    
    // Download the file
    const response = await axios.get(url, {
      responseType: 'blob',
      withCredentials: true
    })
    
    // Create a blob URL and trigger download
    const blob = new Blob([response.data], { 
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    })
    const downloadUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = downloadUrl
    link.download = `bookings_${new Date().toISOString().split('T')[0]}.xlsx`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(downloadUrl)
  } catch (err) {
    console.error('Failed to export bookings:', err)
    alert(t('bookings.exportFailed'))
  } finally {
    exporting.value = false
  }
}

function openCreateModal() {
  isEditing.value = false
  formData.value = {
    guest_id: '',
    check_in_date: '',
    check_out_date: '',
    adults: 1,
    children: 0,
    room_ids: [],
    special_requests: '',
  }
  availableRooms.value = []
  error.value = ''
  showModal.value = true
}

function openEditModal(booking) {
  isEditing.value = true
  formData.value = {
    id: booking.id,
    guest_id: booking.guest_id,
    check_in_date: booking.check_in_date,
    check_out_date: booking.check_out_date,
    adults: booking.adults,
    children: booking.children,
    room_ids: booking.rooms.map(r => r.id),
    special_requests: booking.special_requests || '',
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  error.value = ''
}

async function saveBooking() {
  saving.value = true
  error.value = ''

  try {
    if (isEditing.value) {
      await bookingApi.updateBooking(formData.value.id, formData.value)
    } else {
      await bookingApi.createBooking(formData.value)
    }
    closeModal()
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || t('bookings.saveFailed')
  } finally {
    saving.value = false
  }
}

async function handleConfirm(bookingId) {
  try {
    await bookingApi.confirm(bookingId)
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || t('bookings.confirmFailed'))
  }
}

async function handleCheckIn(bookingId) {
  try {
    await bookingApi.checkIn(bookingId)
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || t('bookings.checkInFailed'))
  }
}

async function handleCheckOut(bookingId) {
  try {
    await bookingApi.checkOut(bookingId)
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || t('bookings.checkOutFailed'))
  }
}

function confirmCancel(booking) {
  bookingToCancel.value = booking
  showCancelConfirm.value = true
}

async function handleCancel() {
  if (!bookingToCancel.value) return
  
  cancelling.value = true
  try {
    await bookingApi.cancel(bookingToCancel.value.id)
    showCancelConfirm.value = false
    bookingToCancel.value = null
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || t('bookings.cancelFailed'))
  } finally {
    cancelling.value = false
  }
}

function confirmDeleteBooking(booking) {
  bookingToDelete.value = booking
  showDeleteConfirm.value = true
}

async function handleDeleteBooking() {
  if (!bookingToDelete.value) return
  
  deleting.value = true
  try {
    await bookingApi.deleteBooking(bookingToDelete.value.id)
    showDeleteConfirm.value = false
    bookingToDelete.value = null
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || t('bookings.deleteFailed'))
  } finally {
    deleting.value = false
  }
}

function viewBooking(booking) {
  selectedBookingDetail.value = booking
  showViewModal.value = true
}

function closeViewModal() {
  showViewModal.value = false
  selectedBookingDetail.value = null
}

function getBookingRefNumber(booking) {
  if (booking && booking.payments && booking.payments.length > 0) {
    const p = booking.payments.find(p => p.reference_number)
    if (p) return p.reference_number
  }
  return null
}

function getBookingReceiptUrl(booking) {
  if (booking && booking.payments && booking.payments.length > 0) {
    const p = booking.payments.find(p => p.receipt_path)
    if (p && p.receipt_path) {
      const cleanPath = p.receipt_path.replace(/^public\//, '').replace(/^storage\//, '')
      return 'http://localhost:8000/storage/' + cleanPath
    }
  }
  return null
}

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    checked_in: 'bg-green-100 text-green-800',
    checked_out: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
  const labels = {
    pending: t('bookings.pending'),
    confirmed: t('bookings.confirmed'),
    checked_in: t('bookings.checkedIn'),
    checked_out: t('bookings.checkedOut'),
    cancelled: t('bookings.cancelled'),
  }
  return labels[status] || status
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

function formatDateTime(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return dateStr
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }) + ' WIB'
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

function getPageNumbers() {
  const pages = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  
  if (last <= 7) {
    // Show all pages if 7 or less
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    // Always show first page
    pages.push(1)
    
    if (current > 3) {
      pages.push('...')
    }
    
    // Show pages around current
    const start = Math.max(2, current - 1)
    const end = Math.min(last - 1, current + 1)
    
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }
    
    if (current < last - 2) {
      pages.push('...')
    }
    
    // Always show last page
    pages.push(last)
  }
  
  return pages
}
</script>
