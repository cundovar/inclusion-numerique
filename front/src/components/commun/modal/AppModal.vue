<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="modelValue"
        :class="backdropClasses"
        @click="handleBackdropClick"
      >
        <Transition :name="transitionName">
          <div
            v-if="modelValue"
            :class="modalClasses"
            @click.stop
            role="dialog"
            aria-modal="true"
            :aria-labelledby="title ? 'modal-title' : undefined"
          >
            <!-- Header -->
            <div v-if="showHeader" class="modal-header">
              <slot name="header">
                <h3 v-if="title" id="modal-title" class="modal-title">
                  <i v-if="icon" :class="[icon, 'modal-icon']"></i>
                  {{ title }}
                </h3>
              </slot>

              <button
                v-if="closable"
                type="button"
                class="modal-close"
                @click="close"
                aria-label="Fermer"
              >
                <i class="pi pi-times"></i>
              </button>
            </div>

            <!-- Body -->
            <div :class="bodyClasses">
              <slot></slot>
            </div>

            <!-- Footer -->
            <div v-if="showFooter || $slots.footer" class="modal-footer">
              <slot name="footer">
                <AppButton
                  v-if="showCancelButton"
                  variant="secondary"
                  :text-content="cancelText"
                  @click="close"
                />
                <AppButton
                  v-if="showConfirmButton"
                  :variant="confirmVariant"
                  :text-content="confirmText"
                  :loading="loading"
                  @click="handleConfirm"
                />
              </slot>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch, onMounted, onBeforeUnmount } from 'vue'
import AppButton from '../button/AppButton.vue'

const props = defineProps({
  // v-model
  modelValue: {
    type: Boolean,
    default: false
  },

  // Contenu
  title: {
    type: String,
    default: ''
  },

  icon: {
    type: String,
    default: null
  },

  // Taille
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },

  // Position
  position: {
    type: String,
    default: 'center',
    validator: (value) => ['center', 'top', 'bottom'].includes(value)
  },

  // Comportement
  closable: {
    type: Boolean,
    default: true
  },

  closeOnBackdrop: {
    type: Boolean,
    default: true
  },

  closeOnEscape: {
    type: Boolean,
    default: true
  },

  persistent: {
    type: Boolean,
    default: false
  },

  // Sections
  showHeader: {
    type: Boolean,
    default: true
  },

  showFooter: {
    type: Boolean,
    default: false
  },

  // Padding du body
  bodyPadding: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  },

  // Backdrop
  backdropBlur: {
    type: Boolean,
    default: true
  },

  // Boutons footer par défaut
  showCancelButton: {
    type: Boolean,
    default: false
  },

  showConfirmButton: {
    type: Boolean,
    default: false
  },

  cancelText: {
    type: String,
    default: 'Annuler'
  },

  confirmText: {
    type: String,
    default: 'Confirmer'
  },

  confirmVariant: {
    type: String,
    default: 'primary'
  },

  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'open', 'confirm', 'cancel'])

// Classes du backdrop
const backdropClasses = computed(() => {
  const classes = ['modal-backdrop']

  if (props.backdropBlur) {
    classes.push('modal-backdrop-blur')
  }

  return classes
})

// Classes de la modal
const modalClasses = computed(() => {
  const classes = ['modal-container']

  // Taille
  classes.push(`modal-${props.size}`)

  // Position
  classes.push(`modal-${props.position}`)

  return classes
})

// Classes du body
const bodyClasses = computed(() => {
  const classes = ['modal-body']
  classes.push(`modal-body-padding-${props.bodyPadding}`)
  return classes
})

// Nom de la transition selon la position
const transitionName = computed(() => {
  switch (props.position) {
    case 'top':
      return 'modal-slide-down'
    case 'bottom':
      return 'modal-slide-up'
    default:
      return 'modal-scale'
  }
})

// Fermer la modal
const close = () => {
  if (!props.persistent) {
    emit('update:modelValue', false)
    emit('close')
    emit('cancel')
  }
}

