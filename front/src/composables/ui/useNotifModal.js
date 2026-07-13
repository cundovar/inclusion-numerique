import { ref, onMounted } from 'vue';

/**
 * Composable pour gérer l'affichage du modal de notification
 * avec persistance via cookies
 */
export function useNotifModal() {
  const showNotifModal = ref(false);

  /**
   * Obtenir la valeur d'un cookie
   */
  const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
      return parts.pop().split(';').shift();
    }
    return null;
  };

  /**
   * Définir un cookie avec une durée d'expiration
   */
  const setCookie = (name, value, days = 365) => {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = `expires=${date.toUTCString()}`;
    document.cookie = `${name}=${value};${expires};path=/`;
  };

  /**
   * Vérifier si le modal doit être affiché
   * Si le cookie "notifVue" n'existe pas, on affiche le modal
   */
  const checkNotifModal = () => {
    const hasSeenNotif = getCookie('notifVue');

    if (!hasSeenNotif) {
      // Délai avant d'afficher le modal (optionnel)
      setTimeout(() => {
        showNotifModal.value = true;
      }, 1000);
    }
  };

  /**
   * Fermer le modal et enregistrer dans les cookies
   */
  const closeNotifModal = () => {
    showNotifModal.value = false;
    setCookie('notifVue', 'true', 365); // Cookie valide pendant 1 an
  };

  onMounted(() => {
    checkNotifModal();
  });

  return {
    showNotifModal,
    checkNotifModal,
    closeNotifModal
  };
}
