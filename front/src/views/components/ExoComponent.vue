<template>
  <section class="p-4">
    <div v-if="loading" class="text-center">
      <p>Chargement...</p>
    </div>

    <div v-else-if="exo" class="max-w-4xl mx-auto ">
        <div v-if="exo.exoMenu" class="  pb-10 flex align-center justify-between mb-4">
          <h1 class="text-3xl  font-bold mb-4">{{ exo.title }}</h1>
        <div v-if="exo.category" class="">
        </div>
        <span class="px-2 py-1   text-gray-700 rounded text-md font-bold ">
            <span class=" py-1">
              {{ exo.category.name }} 
            </span>
            <span >  ->  </span>
          Niveau :  <span :class="exo.category.name==='css'?'text-blue-500':'text-yellow-500 p-2'" > {{ exo.exoMenu.label }} </span>  
        </span>
      </div>
      <h3 class="font-semibold pb-2 text-xl ">Énoncé de l'exercice : </h3>

      <h3
        :class="exo.category.name==='css'?'bg-blue-50':'bg-yellow-50'" 
        class=" ml-3 font-semibold pb-2 text-xl shadow p-3 rounded-md    mb-10" v-html="exo.code">
      </h3>

      <EditorCode
        v-if="exo.exo?.slug"
        :initialCss="exo.category.name"
        :initialJs="exo.js"
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