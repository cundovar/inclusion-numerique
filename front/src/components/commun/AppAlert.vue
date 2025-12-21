<template>
  <div
    v-if="modelValue || show"
    :class="alertClasses"
    role="alert"
  >
    <div class="flex items-start">
      <!-- Icône -->
      <div class="flex-shrink-0">
        <i :class="iconClass"></i>
      </div>

      <!-- Contenu -->
      <div class="ml-3 flex-1">
        <!-- Titre (optionnel) -->
        <h3 v-if="title" :class="titleClass">
          {{ title }}
        </h3>

        <!-- Message -->
        <div :class="messageClass">
          <slot>{{ message }}</slot>
        </div>
      </div>

      <!-- Bouton de fermeture (optionnel) -->
      <button
        v-if="dismissible"
        @click="close"
        type="button"
        :class="closeButtonClass"
      >
        <i class="pi pi-times"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Type d'alerte
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },

  // Titre de l'alerte
  title: {
    type: String,
    default: null
  },

  // Message de l'alerte
  message: {
    type: String,
    default: null
  },

  // Peut être fermée
  dismissible: {
    type: Boolean,
    default: true
  },

  // Afficher l'alerte (v-model)
  modelValue: {
    type: Boolean,
    default: false
  },

  // Afficher l'alerte (alternative)
  show: {
    type: Boolean,
    default: false
  },

  // Variante de style
  variant: {
    type: String,
    default: 'solid',
    validator: (value) => ['solid', 'light', 'outline'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

// Fermer l'alerte
const close = () => {
  emit('update:modelValue', false)
  emit('close')
}

// Configuration des types
const typeConfig = {
  success: {
    icon: 'pi-check-circle',
    solidBg: 'bg-green-500',
    lightBg: 'bg-green-100',
    border: 'border-green-300',
    text: 'text-green-800',
    iconColor: 'text-green-600'
  },
  error: {
    icon: 'pi-times-circle',
    solidBg: 'bg-red-500',
    lightBg: 'bg-red-100',
    border: 'border-red-300',
    text: 'text-red-800',
    iconColor: 'text-red-600'
  },
  warning: {
    icon: 'pi-exclamation-triangle',
    solidBg: 'bg-yellow-500',
    lightBg: 'bg-yellow-100',
    border: 'border-yellow-300',
    text: 'text-yellow-800',
    iconColor: 'text-yellow-600'
  },
  info: {
    icon: 'pi-info-circle',
    solidBg: 'bg-blue-500',
    lightBg: 'bg-blue-100',
    border: 'border-blue-300',
    text: 'text-blue-800',
    iconColor: 'text-blue-600'
  }
}

// Classes de l'alerte
const alertClasses = computed(() => {
  const config = typeConfig[props.type]
  const base = 'rounded-lg p-4 mb-4'

  if (props.variant === 'solid') {
    return `${base} ${config.solidBg} text-white`
  } else if (props.variant === 'light') {
    return `${base} ${config.lightBg} border ${config.border}`
  } else { // outline
    return `${base} border-2 ${config.border}`
  }
})

// Classe de l'icône
const iconClass = computed(() => {
  const config = typeConfig[props.type]
  const base = `pi ${config.icon} text-xl`

  if (props.variant === 'solid') {
    return `${base} text-white`
  } else {
    return `${base} ${config.iconColor}`
  }
})

// Classe du titre
const titleClass = computed(() => {
  const config = typeConfig[props.type]
  const base = 'font-semibold mb-1'

  if (props.variant === 'solid') {
    return `${base} text-white`
  } else {
    return `${base} ${config.text}`
  }
})

// Classe du message
const messageClass = computed(() => {
  const config = typeConfig[props.type]

  if (props.variant === 'solid') {
    return 'text-white'
  } else {
    return config.text
  }
})

// Classe du bouton de fermeture
const closeButtonClass = computed(() => {
  const base = 'ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex h-8 w-8 items-center justify-center transition-colors'

  if (props.variant === 'solid') {
    return `${base} hover:bg-white/20 text-white`
  } else {
    const config = typeConfig[props.type]
    return `${base} hover:bg-gray-100 ${config.text}`
  }
})
</script>
