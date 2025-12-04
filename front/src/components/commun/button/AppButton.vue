<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[buttonClasses, bgHover]"
    :style="hoverBgColor ? `--hover-bg: ${hoverBgColor}` : ''"
    @click="$emit('click', $event)"
  >
    <!-- Icône de chargement -->
    <i v-if="loading" class="pi pi-spinner pi-spin mr-2"></i>

    <!-- Icône avant le texte -->
    <i v-else-if="icon && iconPosition === 'left'" :class="[icon, textContent ? 'mr-2' : '']"></i>

    <!-- Slot pour le contenu personnalisé ou texte par défaut -->
    <slot>{{ textContent }}</slot>

    <!-- Icône après le texte -->
    <i v-if="icon && iconPosition === 'right' && !loading" :class="[icon, textContent ? 'ml-2' : '']"></i>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Type de bouton HTML
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },

  // Variante de style
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'warning', 'ghost', 'outline', 'profile', 'auth', 'favorite', 'unfavorite','neutral'].includes(value)
  },

  // Taille du bouton
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },

  // Classes de hover dynamiques (ex: hover:bg-blue-500)
  bgHover: {
    type: String,
    default: ''
  },

  // Couleur hex pour le hover (ex: #fef08a)
  hoverBgColor: {
    type: String,
    default: ''
  },

  // Icône (classe PrimeIcons)
  icon: {
    type: String,
    default: null
  },

  // Position de l'icône
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },

  // Texte du bouton
  textContent: {
    type: String,
    default: null
  },

  // État désactivé
  disabled: {
    type: Boolean,
    default: false
  },

  // État de chargement
  loading: {
    type: Boolean,
    default: false
  },

  // Pleine largeur
  fullWidth: {
    type: Boolean,
    default: false
  },

  // Arrondi
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
  }
})

defineEmits(['click'])

// Classes de base
const baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2'

// Classes de variantes
const variantClasses = {
  primary: 'bg-blue-400 hover:bg-blue-600 text-white focus:ring-blue-500 disabled:bg-blue-300',
  secondary: 'bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-500 disabled:bg-gray-300',
  danger: 'bg-red-400 hover:bg-red-600 text-white focus:ring-red-500 disabled:bg-red-300',
  success: 'bg-green-200 hover:bg-green-600 text-white focus:ring-green-500 disabled:bg-green-300',
  warning: 'bg-yellow-300 hover:bg-yellow-600 text-white focus:ring-yellow-500 disabled:bg-yellow-300',
  ghost: 'bg-transparent hover:bg-gray-100 text-gray-700 focus:ring-gray-300 disabled:text-gray-400',
  outline: 'bg-transparent border-2 border-blue-500 text-blue-500 hover:bg-blue-50 focus:ring-blue-500 disabled:border-blue-300 disabled:text-blue-300',
  profile: 'bg-amber-200 hover:bg-amber-300 text-gray-800 focus:ring-amber-500 disabled:bg-amber-100 shadow-lg hover:shadow-xl',
  auth: 'bg-pink-400 hover:bg-pink-500 text-white focus:ring-pink-500 disabled:bg-pink-300 shadow-lg hover:shadow-xl',
  favorite: 'bg-red-400 hover:bg-red-600 text-white focus:ring-red-500 disabled:bg-red-300',
  unfavorite: 'bg-gray-200 hover:bg-gray-300 text-gray-700 focus:ring-gray-500 disabled:bg-gray-100',
  neutral:' text-gray-700 focus:ring-gray-500 disabled:bg-gray-100'
}

// Classes de tailles
const sizeClasses = {
  xs: 'px-2 py-1 text-xs',
  sm: 'px-3 py-1.5 text-sm',
  md: 'px-4 py-2 text-base',
  lg: 'px-6 py-3 text-lg',
  xl: 'px-8 py-4 text-xl'
}

// Classes d'arrondi
const roundedClasses = {
  none: 'rounded-none',
  sm: 'rounded-sm',
  md: 'rounded-lg',
  lg: 'rounded-xl',
  full: 'rounded-full'
}

// Computed pour les classes du bouton
const buttonClasses = computed(() => {
  return [
    baseClasses,
    variantClasses[props.variant],
    sizeClasses[props.size],
    roundedClasses[props.rounded],
    {
      'w-full': props.fullWidth,
      'opacity-50 cursor-not-allowed': props.disabled || props.loading,
      'cursor-pointer': !props.disabled && !props.loading
    }
  ]
})
</script>

<style scoped>
button:hover {
  background-color: var(--hover-bg) !important;
}
</style>
