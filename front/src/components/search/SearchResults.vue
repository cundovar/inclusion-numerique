<template>
  <div
    v-if="searchResults.length"
    class="search-results absolute top-24 right-1/2 translate-x-1/2 m-auto p-4 max-h-[40rem] max-w-full max-md:hidden bg-white w-[28rem] rounded-xl my-4 overflow-y-auto shadow-xl z-50 border border-gray-200"
  >
    <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-200">
      <div class="text-sm font-semibold text-gray-700">
        <span class="text-blue-600">{{ searchResults.length }}</span> résultat(s) trouvé(s)
        <span v-if="searchAnalysis" class="text-xs text-gray-500 block mt-1">
          Recherche IA • {{ searchAnalysis.intent || 'Analyse en cours' }}
        </span>
      </div>
      <i
        @click="$emit('close')"
        class="text-gray-400 hover:bg-red-100 hover:text-red-600 p-2 rounded-full pi pi-times cursor-pointer text-lg transition-colors"
      ></i>
    </div>

    <!-- Tags d'analyse IA -->
    <div v-if="searchAnalysis && (searchAnalysis.keywords || searchAnalysis.technologies)" class="mb-3">
      <div class="flex flex-wrap gap-1">
        <span v-for="keyword in searchAnalysis.keywords?.slice(0, 3)"
              :key="keyword"
              class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
          {{ keyword }}
        </span>
        <span v-for="tech in searchAnalysis.technologies?.slice(0, 2)"
              :key="tech"
              class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">
          {{ tech }}
        </span>
      </div>
    </div>

    <ul class="space-y-3">
      <SearchResultItem
        v-for="result in searchResults"
        :key="result.id || result.page?.slug"
        :result="result"
        @close="$emit('close')"
      />
    </ul>
  </div>

  <div v-else-if="search && search.trim()" class="italic text-gray-500 my-4 max-md:hidden">
    Aucun résultat trouvé pour "{{ search }}".
  </div>
</template>

<script setup>
import SearchResultItem from './SearchResultItem.vue';

defineProps({
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
