<template>
  <div class="h-auto  absolute top-3 max-xl:w-2/3 right-0 ">

      <div
        class="flex flex-wrap p-0 pr-48 gap-3  w-full max-md:hidden justify-end items-end text-amber-200 "
      >
        <div class="h-full flex flex-col gap-1 items-center justify-center">
          <a href="https://github.com/poleS-dev" target="_blank">
            <i
              class="pi pi-github m-auto cursor-pointer"
              style="font-size: 2.3rem"
            ></i>
          </a>
          <p class="text-center text-amber-50 font-bold">
            {{ capitalize("github") }}
          </p>
        </div>
        <div
          class="flex flex-col gap-1 items-center justify-center"
          v-for="logo in logos"
          :key="logo.alt"
        >
          <a
            class="w-10 h-10 rounded-full overflow-hidden"
            :href="logo.src"
            target="_blank"
          >
            <img class="h-full" :src="logo.img" :alt="logo.alt" />
          </a>
          <p class="text-center text-amber-50 font-bold">
            {{ capitalize(logo.name) }}
          </p>
        </div>
      </div>

  </div>
</template>

<script setup>
import { capitalize } from "../../utlis/stringsUtlis";
import { logos } from "../../tab-object/list-logo";

//     @/assets/... → @ = alias pour ton src/
// ✅ import.meta.url → fonctionne avec Vite / ESM
</script>

<style scoped>
img {
  object-fit: cover;
}

a {
  overflow: hidden;
  display: inline-block;
}
</style>
