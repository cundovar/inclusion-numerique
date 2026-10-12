<script setup>
import { withBase } from 'vitepress'

// Porte d'entrée : un thème = une question que se pose le lecteur
const themes = [
  { icon: '🚀', title: 'Démarrer', text: 'Ce qu\'est DevDoc, le lancer en local, le vocabulaire.', link: '/demarrer/' },
  { icon: '🧩', title: 'Fonctionnalités', text: 'Chaque fonction, de l\'écran à la base de données.', link: '/fonctionnalites/' },
  { icon: '🏛️', title: 'Architecture', text: 'SPA, API, hexagonal, sécurité, micro-service.', link: '/conception/architecture' },
  { icon: '🗄️', title: 'Données', text: 'MySQL, IndexedDB hors ligne, MongoDB.', link: '/donnees/' },
  { icon: '🔌', title: 'API', text: 'Toutes les routes, leurs rôles et leurs codes de retour.', link: '/api/' },
  { icon: '🤖', title: 'Intégrations', text: 'Agents de cours IA (MCP), clé d\'API.', link: '/migration-mcp-vers-symfony' },
  { icon: '✅', title: 'Qualité', text: 'Tests, make check, CI/CD et déploiement.', link: '/tests/plan-de-tests' },
  { icon: '🧭', title: 'Projet', text: 'Besoins, maquettes, gestion de projet, décisions.', link: '/decisions/' }
]
</script>

<template>
  <div class="theme-grid">
    <a
      v-for="theme in themes"
      :key="theme.title"
      class="theme-card"
      :href="withBase(theme.link)"
    >
      <span
        class="theme-card__icon"
        aria-hidden="true"
      >{{ theme.icon }}</span>
      <strong>{{ theme.title }}</strong>
      <span class="theme-card__text">{{ theme.text }}</span>
    </a>
  </div>
</template>

<style scoped>
.theme-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
  margin-top: 24px;
}
.theme-card {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 20px;
  border: 1px solid var(--vp-c-divider);
  border-radius: 12px;
  background: var(--vp-c-bg-soft);
  color: var(--vp-c-text-1);
  text-decoration: none !important;
  transition: border-color 0.2s, transform 0.2s;
}
.theme-card:hover {
  border-color: var(--vp-c-brand-1);
  transform: translateY(-2px);
}
.theme-card__icon { font-size: 26px; }
.theme-card__text { color: var(--vp-c-text-2); font-size: 14px; line-height: 1.5; }
</style>
