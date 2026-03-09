<template>
  <div class="bg-white rounded-lg shadow p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
      <div>
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
          </svg>
          AI Predictions
        </h2>
        <p class="text-sm text-gray-500 mt-1">Smart forecasting for better decision making</p>
      </div>

      <!-- Owner Controls -->
      <div v-if="isAdmin" class="flex flex-wrap gap-2">
        <button
          @click="handleTrain"
          :disabled="training"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2 text-sm"
        >
          <svg v-if="training" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ training ? 'Training...' : 'Fetch & Train' }}</span>
        </button>
        
        <button
          @click="handlePredict"
          :disabled="predicting"
          class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2 text-sm"
        >
          <svg v-if="predicting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ predicting ? 'Predicting...' : 'Generate Predictions' }}</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
      <p class="text-gray-500 mt-2 text-sm">Loading predictions...</p>
    </div>

    <!-- No Predictions -->
    <div v-else-if="!predictions" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No predictions available</h3>
      <p class="mt-1 text-sm text-gray-500">Train models and generate predictions to see AI insights</p>
    </div>

    <!-- Predictions Grid -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
      <!-- Room Demand Card -->
      <div v-if="predictions.room_demand" class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-blue-900">Room Demand Forecast</h3>
          <span class="text-xs px-2 py-1 bg-blue-200 text-blue-800 rounded-full">
            {{ Math.round(predictions.room_demand.confidence_score * 100) }}% confidence
          </span>
        </div>
        
        <div class="space-y-2">
          <div v-for="(day, index) in predictions.room_demand.data" :key="index" 
               class="bg-white/60 rounded p-2">
            <div class="flex justify-between items-center mb-1">
              <span class="text-xs text-gray-700 font-medium">
                {{ formatPredictionDate(day.date) }}
              </span>
              <span v-if="day.is_weekend" class="text-xs px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded">
                Weekend
              </span>
            </div>
            <!-- Top 3 Predictions -->
            <div class="space-y-0.5">
              <div v-for="(pred, pidx) in day.predictions.slice(0, 3)" :key="pidx" class="flex items-center gap-1">
                <span class="text-xs font-bold text-blue-600 bg-blue-200 rounded-full w-4 h-4 flex items-center justify-center text-[10px]">
                  {{ pidx + 1 }}
                </span>
                <span class="text-xs text-gray-700 font-medium">{{ pred.room_type }}:</span>
                <span class="text-xs text-blue-700 font-semibold">{{ pred.demand }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3 text-xs text-blue-700">
          7-day forecast with top 3 room types per day
        </div>
      </div>

      <!-- Hall Peak Dates Card -->
      <div v-if="predictions.hall_peaks" class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-purple-900">Hall Peak Dates</h3>
          <span class="text-xs px-2 py-1 bg-purple-200 text-purple-800 rounded-full">
            {{ Math.round(predictions.hall_peaks.confidence_score * 100) }}% confidence
          </span>
        </div>
        
        <div class="space-y-2">
          <div v-for="(peak, index) in predictions.hall_peaks.data.peak_dates" :key="index"
               class="bg-white/60 rounded p-2">
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-700 font-medium">
                {{ formatPredictionDate(peak.date) }}
              </span>
              <span class="text-xs px-2 py-0.5 bg-red-200 text-red-800 rounded font-medium">
                PEAK
              </span>
            </div>
            <div class="mt-1 text-xs text-gray-600">
              Expected: <span class="font-medium text-purple-700">{{ peak.expected_bookings }} bookings</span>
              <span v-if="peak.is_weekend" class="ml-1 text-yellow-600">(Weekend)</span>
            </div>
          </div>
        </div>

        <div class="mt-3 text-xs text-purple-700">
          7-day hall booking peak forecast
        </div>
      </div>

      <!-- Menu Popularity Card -->
      <div v-if="predictions.menu_popularity" class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-green-900">Popular Menu Items</h3>
          <span class="text-xs px-2 py-1 bg-green-200 text-green-800 rounded-full">
            {{ Math.round(predictions.menu_popularity.confidence_score * 100) }}% confidence
          </span>
        </div>
        
        <div class="space-y-2">
          <div v-for="(item, index) in predictions.menu_popularity.data.top_10" :key="index"
               class="bg-white/60 rounded p-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-green-700 bg-green-200 rounded-full w-5 h-5 flex items-center justify-center">
                {{ index + 1 }}
              </span>
              <span class="text-xs text-gray-700 font-medium truncate">
                {{ item.menu_name }}
              </span>
            </div>
            <span class="text-xs font-semibold text-green-700">
              {{ Math.round(item.popularity_score) }}
            </span>
          </div>
        </div>

        <div class="mt-3 text-xs text-green-700">
          Top {{ predictions.menu_popularity.data.top_10.length }} most popular menu items
        </div>
      </div>
    </div>

    <!-- Footer Info -->
    <div v-if="predictions" class="mt-4 pt-4 border-t border-gray-200 flex flex-col sm:flex-row sm:justify-between gap-2 text-xs text-gray-500">
      <div>
        Generated: <span class="font-medium">{{ formatTimestamp(predictions.room_demand?.generated_at) }}</span>
      </div>
      <div v-if="modelInfo">
        Models: <span class="font-medium">{{ modelInfo.total_models }} active</span> •
        Avg accuracy: <span class="font-medium">{{ averageAccuracy }}%</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import mlApi from '@/api/ml'

const authStore = useAuthStore()
const isAdmin = computed(() => {
  const roleName = authStore.user?.role?.name || authStore.user?.role
  return roleName === 'owner'
})

const loading = ref(false)
const training = ref(false)
const predicting = ref(false)
const predictions = ref(null)
const modelInfo = ref(null)

const averageAccuracy = computed(() => {
  if (!modelInfo.value?.models) return 0
  const total = modelInfo.value.models.reduce((sum, m) => sum + m.accuracy, 0)
  return Math.round(total / modelInfo.value.models.length)
})

onMounted(() => {
  loadPredictions()
  loadModelInfo()
})

async function loadPredictions() {
  loading.value = true
  try {
    const response = await mlApi.getPredictions()
    if (response.success) {
      predictions.value = response.data
    }
  } catch (err) {
    console.error('Failed to load predictions:', err)
    // Silently fail - predictions might not exist yet
  } finally {
    loading.value = false
  }
}

async function loadModelInfo() {
  try {
    const response = await mlApi.getModelInfo()
    if (response.success) {
      modelInfo.value = response.data
    }
  } catch (err) {
    console.error('Failed to load model info:', err)
  }
}

async function handleTrain() {
  if (!confirm('Training models will analyze 6+ months of data and may take 5-10 minutes. Continue?')) {
    return
  }

  training.value = true
  try {
    const response = await mlApi.train()
    if (response.success) {
      alert(`Models trained successfully!\n\n${response.data.models_trained} models trained\nAverage accuracy: ${response.data.average_accuracy}%`)
      await loadModelInfo()
    }
  } catch (err) {
    alert('Training failed: ' + (err.response?.data?.message || err.message))
  } finally {
    training.value = false
  }
}

async function handlePredict() {
  predicting.value = true
  try {
    const response = await mlApi.generatePredictions()
    if (response.success) {
      alert('Predictions generated successfully!')
      await loadPredictions()
    }
  } catch (err) {
    alert('Prediction generation failed: ' + (err.response?.data?.message || err.message))
  } finally {
    predicting.value = false
  }
}

function formatPredictionDate(dateStr) {
  const date = new Date(dateStr)
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)

  if (date.toDateString() === today.toDateString()) return 'Today'
  if (date.toDateString() === tomorrow.toDateString()) return 'Tomorrow'

  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    weekday: 'short'
  })
}

function formatTimestamp(timestamp) {
  if (!timestamp) return '-'
  const date = new Date(timestamp)
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
