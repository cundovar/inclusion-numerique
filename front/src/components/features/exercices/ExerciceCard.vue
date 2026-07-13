<template>
  <div class="md:w-1/2 w-full">
    <h2 v-if="sectionTitle" class="text-xl text-center font-bold border-2 border-blue-600 rounded px-3 py-1 mb-2">
      {{ sectionTitle }}
    </h2>

    <div v-for="item in items" :key="item.id">
      <router-link :to="{ name: routerName, params: item.routerParams }">
        <div class="rounded-lg p-4 m-2 hover:bg-gray-50 hover:shadow transition">
          <h3 class="font-semibold mb-2">{{ items.indexOf(item) + 1 }} {{ item.title }}</h3>
        
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


</script>
