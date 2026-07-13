import { ref } from 'vue';

export function useNavigation() {
  const hoveredCategory = ref(null);
  const isMenuOpen = ref(false);
  const clickMenu = ref(null);
  const isOpenMenuGauche = ref(false);
  const modalIsOpen = ref(false);

  const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
  };

  const closeMobileMenu = () => {
    isMenuOpen.value = false;
  };

  const openMenu = (catName) => {
    if (clickMenu.value === catName) {
      clickMenu.value = null;
    } else {
      clickMenu.value = catName;
    }
  };

  const toggleMenuGauche = () => {
    isOpenMenuGauche.value = !isOpenMenuGauche.value;
  };

  const closeModal = () => {
    modalIsOpen.value = false;
  };

  const openModal = () => {
    modalIsOpen.value = true;
  };

  const closed = () => {
    closeModal();
    closeMobileMenu();
  };

  return {
    hoveredCategory,
    isMenuOpen,
    clickMenu,
    isOpenMenuGauche,
    modalIsOpen,
    toggleMenu,
    closeMobileMenu,
    openMenu,
    toggleMenuGauche,
    closeModal,
    openModal,
    closed
  };
}
