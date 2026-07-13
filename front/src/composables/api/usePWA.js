import { ref, onMounted } from 'vue';

export function usePWA() {
  const showMobileAppPopup = ref(false);
  const deferredPrompt = ref(null);
  const currentOrigin = ref(window.location.origin);

  const checkMobileAppPopup = (isMobile) => {
    const hasSeenPopup = localStorage.getItem('mobileAppPopupShown');

    if (!hasSeenPopup && isMobile) {
      setTimeout(() => {
        showMobileAppPopup.value = true;
      }, 1000);
    }
  };

  const closeMobileAppPopup = () => {
    showMobileAppPopup.value = false;
    localStorage.setItem('mobileAppPopupShown', 'true');
  };

  const installPWA = async () => {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);

    if (deferredPrompt.value) {
      deferredPrompt.value.prompt();

      const { outcome } = await deferredPrompt.value.userChoice;

      if (outcome === 'accepted') {
        console.log('PWA installée');
        closeMobileAppPopup();
      }

      deferredPrompt.value = null;
    } else if (isIOS) {
      alert('Pour installer sur iOS :\n1. Appuyez sur le bouton "Partager" 📤\n2. Sélectionnez "Ajouter à l\'écran d\'accueil" 📱\n3. Confirmez en appuyant sur "Ajouter"');
    } else {
      alert('Pour installer l\'application, utilisez le menu de votre navigateur : "Ajouter à l\'écran d\'accueil"');
    }
  };

  onMounted(() => {
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt.value = e;
    });
  });

  return {
    showMobileAppPopup,
    deferredPrompt,
    currentOrigin,
    checkMobileAppPopup,
    closeMobileAppPopup,
    installPWA
  };
}
