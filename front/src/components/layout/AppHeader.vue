<template>
  <div
    :class="[
      header.bgColor || 'backdrop-blur-md bg-white/30',
      'hidden xl:flex xl:w-full xl:fixed xl:top-0 xl:right-0 xl:z-50 xl:justify-between xl:items-center xl:px-8 py-4'
    ]"
  >
    <!-- Logo DevDoc -->
    <router-link to="/">
      <div
        :class="[
          header.hoverColor,
          'flex gap-5 items-center rounded-lg px-4 py-2'
        ]"
      >
        <i
          :class="[header.textColor || 'text-blue-950', 'pi pi-code']"
          style="font-size: 1.8rem"
        ></i>
        <span
          :class="[header.textColor || 'text-gray-800', 'font-bold text-xl']"
        >
          {{ siteName }}
        </span>
        <i
          :class="[header.textColor || 'text-blue-950', 'pi pi-code']"
          style="font-size: 1.8rem"
        ></i>
      </div>
    </router-link>

    <!-- Recherche au centre -->
    <SearchInput
      :modelValue="search"
      @update:modelValue="$emit('update:search', $event)"
      @search="$emit('search')"
      placeholder="Rechercher"
      class="w-96"
    />

    <!-- Boutons profil et déconnexion à droite -->
    <div class="flex items-center gap-2">
      <a
        v-if="isAdmin"
        :href="APP_CONFIG.ADMIN_URL"
        target="_blank"
        rel="noopener noreferrer"
      >
        <AppButton
          variant="outline"
          size="sm"
          icon="pi pi-cog"
          text-content="Backoffice"
        />
      </a>
      <AuthButton />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SearchInput from '../features/search/SearchInput.vue';
import AuthButton from '../features/auth/AuthButton.vue';
import { useCustomization } from '../../composables/ui/useCustomization';
import AppButton from '../ui/AppButton.vue';
import { useData } from '../../utils/fetchDataPwa';
import { APP_CONFIG } from '../../config/app.js';

const { siteName, header } = useCustomization();
const { user } = useData();
const isAdmin = computed(() => user.value?.roles?.includes('ROLE_ADMIN'));

defineProps({
  search: {
    type: String,
    default: ''
  }
});

defineEmits(['update:search', 'search']);
</script>
