<template>
      <div
    class=" h-screen max-md:hidden fixed max-xl:p-0 xl:p-12 top-20 max-xl:top-10 right-0 z-50 flex flex-col max-xl:items-end xl:items-center"
  >

    <router-link
      v-for="tech in techs"
      :key="tech"
      :to="`/pages/${tech}`"
      class="my-2 text-lg shadow-neutral-600 shadow-xl bg-blue-400 p-2 rounded-sm hover:bg-blue-600 text-gray-600 font-bold hover:underline hover:text-blue-900"
    >
      {{ tech }}
    </router-link>
  </div>
</template>

<script setup>
const techs = ['symfony', 'vuejs', 'reactjs', 'wordpress']

</script>


    