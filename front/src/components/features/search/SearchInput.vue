<template>
  <form
    class="flex gap-2 md:w-1/2 lg:w-1/3 m-auto items-center justify-start flex-row"
    @submit.prevent="emit('search')"
  >
    <AppInput
      :model-value="modelValue"
      type="text"
      :placeholder="placeholder"
      prefix-icon="Search"
      clearable
      size="default"
      @update:model-value="emit('update:modelValue', $event)"
      @keydown.enter.prevent="emit('search')"
    />

    <!-- Bouton desktop -->
    <AppButton
      variant="primary"
      size="sm"
      text-content="Rechercher"
      type="submit"
      class="max-xl:hidden xl:flex"
    />

    <!-- Bouton mobile (icône ronde) -->
    <AppButton
      variant="warning"
      size="md"
      icon="pi pi-search"
      rounded="full"
      type="submit"
      class="xl:hidden"
    />
  </form>
</template>

<script setup>
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
