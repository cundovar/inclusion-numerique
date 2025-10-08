<template>
  <section class="p-4">
    <div v-if="loading" class="text-center">
      <p>Chargement...</p>
    </div>

    <div v-else-if="exo" class="max-w-4xl mx-auto">
      <h1 class="text-3xl font-bold mb-4">{{ exo.title }}</h1>

      


      
      <div v-if="exo.exoMenu" class="  flex align-center justify-end mb-4">
        <div v-if="exo.category" class="mb-2">
        <div class="px-3 py-1 bg-blue-500 text-white rounded">
          {{ exo.category.name }}
        </div>
      </div>
        <div class="px-2 py-1  text-gray-700 rounded text-md font-bold align-center">
          Niveau : {{ exo.exoMenu.label }}
        </div>
      </div>


      <div v-if="exo.content" class="prose mb-6 border " v-html="exo.content"></div>
      <h3 class=" font-semibold p-2 text-xl border rounded-2xl border-blue-600 mb-10" v-html="exo.code"></h3>
      <EditorCode
        v-if="exo.exo?.slug"
        
      
        :initialCss="'/* Ton CSS ici */'"
        :initialJs="'// Ton JavaScript ici'"
      />

    </div>

    <div v-else class="text-center text-red-600">
      <p>Exercice non trouvé</p>
    </div>
  </section>
</template>

<script setup>
import { useData } from '../../utlis/fetchDataPwa'
import { useRoute } from 'vue-router'
import { onMounted, ref, computed } from 'vue'
import EditorCode from '../components/exercices/EditorCode.vue'

const route = useRoute()
const { exoContents, fetchExoContents } = useData()

const slug = computed(() => decodeURIComponent(String(route.params.slug ?? '')))
console.log("slug décodé:", slug.value)

const exo = ref(null)
const loading = ref(true)

onMounted(async () => {
  if (!slug.value) {
    loading.value = false
    return
  }

  try {
    // Charger tous les exercices si pas déjà fait
    if (exoContents.value.length === 0) {
      await fetchExoContents()
    }
    console.log('exoContents:', exoContents.value)

    // Trouver l'exercice par slug (le slug en base peut avoir ou non le /)
    const foundExo = exoContents.value.find(e => {
      const exoSlug = e.exo?.slug || ''
      return exoSlug === slug.value || exoSlug === '/' + slug.value
    })

    if (foundExo) {
      exo.value = foundExo
      console.log("Exercice trouvé:", exo.value)
    } else {
      console.error("Exercice non trouvé pour slug:", slug.value)
    }
  } catch (error) {
    console.error("Erreur lors du chargement de l'exercice:", error)
  } finally {
    loading.value = false
  }
})
</script>