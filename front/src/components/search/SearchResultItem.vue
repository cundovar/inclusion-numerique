<template>
  <li class="hover:bg-gray-50 p-3 border border-gray-100 rounded-lg transition-all duration-200 hover:shadow-md">
    <router-link
      :to="`/pages${result.page?.slug || result.pageSlug}`"
      class="block"
      @click="$emit('close')"
    >
      <div class="mb-2">
        <div class="font-medium text-gray-800 text-sm leading-tight">
          {{ capitalize(result.title) }}
        </div>
      </div>

      <div class="flex items-center gap-2 text-xs text-gray-600 mb-2">
        <span class="bg-gray-100 px-2 py-1 rounded">
          {{ result.category?.name || result.category || 'Non catégorisé' }}
        </span>
        <span v-if="result.menu?.label" class="bg-blue-100 text-blue-700 px-2 py-1 rounded">
          {{ result.menu.label }}
        </span>
        <span v-if="result.hasCode" class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded flex items-center">
          <i class="pi pi-code mr-1"></i>Code
        </span>
      </div>

      <!-- Summary avec highlight -->
      <div v-if="result.displaySummary || result.summary"
           class="text-xs text-gray-600 leading-relaxed bg-gray-50 p-2 rounded border-l-2 border-blue-200">
        {{ result.displaySummary || result.summary }}
      </div>

      <!-- Date si disponible -->
      <div v-if="result.formattedDate" class="text-xs text-gray-400 mt-2 flex items-center">
        <i class="pi pi-calendar mr-1"></i>
        {{ result.formattedDate }}
      </div>
    </router-link>
  </li>
</template>

<script setup>
import { capitalize } from '../../utlis/stringsUtlis';

defineProps({
  result: {
    type: Object,
    required: true
  }
});

defineEmits(['close']);
</script>
