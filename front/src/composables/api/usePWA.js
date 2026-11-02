import { ref } from 'vue';

// Chrome envoie beforeinstallprompt une seule fois, parfois avant le montage de l'application :
// on l'écoute dès le chargement de ce module et on garde l'événement pour le bouton « Installer ».
const evenementInstallation = ref(null);
const estInstallee = () =>
  window.matchMedia?.('(display-mode: standalone)')?.matches === true || window.navigator.standalone === true;
const installee = ref(typeof window !== 'undefined' && estInstallee());

if (typeof window !== 'undefined') {
  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    evenementInstallation.value = event;
  });
  window.addEventListener('appinstalled', () => {
    installee.value = true;
    evenementInstallation.value = null;
  });
}

export const INSTRUCTIONS_IOS = 'Sur iPhone ou iPad : touchez le bouton Partager, puis « Sur l\'écran d\'accueil », puis « Ajouter ».';
export const INSTRUCTIONS_MENU = 'Ouvrez le menu du navigateur (les trois points en haut à droite). Touchez ensuite « Installer l\'application » ou « Ajouter à l\'écran d\'accueil ».';

export function usePWA() {
  const showMobileAppPopup = ref(false);
  const currentOrigin = ref(window.location.origin);
  // Explications affichées dans la fenêtre quand l'installation directe n'est pas possible
  const instructions = ref('');

  const checkMobileAppPopup = (isMobile) => {
    const hasSeenPopup = localStorage.getItem('mobileAppPopupShown');

    // Inutile de proposer l'installation dans l'application déjà installée
    if (!hasSeenPopup && isMobile && !installee.value) {
      setTimeout(() => {
        showMobileAppPopup.value = true;
      }, 1000);
    }
  };

  const closeMobileAppPopup = () => {
    showMobileAppPopup.value = false;
    instructions.value = '';
    localStorage.setItem('mobileAppPopupShown', 'true');
  };

  const installPWA = async () => {
    const evenement = evenementInstallation.value;

    if (evenement) {
      evenement.prompt();
      const { outcome } = await evenement.userChoice;
      // L'événement ne sert qu'une fois
      evenementInstallation.value = null;
      if (outcome === 'accepted') closeMobileAppPopup();
      return;
    }

    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    instructions.value = isIOS ? INSTRUCTIONS_IOS : INSTRUCTIONS_MENU;
  };

  return {
    showMobileAppPopup,
    deferredPrompt: evenementInstallation,
    installee,
    instructions,
    currentOrigin,
    checkMobileAppPopup,
    closeMobileAppPopup,
    installPWA
  };
}
