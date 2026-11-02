<template>
  <AppModal
    :model-value="true"
    title="Installer le site sur votre téléphone"
    size="sm"
    closable
    @close="$emit('close')"
  >
    <div class="install-popup">
      <div
        class="install-popup__icon"
        aria-hidden="true"
      >
        <i class="fas fa-mobile-screen-button" />
      </div>

      <p class="install-popup__intro">
        Ajoutez ce site sur l'écran d'accueil de votre téléphone. Il s'ouvrira comme une application.
      </p>

      <ul class="install-popup__benefits">
        <li>
          <i
            class="fas fa-hand-pointer"
            aria-hidden="true"
          />
          S'ouvre en touchant une seule icône
        </li>
        <li>
          <i
            class="fas fa-wifi"
            aria-hidden="true"
          />
          Les cours déjà lus restent disponibles avec une connexion faible
        </li>
        <li>
          <i
            class="fas fa-circle-check"
            aria-hidden="true"
          />
          Gratuit, sans publicité
        </li>
      </ul>

      <div class="install-popup__actions">
        <button
          type="button"
          class="install-popup__button install-popup__button--install"
          @click="$emit('install')"
        >
          <i
            class="fas fa-download"
            aria-hidden="true"
          />
          Installer
        </button>
        <button
          type="button"
          class="install-popup__button install-popup__button--later"
          @click="$emit('close')"
        >
          Plus tard
        </button>
      </div>

      <!-- Explications si le navigateur ne propose pas l'installation directe -->
      <p
        role="status"
        aria-live="polite"
        class="install-popup__instructions"
        :class="{ 'install-popup__instructions--shown': instructions }"
        data-testid="instructions-installation"
      >
        <template v-if="instructions">
          <i
            class="fas fa-circle-info"
            aria-hidden="true"
          />
          <span>{{ instructions }}</span>
        </template>
      </p>
    </div>
  </AppModal>
</template>

<script setup>
import AppModal from '../ui/AppModal.vue';

defineProps({
  currentOrigin: {
    type: String,
    default: ''
  },
  instructions: {
    type: String,
    default: ''
  }
});

defineEmits(['close', 'install']);
</script>

<style scoped>
/* Fonds clairs ou pastel, texte foncé (AGENTS.md) ; grands boutons faciles à toucher */
.install-popup {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  text-align: center;
  color: #1f2937;
}

.install-popup__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 4rem;
  height: 4rem;
  border-radius: 999px;
  background: #e0f2fe;
  color: #1e3a8a;
  font-size: 1.9rem;
}

.install-popup__intro {
  margin: 0;
  font-size: 1.125rem;
  line-height: 1.5;
}

.install-popup__benefits {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  width: 100%;
  margin: 0;
  padding: 0.9rem 1rem;
  border-radius: 12px;
  background: #f8fafc;
  list-style: none;
  text-align: left;
  font-size: 1rem;
  line-height: 1.4;
}

.install-popup__benefits li {
  display: flex;
  align-items: flex-start;
  gap: 0.7rem;
}

.install-popup__benefits i {
  width: 1.25rem;
  margin-top: 0.15rem;
  color: #1e3a8a;
  text-align: center;
}

.install-popup__actions {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  width: 100%;
}

.install-popup__button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  min-height: 3rem;
  padding: 0.6rem 1rem;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
}

.install-popup__button--install {
  border: 1px solid #86efac;
  background: #dcfce7;
  color: #14532d;
}

.install-popup__button--install:hover { background: #bbf7d0; }

.install-popup__button--later {
  border: 1px solid #d1d5db;
  background: #fff;
  color: #374151;
  font-weight: 600;
}

.install-popup__button--later:hover { background: #f3f4f6; }

.install-popup__button:focus-visible {
  outline: 3px solid #1d4ed8;
  outline-offset: 2px;
}

/* La zone reste dans la page (vide) pour que l'annonce soit lue par les lecteurs d'écran */
.install-popup__instructions {
  width: 100%;
  margin: 0;
}

.install-popup__instructions--shown {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  padding: 0.9rem 1rem;
  border: 1px solid #93c5fd;
  border-radius: 12px;
  background: #eff6ff;
  color: #1e3a8a;
  text-align: left;
  font-size: 1.05rem;
  line-height: 1.5;
}

.install-popup__instructions--shown i {
  margin-top: 0.2rem;
}
</style>
