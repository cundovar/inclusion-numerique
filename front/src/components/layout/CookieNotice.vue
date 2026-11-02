<template>
  <section
    v-if="visible"
    class="cookie-notice"
    aria-label="Information sur les cookies"
  >
    <!-- Simple information, sans choix à faire : le site ne dépose que des cookies nécessaires, exemptés de
         consentement (CNIL, article 82 de la loi Informatique et Libertés). Rien n'est bloqué en attendant. -->
    <p class="cookie-notice__text">
      <i
        class="fas fa-cookie-bite"
        aria-hidden="true"
      />
      <span>
        Ce site n'utilise que des cookies nécessaires à son fonctionnement.
        Aucune publicité, aucune mesure d'audience, aucun suivi.
      </span>
    </p>
    <div class="cookie-notice__actions">
      <a
        href="/privacy#cookies"
        class="cookie-notice__link"
      >En savoir plus</a>
      <button
        type="button"
        class="cookie-notice__button"
        @click="dismiss"
      >
        J'ai compris
      </button>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

// Ce repère est lui-même un stockage nécessaire : il évite de reposer la même information
const COOKIE_NOTICE_KEY = 'mediateur:cookieNotice:seen'

function alreadySeen() {
  try {
    return localStorage.getItem(COOKIE_NOTICE_KEY) === '1'
  } catch {
    return false
  }
}

const visible = ref(!alreadySeen())

function dismiss() {
  visible.value = false
  try {
    localStorage.setItem(COOKIE_NOTICE_KEY, '1')
  } catch {
    // Navigation privée ou stockage bloqué : le bandeau reviendra à la prochaine visite
  }
}
</script>

<style scoped>
/* Au-dessus de la barre de navigation mobile (h-20, md:h-24), sans la cacher ; fonds pastel (AGENTS.md) */
.cookie-notice {
  position: fixed;
  left: 0.75rem;
  right: 0.75rem;
  bottom: 5.75rem;
  z-index: 1001;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem 1.25rem;
  max-width: 52rem;
  margin: 0 auto;
  padding: 0.9rem 1.1rem;
  border: 1px solid #93c5fd;
  border-radius: 16px;
  background: #eff6ff;
  color: #1f2937;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
}

@media (min-width: 768px) {
  .cookie-notice { bottom: 6.75rem; }
}

@media (min-width: 1280px) {
  .cookie-notice { bottom: 1.25rem; }
}

.cookie-notice__text {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  flex: 1 1 22rem;
  margin: 0;
  font-size: 1rem;
  line-height: 1.5;
}

.cookie-notice__text i {
  margin-top: 0.2rem;
  color: #1e3a8a;
}

.cookie-notice__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.cookie-notice__link {
  color: #1e3a8a;
  font-weight: 600;
  text-decoration: underline;
}

.cookie-notice__button {
  min-height: 2.75rem;
  padding: 0.5rem 1.1rem;
  border: 1px solid #86efac;
  border-radius: 12px;
  background: #dcfce7;
  color: #14532d;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
}

.cookie-notice__button:hover { background: #bbf7d0; }

.cookie-notice__link:focus-visible,
.cookie-notice__button:focus-visible {
  outline: 3px solid #1d4ed8;
  outline-offset: 2px;
}

@media print {
  .cookie-notice { display: none; }
}
</style>
