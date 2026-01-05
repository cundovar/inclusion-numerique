<template>
  <div
    ref="headerRef"
    :class="[
      header.bgColor || 'bg-[var(--primary-color)]',
      'border-blue-950 z-50 py-2 fixed w-full shadow-xl top-0 left-0 xl:hidden'
    ]"
  >
    <!-- Ligne du haut: Menu burger, Logo, Bouton auth -->
    <div class="flex justify-between items-center px-3 py-1">
      <!-- Bouton menu burger -->
      <div @click="$emit('toggleMenu')" class="cursor-pointer">
        <i
          v-if="!isMenuOpen"
          :class="[header.textColor || 'text-blue-950', 'pi pi-bars cursor-pointer']"
          style="font-size: 1.5rem"
        ></i>
        <i
          v-else
          :class="[header.textColor || 'text-blue-950', 'pi pi-times cursor-pointer']"
          style="font-size: 1.5rem"
        ></i>
      </div>

      <!-- Logo centré -->
      <router-link to="/">
        <div class="flex items-center gap-2">
          <i
            :class="[header.textColor || 'text-blue-950', 'pi pi-code']"
            style="font-size: 1.8rem"
          ></i>
          <span
            :class="[header.textColor || 'text-blue-950', 'font-bold text-xl']"
          >
            {{ siteName }}
          </span>
        </div>
      </router-link>

    <!-- Bouton backoffice + connexion/déconnexion -->
    <div class="flex items-center gap-2">
      <a
        v-if="isAdmin"
        :href="APP_CONFIG.ADMIN_URL"
        target="_blank"
        rel="noopener noreferrer"
      >
        <AppButton
          variant="outline"
          size="xs"
          icon="pi pi-cog"
          rounded="full"
        />
      </a>
      <AuthButtonMobile />
    </div>
  </div>

    <!-- Ligne du bas: Barre de recherche -->
    <div class="px-3 pt-1 pb-2">
      <SearchInput
        :modelValue="search"
        @update:modelValue="$emit('update:search', $event)"
        @search="$emit('search')"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Headroom from 'headroom.js';
import AuthButtonMobile from '../features/auth/AuthButtonMobile.vue';
import SearchInput from '../features/search/SearchInput.vue';
import { useCustomization } from '../../composables/ui/useCustomization';
import AppButton from '../ui/AppButton.vue';
import { useData } from '../../utils/fetchDataPwa';
import { APP_CONFIG } from '../../config/app.js';

const { siteName, header } = useCustomization();
const { user } = useData();
const isAdmin = computed(() => user.value?.roles?.includes('ROLE_ADMIN'));

defineProps({
  isMenuOpen: {
    type: Boolean,
    default: false
  },
  search: {
    type: String,
    default: ''
  }
});

defineEmits(['toggleMenu', 'update:search', 'search']);

// Headroom.js
const headerRef = ref(null);
let headroom = null;

onMounted(() => {
  if (headerRef.value) {
    headroom = new Headroom(headerRef.value, {
      offset: 100,          // Ne pas activer avant 100px de scroll
      tolerance: {
        up: 10,             // Sensibilité scroll vers le haut
        down: 10            // Sensibilité scroll vers le bas
      },
      classes: {
        initial: 'headroom',
        pinned: 'headroom--pinned',
        unpinned: 'headroom--unpinned',
        top: 'headroom--top',
        notTop: 'headroom--not-top'
      },
      onPin: () => {},
      onUnpin: () => {},
    });
    headroom.init();
  }
});

onUnmounted(() => {
  if (headroom) {
    headroom.destroy();
  }
});
</script>

<style>
/* Headroom.js animations */
.headroom {
  will-change: transform;
  transition: transform 0.3s ease-in-out !important;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

.headroom--pinned {
  transform: translateY(0%) !important;
}

.headroom--unpinned {
  transform: translateY(-100%) !important;
}

.headroom--top {
  transform: translateY(0%) !important;
}

.headroom--not-top.headroom--pinned {
  transform: translateY(0%) !important;
}

.headroom--not-top.headroom--unpinned {
  transform: translateY(-100%) !important;
}
</style>
