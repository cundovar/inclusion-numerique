<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 mx-4 max-w-sm w-full shadow-xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Télécharger l'app</h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 text-xl"
        >
          ×
        </button>
      </div>

      <!-- Section commentée pour installation PWA -->
      <!-- <div class="text-center">
        <p class="text-gray-600 mb-4">
          Téléchargez notre application mobile pour une meilleure expérience !
        </p>

        <div class="flex flex-col gap-3">
          <button
            @click="$emit('install')"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
          >
            <i class="pi pi-download"></i>
            Installer l'application
          </button>

          <a
            :href="currentOrigin"
            class="bg-green-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-green-700 transition-colors"
          >
            <i class="pi pi-external-link"></i>
            Ouvrir dans le navigateur
          </a>
        </div>
      </div> -->
    </div>
  </div>
</template>

<script setup>
defineProps({
  currentOrigin: {
    type: String,
    default: ''
  }
});

defineEmits(['close', 'install']);
</script>
