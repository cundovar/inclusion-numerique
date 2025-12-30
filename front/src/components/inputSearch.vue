<template>
  <div class="flex gap-2 md:w-1/3 m-auto items-center justify-start flex-row">
    <AppInput
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      @keyup.enter="$emit('search')"
      type="text"
      :placeholder="placeholder"
      prefix-icon="Search"
      clearable
      size="default"
    />

    <!-- Bouton desktop -->
    <AppButton
      @click="$emit('search')"
      variant="primary"
      size="sm"
      text-content="Rechercher"
      class="max-xl:hidden xl:flex"
    />

    <!-- Bouton mobile (icône ronde) -->
    <AppButton
      @click="$emit('search')"
      variant="warning"
      size="md"
      icon="pi pi-search"
      rounded="full"
      class="xl:hidden"
    />
  </div>
</template>
  
<script setup>
import { ref } from 'vue';
import { onMounted } from 'vue';
import axios from 'axios';
import AppButton from './commun/button/AppButton.vue';
import AppInput from './commun/input/AppInput.vue';

defineProps({
  modelValue: String,
  placeholder: {
    type: String,
    default: 'Rechercher'
  }
});
// const fetchUser = async () => {
//   try {
//     const response = await axios.get('/user-api/me');
//     user.value = response.data;
//     console.log('User:', user.value);
//   } catch (error) {
//     console.error('Erreur lors de la récupération de l\'utilisateur', error);
//   }
// };

// onMounted(() => {
//   fetchUser();
// });

</script>
