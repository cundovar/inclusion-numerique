<template>
  <div class="text-2xl max-md:text-xl xl:hidden max-md:drop-shadow-xl pb-2 mt-4 pl-1">
    <router-link
      v-for="audience in AUDIENCES"
      :key="audience.key"
      :to="audience.path"
      :class="[
        audience.key === 'apprendre' ? 'bg-blue-200 hover:bg-blue-300' : 'bg-emerald-200 hover:bg-emerald-300',
        'text-lg min-h-[3rem] cursor-pointer mt-4 shadow-neutral-600 p-2 text-gray-900 font-bold hover:underline flex items-center gap-2'
      ]"
      @click="$emit('closeMenu')"
    >
      <i
        :class="audience.icon"
        aria-hidden="true"
      />
      {{ audience.title }}
    </router-link>
    <router-link
      to="/exercices"
      class="text-lg min-h-[3rem] cursor-pointer mt-4 shadow-neutral-600 bg-amber-200 p-2 hover:bg-amber-300 text-gray-900 font-bold hover:underline flex items-center gap-2"
      @click="$emit('closeMenu')"
    >
      <i
        class="fas fa-puzzle-piece"
        aria-hidden="true"
      />
      Exercices
    </router-link>
    <router-link
      to="/qcm"
      class=" text-lg h-[3rem] cursor-pointer mt-4 shadow-neutral-600 bg-purple-300 p-2 hover:bg-purple-400 text-gray-600 font-bold hover:underline flex items-center gap-2"
      @click="$emit('closeMenu')"
    >
      <i class="fas fa-brain" />
      QCM
    </router-link>
  </div>
</template>

<script setup>
import { AUDIENCES } from '@/config/audiences';

defineEmits(['closeMenu']);
</script>
