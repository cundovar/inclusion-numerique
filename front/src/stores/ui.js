import { defineStore } from 'pinia'

export const useUiStore = defineStore('ui', {
  state: () => ({
    // Sidebar
    sidebarOpen: true,
    sidebarMobileOpen: false,

    // Toasts/Notifications
    toasts: [],

    // Modals
    activeModal: null,
    modalData: null,

    // Loading global
    globalLoading: false,
    loadingMessage: '',

    // Menu mobile
    mobileMenuOpen: false
  }),

  getters: {
    hasToasts: (state) => state.toasts.length > 0,
    isLoading: (state) => state.globalLoading
  },

  actions: {
    // Sidebar
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen
    },

    toggleMobileSidebar() {
      this.sidebarMobileOpen = !this.sidebarMobileOpen
    },

    closeMobileSidebar() {
      this.sidebarMobileOpen = false
    },

    // Toasts
    addToast(toast) {
      const id = Date.now()
      this.toasts.push({
        id,
        type: toast.type || 'info', // success, error, warning, info
        message: toast.message,
        duration: toast.duration || 3000
      })

      // Auto-remove
      if (toast.duration !== 0) {
        setTimeout(() => {
          this.removeToast(id)
        }, toast.duration || 3000)
      }

      return id
    },

    removeToast(id) {
      const index = this.toasts.findIndex(t => t.id === id)
      if (index > -1) {
        this.toasts.splice(index, 1)
      }
    },

    clearToasts() {
      this.toasts = []
    },

    // Shortcuts
    showSuccess(message, duration = 3000) {
      return this.addToast({ type: 'success', message, duration })
    },

    showError(message, duration = 5000) {
      return this.addToast({ type: 'error', message, duration })
    },

    showWarning(message, duration = 4000) {
      return this.addToast({ type: 'warning', message, duration })
    },

    showInfo(message, duration = 3000) {
      return this.addToast({ type: 'info', message, duration })
    },

    // Modals
    openModal(modalName, data = null) {
      this.activeModal = modalName
      this.modalData = data
    },

    closeModal() {
      this.activeModal = null
      this.modalData = null
    },

    // Loading
    setLoading(isLoading, message = '') {
      this.globalLoading = isLoading
      this.loadingMessage = message
    },

    // Menu mobile
    toggleMobileMenu() {
      this.mobileMenuOpen = !this.mobileMenuOpen
    },

    closeMobileMenu() {
      this.mobileMenuOpen = false
    }
  }
})
