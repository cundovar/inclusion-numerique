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
        
          <ul class="ml-6 list-disc">
            <li class="ml-4">menus pour mobile</li>
            <li class="ml-4">gestions de category differente</li>
          </ul>
        </li>
        <li>sommaire dynamique </li>
        <li>agrandissement des pages possible pour exo</li>
        <li>securité du html provenant de l' api</li>
      </ul>
      <p class="text-gray-600">
      
      </p>
    </div>
  </AppModal>
</template>

<script setup>
import AppModal from '../ui/AppModal.vue';

defineEmits(['close']);
</script>
