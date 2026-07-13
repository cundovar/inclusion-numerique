<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-6">Agents Cours</h1>
      <div class="flex flex-wrap gap-2 mb-6">
        <button
          class="px-4 py-2 rounded border text-sm font-semibold"
          :class="tab === 'creer' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300'"
          @click="tab = 'creer'"
        >
          Créer un cours
        </button>
        <button
          class="px-4 py-2 rounded border text-sm font-semibold"
          :class="tab === 'reviser' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300'"
          @click="tab = 'reviser'"
        >
          Réviser un cours
        </button>
      </div>

      <AgentsCoursForm v-if="tab === 'creer'" />
      <AgentsCoursRevisionForm v-else />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AgentsCoursForm from '@/components/features/agents/AgentsCoursForm.vue'
import AgentsCoursRevisionForm from '@/components/features/agents/AgentsCoursRevisionForm.vue'

const tab = ref('creer')
</script>
