<template>
    <div class="md:w-1/2 max-md:w-full">
      <h2 class="text-xl font-bold border-p border-2">intermédiaire</h2>
  
      <div
        v-for="item in items"
        :key="item.id"
      >
        <router-link
          :to="{ name: routerName, params: item.routerParams }"
        >
          <div class="rounded-lg p-4">
            <h2>{{ item.title }}</h2>
            <p class="p-2" v-html="truncateHtml(item.code, 60)"></p>
          </div>
        </router-link>
      </div>
    </div>
  </template>
  
  <script setup>
  import { toRef } from 'vue'
  
  const props = defineProps({
    // Nom de route (chaîne !)
    routerName: { type: String, required: true },
    // Tableau d’éléments déjà filtré
    items: { type: Array, default: () => [] },
  })
  
  // ⚠️ Si tu n’as PAS besoin de HTML, remplace v-html par {{ truncateText(...) }} pour éviter le XSS
  // Ici, on suppose que `code` peut contenir du HTML contrôlé.
  // Sinon, désactive v-html et échappe le contenu.
  const truncateHtml = (html, len = 60) => {
    if (!html) return ''
    const text = String(html)
    return text.length > len ? text.slice(0, len) + '…' : text
  }
  </script>
  