// Clic sur le backdrop
const handleBackdropClick = () => {
  if (props.closeOnBackdrop && !props.persistent) {
    close()
  }
}

// Confirmer
const handleConfirm = () => {
  emit('confirm')
}

// Gestion de la touche Escape
const handleEscape = (event) => {
  if (event.key === 'Escape' && props.modelValue && props.closeOnEscape && !props.persistent) {
    close()
  }
}

// Bloquer le scroll du body quand la modal est ouverte
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden'
    emit('open')
  } else {
    document.body.style.overflow = ''
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* Backdrop */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  z-index: 1000;
  overflow-y: auto;
}

.modal-backdrop-blur {
  backdrop-filter: blur(4px);
}

/* Container */
.modal-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 32px);
  width: 100%;
  position: relative;
}

/* Tailles */
.modal-xs {
  max-width: 320px;
}

.modal-sm {
  max-width: 400px;
}

.modal-md {
  max-width: 500px;
}

.modal-lg {
  max-width: 700px;
}

.modal-xl {
  max-width: 900px;
}

.modal-full {
  max-width: calc(100vw - 32px);
  max-height: calc(100vh - 32px);
}

/* Positions */
.modal-center {
  margin: auto;
}

.modal-top {
  margin-top: 0;
  margin-bottom: auto;
  border-radius: 0 0 12px 12px;
}

.modal-bottom {
  margin-top: auto;
  margin-bottom: 0;
  border-radius: 12px 12px 0 0;
}

/* Header */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-icon {
  font-size: 24px;
  color: #3b82f6;
}

.modal-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  background: none;
  color: #6b7280;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
  font-size: 18px;
}

.modal-close:hover {
  background-color: #f3f4f6;
  color: #ef4444;
}

/* Body */
.modal-body {
  flex: 1;
  overflow-y: auto;
  color: #374151;
  line-height: 1.6;
}

.modal-body-padding-none {
  padding: 0;
}

.modal-body-padding-sm {
  padding: 12px 16px;
}

.modal-body-padding-md {
  padding: 20px 24px;
}

.modal-body-padding-lg {
  padding: 28px 32px;
}

.modal-body-padding-xl {
  padding: 36px 40px;
}

/* Footer */
.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  flex-shrink: 0;
}

/* Responsive */
@media screen and (max-width: 768px) {
  .modal-backdrop {
    padding: 0;
  }

  .modal-container {
    max-height: 100vh;
    border-radius: 0;
  }

  .modal-xs,
  .modal-sm,
  .modal-md,
  .modal-lg,
  .modal-xl {
    max-width: 100vw;
  }

  .modal-full {
    max-width: 100vw;
    max-height: 100vh;
  }

  .modal-top,
  .modal-bottom {
    border-radius: 0;
  }

  .modal-header {
    padding: 16px 20px;
  }

  .modal-title {
    font-size: 18px;
  }

  .modal-body-padding-md {
    padding: 16px 20px;
  }

  .modal-footer {
    padding: 12px 20px;
  }
}

/* Scrollbar personnalisée */
.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Animations - Backdrop */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* Animations - Modal Scale (center) */
.modal-scale-enter-active,
.modal-scale-leave-active {
  transition: all 0.3s ease;
}

.modal-scale-enter-from,
.modal-scale-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Animations - Slide Down (top) */
.modal-slide-down-enter-active,
.modal-slide-down-leave-active {
  transition: all 0.3s ease;
}

.modal-slide-down-enter-from,
.modal-slide-down-leave-to {
  opacity: 0;
  transform: translateY(-100%);
}

/* Animations - Slide Up (bottom) */
.modal-slide-up-enter-active,
.modal-slide-up-leave-active {
  transition: all 0.3s ease;
}

.modal-slide-up-enter-from,
.modal-slide-up-leave-to {
  opacity: 0;
  transform: translateY(100%);
}
</style>
