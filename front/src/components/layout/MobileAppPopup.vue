<template>
  <AppModal
    :model-value="true"
    title="Télécharger l'app"
    size="sm"
    closable
    @close="$emit('close')"
  >
    <!-- Section commentée pour installation PWA -->
     <div class="text-center">
      <p class="text-gray-600 mb-4">
        Téléchargez notre application mobile pour une meilleure expérience !
      </p>

      <div class="flex flex-col gap-3">
        <AppButton
          @click="$emit('install')"
          variant="primary"
          icon="pi-download"
          text-content="Installer l'application"
        />

        <a
          :href="currentOrigin"
          class="bg-green-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-green-700 transition-colors"
        >
          <i class="pi pi-external-link"></i>
          Ouvrir dans le navigateur
        </a>
      </div>
    </div> 
  </AppModal>
</template>

<script setup>
import AppModal from '../commun/modal/AppModal.vue';
import AppButton from '../commun/button/AppButton.vue';

defineProps({
  currentOrigin: {
    type: String,
    default: ''
  }
});

defineEmits(['close', 'install']);
</script>
