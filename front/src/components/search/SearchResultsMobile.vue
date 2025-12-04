<template>
  <!-- Modal avec résultats -->
  <AppModal
    v-if="searchResults.length"
    :model-value="modalIsOpen"
    size="md"
    body-padding="none"
    :closable="false"
    class="md:hidden"
    @close="$emit('close')"
  >
    <template #header>
      <div class="flex justify-between items-center w-full bg-blue-50 p-4 -m-5 mb-0 border-b">
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
    </template>

    <!-- Tags d'analyse IA mobile -->
    <div v-if="searchAnalysis && (searchAnalysis.keywords || searchAnalysis.technologies)" class="p-3 border-b bg-gray-50">
      <div class="flex flex-wrap gap-1">
        <AppBadge
          v-for="keyword in searchAnalysis.keywords?.slice(0, 2)"
          :key="keyword"
          variant="primary"
          size="xs"
          rounded="full"
          outline
        >
          {{ keyword }}
        </AppBadge>
        <AppBadge
          v-for="tech in searchAnalysis.technologies?.slice(0, 1)"
          :key="tech"
          variant="success"
          size="xs"
          rounded="full"
          outline
        >
          {{ tech }}
        </AppBadge>
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
            <AppBadge variant="default" size="xs" rounded="sm">
              {{ result.category?.name || result.category || 'Non catégorisé' }}
            </AppBadge>
            <AppBadge v-if="result.hasCode" variant="warning" size="xs" rounded="sm">
              Code
            </AppBadge>
          </div>

          <!-- Summary mobile -->
          <div v-if="result.displaySummary || result.summary"
               class="text-xs text-gray-600 leading-relaxed bg-blue-50 p-2 rounded border-l-2 border-blue-300">
            {{ (result.displaySummary || result.summary).substring(0, 120) }}{{ (result.displaySummary || result.summary).length > 120 ? '...' : '' }}
          </div>
        </router-link>
      </li>
    </ul>
  </AppModal>

  <!-- Modal sans résultats -->
  <AppModal
    v-else-if="modalIsOpen && search && search.trim()"
    :model-value="modalIsOpen"
    title="Recherche"
    size="sm"
    closable
    class="md:hidden"
    @close="$emit('close')"
  >
    <div class="italic text-gray-500 text-center">
      Aucun résultat trouvé pour "{{ search }}".
    </div>
  </AppModal>
</template>

<script setup>
import { capitalize } from '../../utlis/stringsUtlis';
import AppBadge from '../commun/badge/AppBadge.vue';
import AppModal from '../commun/modal/AppModal.vue';

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
