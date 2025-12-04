<template>

<div class="bg-amber-200 max-xl:bg-violet-300 max-xl:mt-2 max-xl:mb-2 xl:p-2">




  <div class="w-full max-xl:pt-4 min-w-[200px]">
    <AppSelect
      v-model="selectedVocabulaire"
      label="vocabulaire par langage ou technologie :"
      :options="contentSelect.vocabulaire"
      placeholder="choisir un langage"
      @change="navigateVocabulaire"
      clearable
      size="default"
    />
  </div>
   
  <div class="w-full min-w-[200px] mt-4">
    <AppSelect
      v-model="selectedFunctions"
      label="fonctions utiles par langage :"
      :options="contentSelect.functions"
      placeholder="choisir un langage"
      @change="navigateFunctions"
      clearable
      size="default"
    />
  </div>
   
</div>

</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AppSelect from '../commun/select/AppSelect.vue'

const router = useRouter()

const selectedVocabulaire = ref('')
const selectedFunctions = ref('')

const contentSelect = {
  vocabulaire: ['web','html', 'css', 'javascript', 'php','symfony','wordpress','docker','vuejs','reactjs'],
  functions: ['javascript', 'php'],
}

const emit = defineEmits(['closeMenu'])

const navigateVocabulaire = (value) => {
  if(value){
    router.push(`/pages/${value}-vocabulaire`)
    emit('closeMenu')
    selectedVocabulaire.value = '' // Reset après navigation
  }
}

const navigateFunctions = (value) => {
  if(value){
    router.push(`/pages/${value}-functions`)
    emit('closeMenu')
    selectedFunctions.value = '' // Reset après navigation
  }
}
</script>

 

    