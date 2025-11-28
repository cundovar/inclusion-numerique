<template>
  <div
    v-if="modalIsOpen && searchResults.length"
    class="fixed inset-0 overflow-hidden backdrop-blur-2xl bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 md:hidden"
  >
    <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-hidden shadow-xl">
      <div class="flex justify-between items-center p-4 border-b bg-blue-50">
        <div>
          <h2 class="text-lg font-bold text-gray-800">Résultats IA</h2>
          <div class="text-sm text-gray-600">
            <span class="text-blue-600 font-medium">{{ searchResults.length }}</span> résultat(s)
          </div>
        </div>
        <i
          @click="$emit('close')"
          class="text-gray-400 hover:text-red-600 pi pi-times cursor-pointer text-xl p-2 hover:bg-red-100 rounded-full transition-colors"
        ></i>
      </div>

      <!-- Tags d'analyse IA mobile -->
      <div v-if="searchAnalysis && (searchAnalysis.keywords || searchAnalysis.technologies)" class="p-3 border-b bg-gray-50">
        <div class="flex flex-wrap gap-1">
          <span v-for="keyword in searchAnalysis.keywords?.slice(0, 2)"
                :key="keyword"
                class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
            {{ keyword }}
          </span>
          <span v-for="tech in searchAnalysis.technologies?.slice(0, 1)"
                :key="tech"
                class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">
            {{ tech }}
          </span>
        </div>
        <div v-if="searchAnalysis.intent" class="text-xs text-gray-600 mt-1">
          {{ searchAnalysis.intent }}
        </div>
      </div>

      <ul class="overflow-y-auto max-h-[60vh] p-2">
        <li
          v-for="result in searchResults"
          :key="result.id || result.page?.slug"
          class="hover:bg-gray-50 p-3 border-b border-gray-100 transition-colors duration-200"
        >
          <router-link
            :to="`/pages${result.page?.slug || result.pageSlug}`"
            class="block"
            @click="$emit('close')"
          >
            <div class="mb-2">
              <div class="font-medium text-gray-800 text-sm">
                {{ capitalize(result.title) }}
              </div>
            </div>

            <div class="flex items-center gap-1 text-xs text-gray-600 mb-2 flex-wrap">
              <span class="bg-gray-100 px-2 py-1 rounded">
                {{ result.category?.name || result.category || 'Non catégorisé' }}
              </span>
              <span v-if="result.hasCode" class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded">
                Code
              </span>
            </div>

            <!-- Summary mobile -->
            <div v-if="result.displaySummary || result.summary"
                 class="text-xs text-gray-600 leading-relaxed bg-blue-50 p-2 rounded border-l-2 border-blue-300">
              {{ (result.displaySummary || result.summary).substring(0, 120) }}{{ (result.displaySummary || result.summary).length > 120 ? '...' : '' }}
            </div>
          </router-link>
        </li>
      </ul>
    </div>
  </div>

  <div v-else-if="modalIsOpen && search && search.trim()" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 md:hidden">
    <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-xl">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold">Recherche</h2>
        <i
          @click="$emit('close')"
          class="text-blue-950 pi pi-times cursor-pointer text-xl"
        ></i>
      </div>
      <div class="italic text-gray-500 text-center">
        Aucun résultat trouvé pour "{{ search }}".
      </div>
    </div>
  </div>
</template>

<script setup>
import { capitalize } from '../../utlis/stringsUtlis';

defineProps({
  modalIsOpen: {
    type: Boolean,
    default: false
  },
  searchResults: {
    type: Array,
    default: () => []
  },
  searchAnalysis: {
    type: Object,
    default: null
  },
  search: {
    type: String,
    default: ''
  }
});

defineEmits(['close']);
</script>
