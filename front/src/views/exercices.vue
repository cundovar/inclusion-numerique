<template>
    <div class=" page-exo-list md:rounded-2xl mt-10 xl:mt-0 pb-96 min-h-screen w-full relative z-20">
      <div class="w-full">
        <div class="tech-header">
          <h1 class="tech-title  ">Exercices</h1>
        </div>
        <p class="tech-description"></p>
  
        <div class="w-full">
      
  
          <div class="flex flex-wrap gap-10 justify-center items-start">
            <!-- ===== CSS ===== -->
            <section class="section-exo p-10 w-full">
              <div class="summary-header" @click="toggleSection('css')">
                <h2 class="titre-exo text-2xl font-bold mb-4 flex items-center gap-3">
                  <i class="fab fa-css3-alt text-blue-600"></i>
                  CSS
                  <i class="ml-auto transition-transform" :class="openSections.css ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"></i>
                </h2>
              </div>
                
              <Transition name="slide-fade">
                <article v-show="openSections.css" class="article-exo flex max-md:flex-wrap gap-10 justify-center p-4">
                  <Exocard
                    routerName="exercices-id"
                    :items="getItems('css','base')"
                    sectionTitle="Base"
                  />
                  <Exocard
                    routerName="exercices-id"
                    :items="getItems('css','intermédiaire')"
                    sectionTitle="Intermédiaire"
                  />
                  <Exocard
                    routerName="exercices-id"
                    :items="getItems('css','avancé')"
                    sectionTitle="Avancé"
                  />
                </article>
              </Transition>
            </section>
                
            <!-- ===== JavaScript ===== -->
            <section class="section-exo p-10 w-full">
              <div class="summary-header" @click="toggleSection('javascript')">
                <h2 class="titre-exo text-2xl font-bold mb-4 flex items-center gap-3">
                  <i class="fab fa-js-square text-yellow-500"></i>
                  JavaScript
                  <i class="ml-auto transition-transform" :class="openSections.javascript ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"></i>
                </h2>
              </div>

              <Transition name="slide-fade">
                <article v-show="openSections.javascript" class="article-exo flex max-md:flex-wrap gap-10 justify-center p-4">
                  <Exocard
                    routerName="exercices-id"
                    :items="getItems('javascript','base')"
                    sectionTitle="Base"
                  />
                  <Exocard
                    routerName="exercices-id"
                    :items="getItems('javascript','intermédiaire')"
                    sectionTitle="Intermédiaire"
                  />
                  <Exocard
                    routerName="exercices-id"
                    :items="getItems('javascript','avancé')"
                    sectionTitle="Avancé"
                  />
                </article>
              </Transition>
            </section>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import Exocard from "../components/features/exercices/ExerciceCard.vue"
  import { useData } from "../utils/fetchDataPwa"
  import { onMounted, ref, reactive } from "vue"

  const { exoMenus, fetchExoMenus } = useData()
  const { exoContents, fetchExoContents } = useData()

  // État d'ouverture des sections
  const openSections = reactive({
    css: false,
    javascript: false
  })

  // Toggle une section
  const toggleSection = (section) => {
    openSections[section] = !openSections[section]
  }

  onMounted(async () => {
    await fetchExoMenus()
    await fetchExoContents()
  })
  
  /**
   * Normalise un exo -> item pour Exocard
   */
  const toItem = (exo) => ({
    id: exo.id,
    title: exo.title || exo.name || "Sans titre",
    code: exo.code || exo.content || "",
    routerParams: { slug: exo.exo?.slug || "" },
  })
  
  /**
   * Renvoie les items pour une catégorie et un niveau donné
   * cat: "css" | "javascript"
   * lvl: "base" | "intermédiaire" | "avancé"
   */
  const getItems = (cat, lvl) =>
    (exoContents.value || [])
      .filter(e => e?.category?.name?.toLowerCase() === cat.toLowerCase())
      .filter(e => e?.exoMenu?.label?.toLowerCase() === lvl.toLowerCase())
      .map(toItem)
  </script>
  

  <style scoped>

  .summary-header {
    cursor: pointer;
    user-select: none;
  }

  .titre-exo {
    text-align: center;
    display: block;
  }

  @media (min-width: 1024px) {
    .article-exo {
      padding-left: 5rem;
      padding-right: 5rem;
    }
  }

  /* Transitions Vue */
  .slide-fade-enter-active {
    transition: all 0.4s ease-out;
  }

  .slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
  }

  .slide-fade-enter-from {
    transform: translateY(-20px);
    opacity: 0;
  }

  .slide-fade-leave-to {
    transform: translateY(-20px);
    opacity: 0;
  }
</style>