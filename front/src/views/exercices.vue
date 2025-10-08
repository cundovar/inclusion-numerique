<template>
  <div
    class="md:rounded-2xl md:mt-10 xl:mt-0 pb-96 text-blue-500 min-h-screen w-full bg-cyan-100"
  >
    <div class="w-full">
      <div class="tech-header">
        <h1 class="tech-title">Exercices</h1>
      </div>
      <p class="tech-description"></p>

      <div class="w-full">
        <p>exercices text</p>

        <div class="flex flex-wrap gap-10 justify-center items-center">
          <details class="border">
            <summary>javascript</summary>

            <h2 class="text-xl font-bold border-p border-2">base</h2>
            <div
              v-for="filter in filterMenuContentsJSBase('base')"
              :key="filter.id"
            >
              <router-link
                :to="{
                  name: 'exercices-id',
                  params: { slug: filter.exo?.slug || '' },
                }"
              >
                <Exocard :titre="filter.title" :text="filter.code" />
              </router-link>
            </div>

            <h2 class="text-xl font-bold border-p border-2">intermédiaire</h2>
            <div
              v-for="filter in filterMenuContentsJSBase('intermédiaire')"
              :key="filter.id"
            >
              <router-link
                :to="{
                  name: 'exercices-id',
                  params: { slug: filter.exo?.slug || '' },
                }"
              >
                <Exocard :titre="filter.title" />
              </router-link>
            </div>

            <h2 class="text-xl font-bold border-p border-2">Avancé</h2>
            <div
              v-for="filter in filterMenuContentsJSBase('avancé')"
              :key="filter.id"
            >
              <router-link
                :to="{
                  name: 'exercices-id',
                  params: { slug: filter.exo?.slug || '' },
                }"
              >
                <Exocard :titre="filter.title" />
              </router-link>
            </div>
          </details>

          <div class="border p-10 w-full">
            <h1>css</h1>

            <article class="flex border max-md:flex-wrap gap-10 justify-center">
              <Exocard
                :titre="filter.title"
                :text="filter.code"
                :routerName="exercices-id"
                :routerParams="{ slug: filter.exo?.slug || '' }"
                :filterFunction="filterMenuContentsCSSBase('base')"
                :keyBoucle="filter.id"
                :filter="filter"
              />

              <Exocard
                :titre="filter.title"
                :text="filter.code"
                :routerName="exercices-id"
                :routerParams="{ slug: filter.exo?.slug || '' }"
                :filterFunction="filterMenuContentsCSSBase('intermédiaire')"
                :keyBoucle="filter.id"
                :filter="filter"

              />

              <Exocard
                :titre="filter.title"
                :text="filter.code"
                :routerName="exercices-id"
                :routerParams="{ slug: filter.exo?.slug || '' }"
                :filterFunction="filterMenuContentsCSSBase('avancé')"
                :keyBoucle="filter.id"
                :filter="filter"
              />
            </article>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Exocard from "./components/exercices/Exocard.vue";
import { useData } from "../utlis/fetchDataPwa";
import { onMounted, computed } from "vue";
const { exoMenus, fetchExoMenus } = useData();
const { exoContents, fetchExoContents } = useData();

onMounted(async () => {
  await fetchExoMenus();
  await fetchExoContents();

  console.log("exoMenus", exoMenus.value);
  console.log("exoContents", exoContents.value);
});

const filterMenuContentsJS = computed(() => {
  return exoContents.value.filter((exo) => exo.category.name === "javascript");
});

const filterMenuContentsJSBase = (niveau) => {
  return filterMenuContentsJS.value.filter(
    (exo) => exo.exoMenu.label === niveau
  );
};

const filterMenuContentsCSS = computed(() => {
  return exoContents.value.filter((exo) => exo.category.name === "css");
});
const filterMenuContentsCSSBase = (niveau) => {
  return filterMenuContentsCSS.value.filter(
    (exo) => exo.exoMenu.label === niveau
  );
};
console.log("filterMenuContentsJS", filterMenuContentsJS.value);
console.log("filterMenuContentsCSS", filterMenuContentsCSS.value);
console.log("filterMenuContentsJSBase", filterMenuContentsJSBase("base"));
</script>
