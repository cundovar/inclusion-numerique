<template>
  <div class="app-select-wrapper">
    <!-- Label (optionnel) -->
    <label v-if="label" :for="selectId" :class="labelClass">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Select simple/multiple -->
    <el-select
      v-if="!cascader"
      :id="selectId"
      v-model="selectValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :clearable="clearable"
      :multiple="multiple"
      :filterable="filterable"
      :size="size"
      :collapse-tags="collapseTags"
      :collapse-tags-tooltip="collapseTagsTooltip"
      @change="handleChange"
      @blur="handleBlur"
      @focus="handleFocus"
      class="w-full"
    >
      <el-option
        v-for="option in options"
        :key="getOptionValue(option)"
        :label="getOptionLabel(option)"
        :value="getOptionValue(option)"
        :disabled="option.disabled"
      />
    </el-select>

    <!-- Cascader (sélection en cascade) -->
    <el-cascader
      v-else
      :id="selectId"
      v-model="selectValue"
      :options="options"
      :props="cascaderProps"
      :placeholder="placeholder"
      :disabled="disabled"
      :clearable="clearable"
      :filterable="filterable"
      :size="size"
      :show-all-levels="showAllLevels"
      @change="handleChange"
      @blur="handleBlur"
      @focus="handleFocus"
      class="w-full"
    />

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
import { computed } from 'vue'

const props = defineProps({
  // v-model
  modelValue: {
    type: [String, Number, Array, Boolean],
    default: null
  },

  // Type de select
  cascader: {
    type: Boolean,
    default: false
  },

  // Label
  label: {
    type: String,
    default: null
  },

  // Placeholder
  placeholder: {
    type: String,
    default: 'Sélectionnez'
  },

  // Options
  options: {
    type: Array,
    default: () => []
  },

  // Props pour cascader
  cascaderProps: {
    type: Object,
    default: () => ({
      value: 'value',
      label: 'label',
      children: 'children'
    })
  },

  // Props pour select simple
  optionLabel: {
    type: String,
    default: 'label'
  },

  optionValue: {
    type: String,
    default: 'value'
  },

  // Taille
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['large', 'default', 'small'].includes(value)
  },

  // Multiple
  multiple: {
    type: Boolean,
    default: false
  },

  // Filterable
  filterable: {
    type: Boolean,
    default: false
  },

  // Clearable
  clearable: {
    type: Boolean,
    default: false
  },

  // Disabled
  disabled: {
    type: Boolean,
    default: false
  },

  // Required
  required: {
    type: Boolean,
    default: false
  },

  // Collapse tags (pour multiple)
  collapseTags: {
    type: Boolean,
    default: false
  },

  // Collapse tags tooltip
  collapseTagsTooltip: {
    type: Boolean,
    default: false
  },

  // Show all levels (pour cascader)
  showAllLevels: {
    type: Boolean,
    default: true
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

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus'])

// Génération d'un ID unique si non fourni
const selectId = computed(() => props.id || `select-${Math.random().toString(36).substr(2, 9)}`)

// Gestion du v-model
const selectValue = computed({
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

// Getters pour les options
const getOptionLabel = (option) => {
  if (typeof option === 'string' || typeof option === 'number') {
    return option
  }
  return option[props.optionLabel] || option.label || option.name || option
}

const getOptionValue = (option) => {
  if (typeof option === 'string' || typeof option === 'number') {
    return option
  }
  return option[props.optionValue] !== undefined
    ? option[props.optionValue]
    : (option.value !== undefined ? option.value : option.id)
}

// Handlers
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
.app-select-wrapper {
  width: 100%;
}
</style>
