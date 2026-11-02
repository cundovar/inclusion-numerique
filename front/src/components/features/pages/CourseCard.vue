<template>
  <AppCard
    :title="title"
    :icon="iconClass"
    :items="menus"
    :show-footer="true"
    footer-text="cours"
    footer-icon="fas fa-book"
  >
    <template #item="{ item }">
      <router-link
        :to="`/pages${item.page.slug}`"
        class="course-link"
      >
        <div class="course-info">
          <i class="fas fa-file-lines course-item-icon" />
          <span class="course-name">{{ item.title }}</span>
        </div>
        <!-- Marches seules, toujours à la même place : la légende en haut de la page donne leur sens -->
        <NiveauBadge
          :niveau="item.niveauCours"
          :niveaux="niveaux"
          variant="icon"
          class="course-level"
        />
        <i class="fas fa-arrow-right course-arrow" />
      </router-link>
    </template>

    <template #footer>
      <div class="footer-count">
        <i
          class="fas fa-book"
          aria-hidden="true"
        />
        <span>{{ menus.length }} cours</span>
      </div>
      <PdfDownload
        v-if="moduleId"
        :href="`/module/${moduleId}.pdf`"
        label="Fiche d'atelier en PDF"
      />
    </template>
  </AppCard>
</template>

<script setup>
import AppCard from '@/components/ui/AppCard.vue'
import NiveauBadge from '@/components/ui/NiveauBadge.vue'
import PdfDownload from './PdfDownload.vue'

defineProps({
  title: {
    type: String,
    required: true
  },
  menus: {
    type: Array,
    required: true
  },
  iconClass: {
    type: String,
    default: 'fas fa-book'
  },
  // Tous les niveaux, pour le badge de chaque cours
  niveaux: {
    type: Array,
    default: () => []
  },
  // Module des cours, pour télécharger la fiche d'atelier (absent pour les cours sans module)
  moduleId: {
    type: Number,
    default: null
  }
})
</script>

<style scoped>
.course-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 15px 18px;
  text-decoration: none;
  color: inherit;
  border-radius: 12px;
  transition: background-color 160ms ease;
  min-height: 3.25rem;
}

.course-info { display: flex; align-items: center; flex: 1; }
.course-item-icon { font-size: 16px; color: #334155; margin-right: 12px; width: 20px; text-align: center; }
.footer-count { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #475569; font-weight: 500; }
.course-level { flex-shrink: 0; margin: 0 12px; }
.course-name { font-size: 16px; font-weight: 500; color: #334155; line-height: 1.4; }
.course-arrow { font-size: 14px; color: #334155; transition: transform 160ms ease; }
.course-link:hover .course-arrow { transform: translateX(4px); }
.course-link:hover, .course-link:focus-visible { background: #dbeafe; }
.course-link:focus-visible { outline: 3px solid #1d4ed8; outline-offset: 2px; }
</style>
