<template>
  <div class="niveau-filter flex gap-2">
    <!-- Bouton "Tous" -->
    <AppButton
      :variant="modelValue === null ? 'primary' : 'outline'"
      size="sm"
      @click="$emit('update:modelValue', null)"
    >
      Tous
    </AppButton>

    <!-- Boutons dynamiques pour chaque niveau -->
    <AppButton
      v-for="niveau in niveaux"
      :key="niveau.id"
      :variant="modelValue === niveau.name ? 'primary' : 'outline'"
      size="sm"
      @click="$emit('update:modelValue', niveau.name)"
    >
      {{ niveau.name }}
    </AppButton>
  </div>
</template>

<script setup>
import AppButton from '@/components/ui/AppButton.vue'

defineProps({
  // Liste des niveaux disponibles
  niveaux: {
    type: Array,
    required: true
  },
  // Niveau actuellement sélectionné (v-model)
  modelValue: {
    type: String,
    default: null
  }
})

defineEmits(['update:modelValue'])
</script>
