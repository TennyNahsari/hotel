/**
 * ML/AI Predictions API Service
 */
import api from './api'

export default {
  /**
   * Train ML models (Admin only)
   * Rate limited: 2 requests per hour
   */
  async train() {
    const response = await api.post('/ml/train')
    return response.data
  },

  /**
   * Generate predictions (Admin only)
   * Rate limited: 10 requests per hour
   */
  async generatePredictions() {
    const response = await api.post('/ml/predict')
    return response.data
  },

  /**
   * Get latest predictions (All users)
   * Returns: room_demand, hall_peaks, menu_popularity
   */
  async getPredictions() {
    const response = await api.get('/ml/predictions')
    return response.data
  },

  /**
   * Get model information (All users)
   * Returns: active models with accuracy and training info
   */
  async getModelInfo() {
    const response = await api.get('/ml/info')
    return response.data
  }
}
