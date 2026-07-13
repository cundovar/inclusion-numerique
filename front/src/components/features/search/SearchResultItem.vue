<template>
  <li class="hover:bg-gray-50 p-3  w-full   border border-gray-100 rounded-lg transition-all duration-200 hover:shadow-md">
    <router-link
      :to="`/pages${result.page?.slug || result.pageSlug}`"
      class="block"
      @click="$emit('close')"
    >
      <div class="mb-2">
        <div class="font-medium text-gray-700 text-md leading-tight">
        cour :  {{ capitalize(result.title) }}
        </div>
      </div>

      <div class="flex items-center gap-2 text-xs text-gray-600 mb-2">
        <AppBadge variant="default" size="xl" rounded="sm">
         categorie : {{ result.category?.name || result.category || 'Non catégorisé' }}
        </AppBadge>
        <AppBadge v-if="result.menu?.label" " size="xl" rounded="sm">
        chapitre:  {{ result.menu.label }}
        </AppBadge>
        <AppBadge v-if="result.hasCode" size="xl" rounded="sm" icon="pi pi-code">
          Code
        </AppBadge>
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
import { capitalize } from '../../../utils/stringsUtlis';
import AppBadge from '../../ui/AppBadge.vue';

defineProps({
  result: {
    type: Object,
    required: true
  }
});

defineEmits(['close']);
</script>
