<template>
  <div class="flex gap-2 md:w-1/2 lg:w-1/3 m-auto items-center justify-start flex-row">
    <AppInput
      :model-value="modelValue"
      type="text"
      :placeholder="placeholder"
      prefix-icon="Search"
      clearable
      size="default"
      @update:model-value="emit('update:modelValue', $event)"
      @keyup.enter="emit('search')"
    />

    <!-- Bouton desktop -->
    <AppButton
      variant="primary"
      size="sm"
      text-content="Rechercher"
      class="max-xl:hidden xl:flex"
      @click="emit('search')"
    />

    <!-- Bouton mobile (icône ronde) -->
    <AppButton
      variant="warning"
      size="md"
      icon="pi pi-search"
      rounded="full"
      class="xl:hidden"
      @click="emit('search')"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { onMounted } from 'vue';
import axios from 'axios';
import AppButton from '../../ui/AppButton.vue';
import AppInput from '../../ui/AppInput.vue';

defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Rechercher'
  }
});
const emit = defineEmits(['update:modelValue', 'search']);
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
