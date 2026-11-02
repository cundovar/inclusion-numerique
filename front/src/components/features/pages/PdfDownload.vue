<template>
  <!-- Le PDF est fabriqué par le serveur : hors ligne, on le dit au lieu d'ouvrir une page d'erreur -->
  <a
    v-if="online"
    :href="href"
    class="pdf-download"
    download
  >
    <i
      class="fas fa-file-pdf"
      aria-hidden="true"
    />
    {{ label }}
  </a>
  <p
    v-else
    class="pdf-download pdf-download--offline"
    role="status"
  >
    <i
      class="fas fa-file-pdf"
      aria-hidden="true"
    />
    {{ label }} : disponible avec une connexion
  </p>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

defineProps({
  // Adresse du PDF côté serveur (/cours/… ou /module/…)
  href: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: 'Télécharger en PDF'
  }
})

const online = ref(typeof navigator === 'undefined' ? true : navigator.onLine !== false)
const update = () => { online.value = navigator.onLine !== false }

onMounted(() => {
  window.addEventListener('online', update)
  window.addEventListener('offline', update)
})
onBeforeUnmount(() => {
  window.removeEventListener('online', update)
  window.removeEventListener('offline', update)
})
</script>

<style scoped>
.pdf-download {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  min-height: 2.75rem;
  padding: 0.5rem 1rem;
  border-radius: 12px;
  border: 1px solid #93c5fd;
  background: #eff6ff;
  color: #1e3a8a;
  font-size: 1.05rem;
  font-weight: 600;
  text-decoration: none;
}

.pdf-download:hover { background: #dbeafe; }
.pdf-download:focus-visible { outline: 3px solid #1d4ed8; outline-offset: 2px; }

.pdf-download--offline {
  border-color: #d1d5db;
  background: #f3f4f6;
  color: #374151;
  font-weight: 500;
}

@media print {
  .pdf-download { display: none; }
}
</style>
