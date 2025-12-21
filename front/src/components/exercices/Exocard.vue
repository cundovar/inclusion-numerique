<template>
  <div class="md:w-1/2 w-full">
    <h2 v-if="sectionTitle" class="text-xl text-center font-bold border-2 border-blue-600 rounded px-3 py-1 mb-2">
      {{ sectionTitle }}
    </h2>

    <div v-for="item in items" :key="item.id">
      <router-link :to="{ name: routerName, params: item.routerParams }">
        <div class="rounded-lg p-4 m-2 hover:bg-gray-50 hover:shadow transition">
          <h3 class="font-semibold mb-2">{{ items.indexOf(item) + 1 }} {{ item.title }}</h3>
          <!-- <p class="p-2" v-html="truncateHtml(item.code, 60)"></p>-->
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  routerName: { type: String, required: true },
  items: { type: Array, default: () => [] },
  sectionTitle: { type: String, default: '' },
})

// ⚠️ Si le HTML n'est pas sûr, éviter v-html ou le sanitiser.
const truncateHtml = (html, len = 60) => {
  if (!html) return ''
  const str = String(html)
  return str.length > len ? str.slice(0, len) + '…' : str
}
</script>
