import { ref, onMounted, onUnmounted } from 'vue';

export function useResponsive() {
  const isMobile = ref(window.innerWidth < 768);
  const isMdOuPlus = ref(window.innerWidth >= 768);
  const islgOuPlus = ref(window.innerWidth >= 1024);
  const isXlOuPlus = ref(window.innerWidth >= 1280);

  const updateScreenSizes = () => {
    isMobile.value = window.innerWidth < 768;
    isMdOuPlus.value = window.innerWidth >= 768;
    islgOuPlus.value = window.innerWidth >= 1024;
    isXlOuPlus.value = window.innerWidth >= 1280;
  };

  onMounted(() => {
    window.addEventListener('resize', updateScreenSizes);
    updateScreenSizes();
  });

  onUnmounted(() => {
    window.removeEventListener('resize', updateScreenSizes);
  });

  return {
    isMobile,
    isMdOuPlus,
    islgOuPlus,
    isXlOuPlus,
    updateScreenSizes
  };
}
