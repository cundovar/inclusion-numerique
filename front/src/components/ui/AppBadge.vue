<template>
  <span :class="badgeClasses" @click="handleClick">
    <!-- Dot indicator -->
    <span v-if="dot" :class="dotClasses"></span>

    <!-- Icon -->
    <i v-if="icon" :class="[icon, 'badge-icon']"></i>

    <!-- Slot pour icône personnalisée -->
    <slot name="icon"></slot>

    <!-- Contenu du badge -->
    <span class="badge-content">
      <slot></slot>
    </span>

    <!-- Bouton remove -->
    <button
      v-if="removable"
      type="button"
      class="badge-remove"
      @click.stop="handleRemove"
      aria-label="Remove"
    >
      <i class="fas fa-times"></i>
    </button>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Variante de style
  variant: {
    type: String,
    default: 'default',
    validator: (value) => [
      'default', 'primary', 'secondary', 'success',
      'warning', 'danger', 'info', 'dark'
    ].includes(value)
  },

  // Taille
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },

  // Icône
  icon: {
    type: String,
    default: null
  },

  // Afficher un point indicateur
  dot: {
    type: Boolean,
    default: false
  },

  // Badge supprimable
  removable: {
    type: Boolean,
    default: false
  },

  // Arrondi
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
  },

  // Badge cliquable
  clickable: {
    type: Boolean,
    default: false
  },

  // Outline variant
  outline: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click', 'remove'])

// Classes du badge
const badgeClasses = computed(() => {
  const classes = ['app-badge']

  // Variante
  if (props.outline) {
    classes.push(`badge-outline-${props.variant}`)
  } else {
    classes.push(`badge-${props.variant}`)
  }

  // Taille
  classes.push(`badge-${props.size}`)

  // Arrondi
  classes.push(`badge-rounded-${props.rounded}`)

  // Cliquable
  if (props.clickable || props.removable) {
    classes.push('badge-clickable')
  }

  return classes
})

// Classes du dot
const dotClasses = computed(() => {
  const classes = ['badge-dot']
  classes.push(`badge-dot-${props.variant}`)
  return classes
})

const handleClick = (event) => {
  if (props.clickable) {
    emit('click', event)
  }
}

const handleRemove = (event) => {
  emit('remove', event)
}
</script>

<style scoped>
.app-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-weight: 500;
  line-height: 1;
  white-space: nowrap;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

/* Sizes */
.badge-xs {
  padding: 2px 6px;
  font-size: 10px;
}

.badge-sm {
  padding: 3px 8px;
  font-size: 12px;
}

.badge-md {
  padding: 4px 10px;
  font-size: 13px;
}

.badge-lg {
  padding: 6px 14px;
  font-size: 14px;
}

.badge-xl {
  padding: 8px 18px;
  font-size: 16px;
}

/* Rounded */
.badge-rounded-none {
  border-radius: 0;
}

.badge-rounded-sm {
  border-radius: 2px;
}

.badge-rounded-md {
  border-radius: 4px;
}

.badge-rounded-lg {
  border-radius: 8px;
}

.badge-rounded-full {
  border-radius: 9999px;
}

/* Variants - Solid */
.badge-default {
  background-color: #e5e7eb;
  color: #374151;
}

.badge-primary {
  background-color: #3b82f6;
  color: white;
}

.badge-secondary {
  background-color: #6b7280;
  color: white;
}

.badge-success {
  background-color: #10b981;
  color: white;
}

.badge-warning {
  background-color: #f59e0b;
  color: white;
}

.badge-danger {
  background-color: #ef4444;
  color: white;
}

.badge-info {
  background-color: #06b6d4;
  color: white;
}

.badge-dark {
  background-color: #1f2937;
  color: white;
}

/* Variants - Outline */
.badge-outline-default {
  background-color: transparent;
  color: #374151;
  border-color: #d1d5db;
}

.badge-outline-primary {
  background-color: transparent;
  color: #3b82f6;
  border-color: #3b82f6;
}

.badge-outline-secondary {
  background-color: transparent;
  color: #6b7280;
  border-color: #6b7280;
}

.badge-outline-success {
  background-color: transparent;
  color: #10b981;
  border-color: #10b981;
}

.badge-outline-warning {
  background-color: transparent;
  color: #f59e0b;
  border-color: #f59e0b;
}

.badge-outline-danger {
  background-color: transparent;
  color: #ef4444;
  border-color: #ef4444;
}

.badge-outline-info {
  background-color: transparent;
  color: #06b6d4;
  border-color: #06b6d4;
}

.badge-outline-dark {
  background-color: transparent;
  color: #1f2937;
  border-color: #1f2937;
}

/* Dot indicator */
.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.badge-dot-default {
  background-color: #6b7280;
}

.badge-dot-primary {
  background-color: #3b82f6;
}

.badge-dot-secondary {
  background-color: #6b7280;
}

.badge-dot-success {
  background-color: #10b981;
}

.badge-dot-warning {
  background-color: #f59e0b;
}

.badge-dot-danger {
  background-color: #ef4444;
}

.badge-dot-info {
  background-color: #06b6d4;
}

.badge-dot-dark {
  background-color: #1f2937;
}

/* Icon */
.badge-icon {
  font-size: 0.875em;
  flex-shrink: 0;
}

/* Content */
.badge-content {
  line-height: 1.2;
}

/* Remove button */
.badge-remove {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  padding: 0;
  margin-left: 2px;
  background: none;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  opacity: 0.6;
  transition: all 0.2s ease;
  font-size: 10px;
}

.badge-remove:hover {
  opacity: 1;
  background-color: rgba(0, 0, 0, 0.1);
}

.badge-remove i {
  font-size: 10px;
}

/* Clickable */
.badge-clickable {
  cursor: pointer;
}

.badge-clickable:hover {
  opacity: 0.85;
  transform: translateY(-1px);
}

/* Responsive adjustments */
@media screen and (max-width: 768px) {
  .badge-lg {
    padding: 5px 12px;
    font-size: 13px;
  }

  .badge-xl {
    padding: 6px 14px;
    font-size: 14px;
  }
}
</style>
