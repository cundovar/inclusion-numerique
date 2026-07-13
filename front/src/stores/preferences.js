import { defineStore } from 'pinia'

export const usePreferencesStore = defineStore('preferences', {
  state: () => ({
    // Theme
    darkMode: false,

    // Affichage
    fontSize: 'medium', // small, medium, large
    codeTheme: 'prism-tomorrow', // theme Prism.js

    // Navigation
    sidebarCollapsed: false,
    tocPosition: 'right', // left, right

    // Accessibilite
    reduceMotion: false,
    highContrast: false
  }),

  getters: {
    fontSizeClass: (state) => {
      const sizes = {
        small: 'text-sm',
        medium: 'text-base',
        large: 'text-lg'
      }
      return sizes[state.fontSize] || 'text-base'
    },

    themeClass: (state) => {
      return state.darkMode ? 'dark' : 'light'
    }
  },

  actions: {
    toggleDarkMode() {
      this.darkMode = !this.darkMode
      this.applyTheme()
    },

    setDarkMode(value) {
      this.darkMode = value
      this.applyTheme()
    },

    setFontSize(size) {
      if (['small', 'medium', 'large'].includes(size)) {
        this.fontSize = size
      }
    },

    setCodeTheme(theme) {
      this.codeTheme = theme
    },

    toggleSidebarCollapsed() {
      this.sidebarCollapsed = !this.sidebarCollapsed
    },

    setTocPosition(position) {
      if (['left', 'right'].includes(position)) {
        this.tocPosition = position
      }
    },

    toggleReduceMotion() {
      this.reduceMotion = !this.reduceMotion
      this.applyMotionPreference()
    },

    toggleHighContrast() {
      this.highContrast = !this.highContrast
      this.applyContrastPreference()
    },

    // Appliquer le theme au DOM
    applyTheme() {
      if (this.darkMode) {
        document.documentElement.classList.add('dark')
      } else {
        document.documentElement.classList.remove('dark')
      }
    },

    applyMotionPreference() {
      if (this.reduceMotion) {
        document.documentElement.classList.add('reduce-motion')
      } else {
        document.documentElement.classList.remove('reduce-motion')
      }
    },

    applyContrastPreference() {
      if (this.highContrast) {
        document.documentElement.classList.add('high-contrast')
      } else {
        document.documentElement.classList.remove('high-contrast')
      }
    },

    // Initialiser depuis les preferences systeme
    initFromSystemPreferences() {
      // Dark mode systeme
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        this.darkMode = true
      }

      // Reduce motion systeme
      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        this.reduceMotion = true
      }

      this.applyTheme()
      this.applyMotionPreference()
    }
  },

  // Persistance dans localStorage
  persist: {
    storage: localStorage,
    paths: ['darkMode', 'fontSize', 'codeTheme', 'sidebarCollapsed', 'tocPosition', 'reduceMotion', 'highContrast']
  }
})
