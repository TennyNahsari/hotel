import { defineStore } from 'pinia'
import api from '../api/axios'

export const useBranchStore = defineStore('branch', {
  state: () => ({
    branches: [],
    currentBranch: null,
    loading: false,
    error: null,
  }),

  getters: {
    activeBranchId: (state) => {
      if (state.currentBranch) return state.currentBranch.id
      const savedId = localStorage.getItem('active_branch_id')
      return savedId ? parseInt(savedId) : 1
    },
    activeBranchName: (state) => {
      return state.currentBranch?.name || 'Grand Hotel Merdeka Jakarta'
    },
  },

  actions: {
    async fetchPublicBranches() {
      this.loading = true
      try {
        const response = await api.get('/public/branches')
        const rawData = response.data?.data || response.data
        this.branches = Array.isArray(rawData) ? rawData : []
        
        // Auto select branch from localStorage or first branch
        const savedId = localStorage.getItem('active_branch_id')
        if (savedId && this.branches.length > 0) {
          const found = this.branches.find(b => b.id === parseInt(savedId))
          if (found) {
            this.currentBranch = found
          } else {
            this.selectBranch(this.branches[0])
          }
        } else if (this.branches.length > 0) {
          this.selectBranch(this.branches[0])
        }
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    },

    async fetchAdminBranches() {
      this.loading = true
      try {
        const response = await api.get('/branches')
        const rawData = response.data?.data || response.data
        const list = Array.isArray(rawData) ? rawData : []

        if (list.length > 0) {
          this.branches = list
        } else {
          // Fallback to public branches if admin query returned empty
          await this.fetchPublicBranches()
          return
        }

        const savedId = localStorage.getItem('active_branch_id')
        if (savedId && this.branches.length > 0) {
          const found = this.branches.find(b => b.id === parseInt(savedId))
          if (found) {
            this.currentBranch = found
          } else {
            this.selectBranch(this.branches[0])
          }
        } else if (this.branches.length > 0) {
          this.selectBranch(this.branches[0])
        }
      } catch (err) {
        this.error = err.message
        // Fallback to public branches if admin endpoint failed
        await this.fetchPublicBranches()
      } finally {
        this.loading = false
      }
    },

    selectBranch(branch) {
      if (!branch) return
      this.currentBranch = branch
      localStorage.setItem('active_branch_id', branch.id)
      localStorage.setItem('active_branch_slug', branch.slug)
    },

    selectBranchById(id) {
      const branch = this.branches.find(b => b.id === parseInt(id))
      if (branch) {
        this.selectBranch(branch)
      }
    },
  },
})
