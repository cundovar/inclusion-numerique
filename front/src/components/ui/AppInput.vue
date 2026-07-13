<template>
  <div class="app-input-wrapper">
    <!-- Label (optionnel) -->
    <label v-if="label" :for="inputId" :class="labelClass">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Input texte -->
    <el-input
      v-if="type === 'text' || type === 'password' || type === 'email' || type === 'number'"
      :id="inputId"
      v-model="inputValue"
      :type="type"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :maxlength="maxlength"
      :clearable="clearable"
      :show-password="type === 'password'"
      :size="size"
      :prefix-icon="prefixIcon"
      :suffix-icon="suffixIcon"
      @input="handleInput"
      @change="handleChange"
      @blur="handleBlur"
      @focus="handleFocus"
    />

    <!-- Textarea -->
    <el-input
      v-else-if="type === 'textarea'"
      :id="inputId"
      v-model="inputValue"
      type="textarea"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :maxlength="maxlength"
      :rows="rows"
      :autosize="autosize"
      :show-word-limit="showWordLimit"
      :size="size"
      @input="handleInput"
      @change="handleChange"
      @blur="handleBlur"
      @focus="handleFocus"
    />

    <!-- Select -->
    <el-select
      v-else-if="type === 'select'"
      :id="inputId"
      v-model="inputValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :clearable="clearable"
      :multiple="multiple"
      :filterable="filterable"
      :size="size"
      @change="handleChange"
      @blur="handleBlur"
      @focus="handleFocus"
    >
      <el-option
        v-for="option in options"
        :key="option.value"
        :label="option.label"
        :value="option.value"
        :disabled="option.disabled"
      />
    </el-select>

    <!-- Message d'erreur -->
    <div v-if="error" class="text-red-500 text-sm mt-1">
      {{ error }}
    </div>

    <!-- Message d'aide -->
    <div v-if="helpText && !error" class="text-gray-500 text-sm mt-1">
      {{ helpText }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  // v-model
  modelValue: {
    type: [String, Number, Array],
    default: ''
  },

  // Type d'input
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'password', 'email', 'number', 'textarea', 'select'].includes(value)
  },

  // Label
  label: {
    type: String,
    default: null
  },

  // Placeholder
  placeholder: {
    type: String,
    default: ''
  },

  // Taille
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['large', 'default', 'small'].includes(value)
  },

  // Disabled
  disabled: {
    type: Boolean,
    default: false
  },

  // Readonly
  readonly: {
    type: Boolean,
    default: false
  },

  // Required
  required: {
    type: Boolean,
    default: false
  },

  // Clearable
  clearable: {
    type: Boolean,
    default: false
  },

  // Maxlength
  maxlength: {
    type: Number,
    default: null
  },

  // Icônes
  prefixIcon: {
    type: String,
    default: null
  },

  suffixIcon: {
    type: String,
    default: null
  },

  // Options pour select
  options: {
    type: Array,
    default: () => []
  },

  // Multiple select
  multiple: {
    type: Boolean,
    default: false
  },

  // Filterable select
  filterable: {
    type: Boolean,
    default: false
  },

  // Textarea rows
  rows: {
    type: Number,
    default: 3
  },

  // Textarea autosize
  autosize: {
    type: [Boolean, Object],
    default: false
  },

  // Show word limit
  showWordLimit: {
    type: Boolean,
    default: false
  },

  // Message d'erreur
  error: {
    type: String,
    default: null
  },

  // Message d'aide
  helpText: {
    type: String,
    default: null
  },

  // ID unique
  id: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus', 'input'])

// Génération d'un ID unique si non fourni
const inputId = computed(() => props.id || `input-${Math.random().toString(36).substr(2, 9)}`)

// Gestion du v-model
const inputValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  }
})

// Classes du label
const labelClass = computed(() => {
  return 'block mb-2 text-sm font-medium text-gray-700'
})

// Handlers
const handleInput = (value) => {
  emit('input', value)
}

const handleChange = (value) => {
  emit('change', value)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleFocus = (event) => {
  emit('focus', event)
}
</script>

<style scoped>
.app-input-wrapper {
  width: 100%;
}
</style>
