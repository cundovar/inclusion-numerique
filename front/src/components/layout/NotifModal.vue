<template>
  <AppModal
    :model-value="true"
    title="Notification"
    size="md"
    closable
    close-on-backdrop
    close-on-escape
    @close="$emit('close')"
  >
    <!-- Contenu vide pour le moment -->
    <div class="  py-6">
      <h3 class="text-center">mise a jour  :</h3>
      <ul>
        <li>
          pour les user connectés :
          <ul class="ml-6 list-disc">
            <li class="ml-4">possibilité de prendre des notes dans pages cours</li>
            <li class="ml-4">possibilité de personnaliser le site</li>
          </ul>
        </li>
        <li>sommaire dynamique et déplaçable dans page des cours</li>
        <li>agrandissement des pages possible</li>
      </ul>
      <p class="text-gray-600">
      
      </p>
    </div>
  </AppModal>
</template>

<script setup>
import AppModal from '../commun/modal/AppModal.vue';

defineEmits(['close']);
</script>
