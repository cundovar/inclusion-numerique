<template>
  <footer class="app-footer">
    <!-- Pages légales servies par Symfony (hors de la SPA) : liens simples, pas router-link -->
    <nav
      aria-label="Informations légales"
      class="app-footer__links"
    >
      <a href="/mentions-legales">Mentions légales</a>
      <span aria-hidden="true">·</span>
      <a href="/privacy">Confidentialité et cookies</a>
      <span aria-hidden="true">·</span>
      <a href="/terms">Conditions d'utilisation</a>
    </nav>
    <p class="app-footer__site">
      {{ siteName }} — site gratuit, sans publicité
    </p>
  </footer>
</template>

<script setup>
import { useCustomization } from '@/composables/ui/useCustomization'

const { siteName } = useCustomization()
</script>

<style scoped>
/* Sous le contenu de chaque page ; sur mobile, marge basse pour ne pas passer sous la barre de navigation fixe */
.app-footer {
  position: relative;
  z-index: 10;
  margin-top: 2rem;
  padding: 1.25rem 1rem 7rem;
  border-top: 1px solid #e2e8f0;
  text-align: center;
  color: #374151;
}

@media (min-width: 768px) {
  .app-footer { padding-bottom: 8rem; }
}

@media (min-width: 1280px) {
  .app-footer { padding-bottom: 1.5rem; }
}

.app-footer__links {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.25rem 0.75rem;
  font-size: 0.95rem;
}

.app-footer__links a {
  display: inline-block;
  padding: 0.35rem 0.1rem;
  color: #1e3a8a;
  text-decoration: underline;
}

.app-footer__links a:focus-visible {
  outline: 3px solid #1d4ed8;
  outline-offset: 2px;
}

.app-footer__site {
  margin: 0.5rem 0 0;
  font-size: 0.9rem;
  color: #4b5563;
}

@media print {
  .app-footer { display: none; }
}
</style>
