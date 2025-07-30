<template>
      <div
    class=" xl:h-screen max-xl:hidden   xl:items-end xl:w-[16rem] xl:pt-0 xl:pr-2  xl:justify-start  max-xl:right-10 max-xl:gap-5 sm:flex-row sm:top-0 sm:m-auto  sm:absolute sm:items-start sm:pt-20 xl:gap-2  xl:fixed xl:top-20 xl:right-0 xl:z-50 xl:flex xl:flex-col max-xl:items-end "
  >

    <router-link
      v-for="tech in techs"
      :key="tech"
      :to="`/pages/${tech}`"
      class=" text-xl xl:w-full h-[3rem] max-xl:w-[14rem] max-xl:ml-20 cursor-pointer   shadow-neutral-600  bg-blue-300 p-2 hover:bg-blue-400 text-gray-600 font-bold hover:underline "
    >
      {{ tech }}
    </router-link>
  </div>
</template>

<script setup>
const techs = ['symfony', 'vuejs', 'reactjs', 'wp']

</script>


    