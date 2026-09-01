<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $t('dashboard.title') }}</h1>
          <p class="text-gray-600 mt-1 text-xs sm:text-sm md:text-base">{{ $t('dashboard.subtitle') }}</p>
        </div>
        <button
          @click="handleRefreshDashboard"
          :disabled="refreshing || loading"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-forest text-white text-xs sm:text-sm font-semibold rounded-lg hover:bg-forest-800 transition-colors shadow disabled:opacity-50"
        >
          <svg :class="['w-4 h-4', refreshing ? 'animate-spin' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>{{ refreshing ? 'Memproses Status...' : 'Refresh Status Booking' }}</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-forest"></div>
        <p class="text-gray-500 mt-2">{{ $t('dashboard.loading') }}</p>
      </div>

      <!-- Stats Cards -->
      <div v-else class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
          <!-- Available Rooms -->
          <div class="bg-white rounded-lg shadow p-4 md:p-6 border border-sand/20">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-forest rounded-md p-3">
                <svg class="h-6 w-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.availableRooms') }}</dt>
                  <dd class="text-2xl font-semibold text-gray-900">
                    {{ dashboard.rooms?.available || 0 }}/{{ dashboard.rooms?.total || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Today's Check-ins -->
          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.todayCheckIns') }}</dt>
                  <dd class="text-2xl font-semibold text-gray-900">{{ dashboard.bookings?.today_check_ins || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Today's Check-outs -->
          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.todayCheckOuts') }}</dt>
                  <dd class="text-2xl font-semibold text-gray-900">{{ dashboard.bookings?.today_check_outs || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Today's Revenue -->
          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.todayRevenue') }}</dt>
                  <dd class="text-2xl font-semibold text-gray-900">
                    {{ formatCurrency(dashboard.revenue?.today_full_payments || 0) }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
          <!-- Occupied Rooms -->
          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="text-sm font-medium text-gray-500">{{ $t('dashboard.occupiedRooms') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ dashboard.rooms?.occupied || 0 }}</div>
          </div>

          <!-- Pending Bookings -->
          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="text-sm font-medium text-gray-500">{{ $t('dashboard.pendingBookings') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ dashboard.bookings?.pending || 0 }}</div>
          </div>

          <!-- Pending Tasks -->
          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="text-sm font-medium text-gray-500">{{ $t('dashboard.pendingTasks') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ dashboard.housekeeping?.pending || 0 }}</div>
          </div>

          <!-- Month Revenue -->
          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="text-sm font-medium text-gray-500">{{ $t('dashboard.monthRevenue') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">
              {{ formatCurrency(dashboard.revenue?.month_full_payments || 0) }}
            </div>
          </div>
        </div>

        <!-- AI Predictions Section -->
        <AIPredictionsCard />

        <!-- Payment Report Section -->
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
          <h2 class="text-base md:text-lg font-semibold text-gray-900 mb-4">{{ $t('dashboard.paymentReport') }}</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4">
            <!-- Full Payments Today -->
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
              <div class="text-xs font-medium text-green-600 uppercase">{{ $t('dashboard.fullPayment') }}</div>
              <div class="mt-2 text-xl font-bold text-green-700">
                {{ formatCurrency(dashboard.revenue?.today_full_payments || 0) }}
              </div>
              <div class="text-xs text-green-600 mt-1">
                {{ dashboard.payments?.today_by_type?.full?.count || 0 }} {{ $t('dashboard.transactions') }}
              </div>
            </div>

            <!-- Deposit Today -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
              <div class="text-xs font-medium text-blue-600 uppercase">{{ $t('dashboard.deposit') }}</div>
              <div class="mt-2 text-xl font-bold text-blue-700">
                {{ formatCurrency(dashboard.payments?.today_by_type?.deposit?.total || 0) }}
              </div>
              <div class="text-xs text-blue-600 mt-1">
                {{ dashboard.payments?.today_by_type?.deposit?.count || 0 }} {{ $t('dashboard.transactions') }}
              </div>
            </div>

            <!-- Partial Today -->
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
              <div class="text-xs font-medium text-yellow-600 uppercase">{{ $t('dashboard.partial') }}</div>
              <div class="mt-2 text-xl font-bold text-yellow-700">
                {{ formatCurrency(dashboard.payments?.today_by_type?.partial?.total || 0) }}
              </div>
              <div class="text-xs text-yellow-600 mt-1">
                {{ dashboard.payments?.today_by_type?.partial?.count || 0 }} {{ $t('dashboard.transactions') }}
              </div>
            </div>

            <!-- Refund Today -->
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
              <div class="text-xs font-medium text-red-600 uppercase">{{ $t('dashboard.refund') }}</div>
              <div class="mt-2 text-xl font-bold text-red-700">
                {{ formatCurrency(dashboard.payments?.today_by_type?.refund?.total || 0) }}
              </div>
              <div class="text-xs text-red-600 mt-1">
                {{ dashboard.payments?.today_by_type?.refund?.count || 0 }} {{ $t('dashboard.transactions') }}
              </div>
            </div>

            <!-- Total Today -->
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
              <div class="text-xs font-medium text-purple-600 uppercase">{{ $t('dashboard.totalToday') }}</div>
              <div class="mt-2 text-xl font-bold text-purple-700">
                {{ formatCurrency(dashboard.revenue?.today_all_payments || 0) }}
              </div>
              <div class="text-xs text-purple-600 mt-1">{{ $t('dashboard.allPayments') }}</div>
            </div>
          </div>

          <!-- Monthly Summary -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ $t('dashboard.monthSummary') }}</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-600">{{ $t('dashboard.fullPaymentsMonth') }}</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                  {{ formatCurrency(dashboard.revenue?.month_full_payments || 0) }}
                </div>
              </div>
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-600">{{ $t('dashboard.allPaymentsMonth') }}</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                  {{ formatCurrency(dashboard.revenue?.month_all_payments || 0) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Bookings -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ $t('dashboard.recentBookings') }}</h2>
          </div>
          <div v-if="!dashboard.recent_bookings || dashboard.recent_bookings.length === 0" class="p-6">
            <div class="text-center text-gray-500 py-8">
              <p>{{ $t('dashboard.noRecentBookings') }}</p>
            </div>
          </div>
          <div v-else>
            <!-- Mobile View -->
            <div class="block md:hidden">
              <div v-for="booking in dashboard.recent_bookings" :key="booking.id" class="p-4 border-b border-gray-200 last:border-b-0">
                <div class="space-y-2">
                  <div class="flex justify-between items-start">
                    <div>
                      <div class="font-medium text-gray-900">{{ booking.guest?.name }}</div>
                      <div class="text-xs text-gray-500">{{ booking.guest?.email }}</div>
                    </div>
                    <span v-if="isOverdue(booking)" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                      {{ $t('dashboard.overdue') }}
                    </span>
                    <span v-else :class="getStatusBadgeClass(booking.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getStatusLabel(booking.status) }}
                    </span>
                  </div>
                  <div class="text-xs text-gray-600 space-y-1">
                    <div>Kamar {{ booking.room?.room_number || '-' }} - {{ booking.room?.room_type?.name || '-' }}</div>
                    <div>{{ formatDate(booking.check_in_date) }} → {{ formatDate(booking.check_out_date) }}</div>
                    <div v-if="booking.payment_due_at" class="font-mono text-gray-700">
                      🕒 {{ $t('dashboard.paymentDue') }}: <span :class="isOverdue(booking) ? 'text-red-600 font-bold' : ''">{{ formatDateTime(booking.payment_due_at) }}</span>
                    </div>
                    <div v-if="booking.receipt_url" class="pt-1">
                      <a :href="booking.receipt_url" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-xs font-semibold">
                        📎 {{ $t('dashboard.paymentReceipt') }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('dashboard.guest') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('dashboard.room') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('dashboard.checkIn') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('dashboard.checkOut') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('dashboard.paymentDue') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('dashboard.paymentReceipt') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('dashboard.status') }}</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="booking in dashboard.recent_bookings" :key="booking.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ booking.guest?.name }}</div>
                    <div class="text-xs text-gray-500">{{ booking.guest?.email }}</div>
                    <div v-if="booking.guest?.phone" class="mt-1">
                      <a
                        :href="formatWaLink(booking.guest?.phone)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-800 font-medium hover:underline bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/80 transition-colors shadow-2xs"
                        title="Chat via WhatsApp"
                      >
                        <svg class="w-3.5 h-3.5 fill-emerald-600 flex-shrink-0" viewBox="0 0 24 24">
                          <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span class="font-mono">{{ booking.guest?.phone }}</span>
                      </a>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ booking.room?.room_number || '-' }}</div>
                    <div class="text-sm text-gray-500">{{ booking.room?.room_type?.name || '-' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(booking.check_in_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(booking.check_out_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                    <span v-if="booking.payment_due_at" :class="isOverdue(booking) ? 'text-red-600 font-bold' : 'text-gray-700'">
                      {{ formatDateTime(booking.payment_due_at) }}
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="booking.receipt_url" class="space-y-1">
                      <a
                        :href="booking.receipt_url"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-xs font-semibold hover:bg-emerald-100 transition-colors shadow-2xs"
                      >
                        <span>📎</span> Lihat Struk
                      </a>
                      <div v-if="booking.reference_number" class="text-[10px] text-gray-500 font-mono">
                        Ref: {{ booking.reference_number }}
                      </div>
                    </div>
                    <div v-else-if="booking.has_receipt || booking.reference_number" class="space-y-0.5">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[11px] font-semibold">
                        ✓ Ada Pembayaran
                      </span>
                      <div v-if="booking.reference_number" class="text-[10px] text-gray-500 font-mono">
                        Ref: {{ booking.reference_number }}
                      </div>
                    </div>
                    <span v-else class="text-xs text-gray-400 italic">Belum ada</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="isOverdue(booking)" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                      Telat Bayar
                    </span>
                    <span v-else :class="getStatusBadgeClass(booking.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getStatusLabel(booking.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
            </div>
          </div>
        </div>

        <!-- Recent Hall Bookings -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Pemesanan Gedung / Hall Terbaru</h2>
          </div>
          <div v-if="!dashboard.recent_hall_bookings || dashboard.recent_hall_bookings.length === 0" class="p-6">
            <div class="text-center text-gray-500 py-8">
              <p>Belum ada pemesanan hall terbaru</p>
            </div>
          </div>
          <div v-else>
            <!-- Mobile View -->
            <div class="block md:hidden">
              <div v-for="hb in dashboard.recent_hall_bookings" :key="hb.id" class="p-4 border-b border-gray-200 last:border-b-0">
                <div class="space-y-2">
                  <div class="flex justify-between items-start">
                    <div>
                      <div class="font-medium text-gray-900">{{ hb.customer_name }}</div>
                      <div class="text-xs text-gray-500">{{ hb.customer_email }}</div>
                      <div v-if="hb.customer_phone || hb.guest?.phone" class="mt-1">
                        <a
                          :href="formatWaLink(hb.customer_phone || hb.guest?.phone)"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="inline-flex items-center gap-1.5 text-xs text-emerald-700 font-medium bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/80"
                        >
                          <svg class="w-3.5 h-3.5 fill-emerald-600 flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                          </svg>
                          <span class="font-mono">{{ hb.customer_phone || hb.guest?.phone }}</span>
                        </a>
                      </div>
                    </div>
                    <span :class="getStatusBadgeClass(hb.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getStatusLabel(hb.status) }}
                    </span>
                  </div>
                  <div class="text-xs text-gray-600 space-y-1">
                    <div>Hall {{ hb.hall?.name || '-' }} - {{ hb.event_name }}</div>
                    <div>Tanggal: {{ formatDate(hb.event_date) }} ({{ hb.start_time }} - {{ hb.end_time }})</div>
                    <div v-if="hb.receipt_url" class="pt-1">
                      <a :href="hb.receipt_url" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-xs font-semibold">
                        📎 {{ $t('dashboard.paymentReceipt') }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hall & Acara</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Acara</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Struk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="hb in dashboard.recent_hall_bookings" :key="hb.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ hb.customer_name }}</div>
                      <div class="text-xs text-gray-500">{{ hb.customer_email }}</div>
                      <div v-if="hb.customer_phone || hb.guest?.phone" class="mt-1">
                        <a
                          :href="formatWaLink(hb.customer_phone || hb.guest?.phone)"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="inline-flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-800 font-medium hover:underline bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/80 transition-colors shadow-2xs"
                          title="Chat via WhatsApp"
                        >
                          <svg class="w-3.5 h-3.5 fill-emerald-600 flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                          </svg>
                          <span class="font-mono">{{ hb.customer_phone || hb.guest?.phone }}</span>
                        </a>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ hb.hall?.name || '-' }}</div>
                      <div class="text-xs text-gray-500">{{ hb.event_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(hb.event_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="hb.receipt_url" class="space-y-1">
                        <a
                          :href="hb.receipt_url"
                          target="_blank"
                          class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-xs font-semibold hover:bg-emerald-100 transition-colors shadow-2xs"
                        >
                          <span>📎</span> Lihat Struk
                        </a>
                      </div>
                      <span v-else class="text-xs text-gray-400 italic">Belum ada</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusBadgeClass(hb.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                        {{ getStatusLabel(hb.status) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
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
import AIPredictionsCard from '../components/AIPredictionsCard.vue'
import { dashboardApi } from '../api'
import axios from 'axios'

const { t } = useI18n()
const dashboard = ref({})
const loading = ref(false)
const refreshing = ref(false)

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
  
  loadDashboard()
})

async function loadDashboard() {
  loading.value = true
  try {
    dashboard.value = await dashboardApi.getDashboard()
  } catch (err) {
    console.error('Failed to load dashboard:', err)
  } finally {
    loading.value = false
  }
}

async function handleRefreshDashboard() {
  refreshing.value = true
  try {
    dashboard.value = await dashboardApi.refreshDashboard()
  } catch (err) {
    console.error('Failed to refresh dashboard:', err)
  } finally {
    refreshing.value = false
  }
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
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

function isOverdue(booking) {
  if (!booking) return false
  if (booking.is_overdue) return true
  if (booking.status === 'pending' && booking.payment_due_at) {
    return new Date() > new Date(booking.payment_due_at)
  }
  return false
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
    pending: t('dashboard.pending'),
    confirmed: t('dashboard.confirmed'),
    checked_in: t('dashboard.checkedIn'),
    checked_out: t('dashboard.checkedOut'),
    cancelled: t('dashboard.cancelled'),
  }
  return labels[status] || status
}

function formatWaLink(phone) {
  if (!phone) return '#'
  let cleaned = String(phone).replace(/[^0-9]/g, '')
  if (cleaned.startsWith('0')) {
    cleaned = '62' + cleaned.slice(1)
  }
  return `https://wa.me/${cleaned}`
}
</script>
