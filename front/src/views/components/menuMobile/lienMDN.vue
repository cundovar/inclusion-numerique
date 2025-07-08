<template>
  <button
    @click="openLienMDN"
    class="md:hidden bg-gray-500 focus:bg-gray-700 w-full flex justify-end items-center"
  >
    <p class="font-bold text-amber-200 max-md:text-xl p-2">documentations</p>
  </button>

  <div
    v-if="isLienMDNOpen"
    class="border bg-gray-200 z-50"
    v-for="logo in logos"
    :key="logo.alt"
  >
  <button class="w-full p-2 focus:bg-gray-600">
    <a
      class="w-10  h-10 rounded-full overflow-hidden"
      :href="logo.src"
      target="_blank"
    >
      <p class="text-center text-xl shadow-[0_0_10px_0_rgba(0,0,0,0.5)] font-bold">
        {{ capitalize(logo.name) }}
      </p>
    </a>
  </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { logos } from "../../../tab-object/list-logo";
import { capitalize } from "../../../utlis/stringsUtlis";


const isLienMDNOpen = ref(false);

console.log("logos", logos);

const openLienMDN = () => {
  isLienMDNOpen.value = !isLienMDNOpen.value;
}
</script>
