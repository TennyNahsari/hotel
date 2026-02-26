import api from './axios'

export const authApi = {
  async login(credentials) {
    // Get CSRF cookie first
    const apiUrl = import.meta.env.VITE_API_URL || 'https://hotel.tazkia.web.id'
    await api.get(`${apiUrl}/sanctum/csrf-cookie`)
    // Then login
    const response = await api.post('/login', credentials)
    return response.data
  },

  async logout() {
    const response = await api.post('/logout')
    return response.data
  },

  async getUser() {
    const response = await api.get('/user')
    return response.data
  },
}

export const roomApi = {
  async getRooms(params = {}) {
    const response = await api.get('/rooms', { params })
    return response.data
  },

  async getRoomTypes() {
    const response = await api.get('/room-types')
    return response.data
  },

  async getRoomStatistics() {
    const response = await api.get('/rooms-statistics')
    return response.data
  },

  async createRoom(data) {
    const response = await api.post('/rooms', data)
    return response.data
  },

  async updateRoom(roomId, data) {
    const response = await api.put(`/rooms/${roomId}`, data)
    return response.data
  },

  async updateRoomStatus(roomId, status) {
    const response = await api.patch(`/rooms/${roomId}/status`, { status })
    return response.data
  },

  async deleteRoom(roomId) {
    const response = await api.delete(`/rooms/${roomId}`)
    return response.data
  },

  async exportRooms(params = {}) {
    const response = await api.get('/rooms/export', { 
      params,
      responseType: 'blob' 
    })
    
    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `rooms_${new Date().toISOString().split('T')[0]}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  },
}

export const roomTypeApi = {
  async getRoomTypes(params = {}) {
    const response = await api.get('/room-types', { params })
    return response.data
  },

  async getRoomType(roomTypeId) {
    const response = await api.get(`/room-types/${roomTypeId}`)
    return response.data
  },

  async createRoomType(data) {
    const response = await api.post('/room-types', data)
    return response.data
  },

  async updateRoomType(roomTypeId, data) {
    const response = await api.put(`/room-types/${roomTypeId}`, data)
    return response.data
  },

  async deleteRoomType(roomTypeId) {
    const response = await api.delete(`/room-types/${roomTypeId}`)
    return response.data
  },
}

export const guestApi = {
  async getGuests(params = {}) {
    const response = await api.get('/guests', { params })
    return response.data
  },

  async getGuest(guestId) {
    const response = await api.get(`/guests/${guestId}`)
    return response.data
  },

  async createGuest(data) {
    const response = await api.post('/guests', data)
    return response.data
  },

  async updateGuest(guestId, data) {
    const response = await api.put(`/guests/${guestId}`, data)
    return response.data
  },

  async deleteGuest(guestId) {
    const response = await api.delete(`/guests/${guestId}`)
    return response.data
  },

  async searchGuest(query) {
    const response = await api.get(`/guests/search/${query}`)
    return response.data
  },
}

export const bookingApi = {
  async getBookings(params = {}) {
    const response = await api.get('/bookings', { params })
    return response.data
  },

  async getBooking(bookingId) {
    const response = await api.get(`/bookings/${bookingId}`)
    return response.data
  },

  async createBooking(data) {
    const response = await api.post('/bookings', data)
    return response.data
  },

  async updateBooking(bookingId, data) {
    const response = await api.put(`/bookings/${bookingId}`, data)
    return response.data
  },

  async deleteBooking(bookingId) {
    const response = await api.delete(`/bookings/${bookingId}`)
    return response.data
  },

  async confirm(bookingId) {
    const response = await api.post(`/bookings/${bookingId}/confirm`)
    return response.data
  },

  async checkIn(bookingId) {
    const response = await api.post(`/bookings/${bookingId}/check-in`)
    return response.data
  },

  async checkOut(bookingId) {
    const response = await api.post(`/bookings/${bookingId}/check-out`)
    return response.data
  },

  async cancel(bookingId) {
    const response = await api.post(`/bookings/${bookingId}/cancel`)
    return response.data
  },

  async checkAvailability(params) {
    const response = await api.get('/bookings/check-availability', { params })
    return response.data
  },
}

export const housekeepingApi = {
  async getTasks(params = {}) {
    const response = await api.get('/housekeeping', { params })
    return response.data
  },

  async getTask(taskId) {
    const response = await api.get(`/housekeeping/${taskId}`)
    return response.data
  },

  async createTask(data) {
    const response = await api.post('/housekeeping', data)
    return response.data
  },

  async updateTask(taskId, data) {
    const response = await api.put(`/housekeeping/${taskId}`, data)
    return response.data
  },

  async deleteTask(taskId) {
    const response = await api.delete(`/housekeeping/${taskId}`)
    return response.data
  },

  async updateTaskStatus(taskId, status) {
    const response = await api.patch(`/housekeeping/${taskId}/status`, { status })
    return response.data
  },

  async getStatistics() {
    const response = await api.get('/housekeeping-statistics')
    return response.data
  },
}

export const dashboardApi = {
  async getDashboard() {
    const response = await api.get('/dashboard')
    return response.data
  },
}

export const userApi = {
  async getUsers(params) {
    const response = await api.get('/users', { params })
    return response.data
  },

  async getUser(userId) {
    const response = await api.get(`/users/${userId}`)
    return response.data
  },
}

export const paymentApi = {
  async getPayments(params) {
    const response = await api.get('/payments', { params })
    return response.data
  },

  async getPayment(paymentId) {
    const response = await api.get(`/payments/${paymentId}`)
    return response.data
  },

  async createPayment(data) {
    const response = await api.post('/payments', data)
    return response.data
  },

  async updatePayment(paymentId, data) {
    const response = await api.put(`/payments/${paymentId}`, data)
    return response.data
  },

  async deletePayment(paymentId) {
    const response = await api.delete(`/payments/${paymentId}`)
    return response.data
  },

  async getBookingPayments(bookingId) {
    const response = await api.get(`/bookings/${bookingId}/payments`)
    return response.data
  },
}

export const hallApi = {
  async getHalls(params) {
    const response = await api.get('/halls', { params })
    // Handle paginated response - extract data array
    return response.data.data || response.data
  },

  async getHall(hallId) {
    const response = await api.get(`/halls/${hallId}`)
    return response.data
  },

  async getHallTypes() {
    const response = await api.get('/halls/types')
    return response.data
  },

  async createHall(data) {
    const response = await api.post('/halls', data)
    return response.data
  },

  async updateHall(hallId, data) {
    const response = await api.put(`/halls/${hallId}`, data)
    return response.data
  },

  async deleteHall(hallId) {
    const response = await api.delete(`/halls/${hallId}`)
    return response.data
  },

  async checkAvailability(hallId, data) {
    const response = await api.post(`/halls/${hallId}/availability`, data)
    return response.data
  },
}

export const hallBookingApi = {
  async getHallBookings(params) {
    const response = await api.get('/hall-bookings', { params })
    // Handle paginated response - extract data array
    return response.data.data || response.data
  },

  async getHallBooking(bookingId) {
    const response = await api.get(`/hall-bookings/${bookingId}`)
    return response.data
  },

  async createHallBooking(data) {
    const response = await api.post('/hall-bookings', data)
    return response.data
  },

  async updateHallBooking(bookingId, data) {
    const response = await api.put(`/hall-bookings/${bookingId}`, data)
    return response.data
  },

  async deleteHallBooking(bookingId) {
    const response = await api.delete(`/hall-bookings/${bookingId}`)
    return response.data
  },

  async confirmHallBooking(bookingId) {
    const response = await api.post(`/hall-bookings/${bookingId}/confirm`)
    return response.data
  },

  async cancelHallBooking(bookingId) {
    const response = await api.post(`/hall-bookings/${bookingId}/cancel`)
    return response.data
  },

  async completeHallBooking(bookingId) {
    const response = await api.post(`/hall-bookings/${bookingId}/complete`)
    return response.data
  },

  async getCalendar(params) {
    const response = await api.get('/hall-bookings/calendar', { params })
    return response.data
  },
}

export const breakfastApi = {
  async getBreakfasts(params) {
    const response = await api.get('/breakfasts', { params })
    return response.data
  },

  async getStatistics() {
    const response = await api.get('/breakfasts/statistics')
    return response.data
  },

  async updateStatus(bookingId, status) {
    const response = await api.patch(`/bookings/${bookingId}/breakfast`, {
      breakfast_status: status
    })
    return response.data
  },
}

export const menuItemApi = {
  async getMenuItems(params = {}) {
    const response = await api.get('/menu-items', { params })
    return response.data
  },

  async getMenuItem(itemId) {
    const response = await api.get(`/menu-items/${itemId}`)
    return response.data
  },

  async createMenuItem(data) {
    const formData = new FormData()
    Object.keys(data).forEach(key => {
      if (data[key] !== null && data[key] !== undefined) {
        // Convert boolean to integer for Laravel
        if (key === 'is_available') {
          formData.append(key, data[key] ? '1' : '0')
        } else {
          formData.append(key, data[key])
        }
      }
    })
    const response = await api.post('/menu-items', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data
  },

  async updateMenuItem(itemId, data) {
    const formData = new FormData()
    Object.keys(data).forEach(key => {
      if (data[key] !== null && data[key] !== undefined) {
        // Convert boolean to integer for Laravel
        if (key === 'is_available') {
          formData.append(key, data[key] ? '1' : '0')
        } else {
          formData.append(key, data[key])
        }
      }
    })
    formData.append('_method', 'PUT')
    const response = await api.post(`/menu-items/${itemId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data
  },

  async deleteMenuItem(itemId) {
    const response = await api.delete(`/menu-items/${itemId}`)
    return response.data
  },
}

export const restaurantOrderApi = {
  async getOrders(params = {}) {
    const response = await api.get('/restaurant-orders', { params })
    return response.data
  },

  async getOrder(orderId) {
    const response = await api.get(`/restaurant-orders/${orderId}`)
    return response.data
  },

  async createOrder(data) {
    const response = await api.post('/restaurant-orders', data)
    return response.data
  },

  async updateOrderStatus(orderId, status) {
    const response = await api.patch(`/restaurant-orders/${orderId}/status`, { status })
    return response.data
  },

  async deleteOrder(orderId) {
    const response = await api.delete(`/restaurant-orders/${orderId}`)
    return response.data
  },

  async getBookingCharges(bookingId) {
    const response = await api.get(`/bookings/${bookingId}/restaurant-charges`)
    return response.data
  },
}
