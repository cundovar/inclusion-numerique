<template>
  <div
    class="pb-96 md:mt-16 xl:mt-0 md:p-20 lg:p-5  shadow-2xl bg-gray-50  md:rounded-2xl"
  >
    <div v-if="pageContent">
      <div
        class="md:ml-20 p-5"
        v-html="'accueil/' + pageContent.menu.label"
      ></div>

      <div class="flex justify-between items-center p-5 max-md:p-2">
        <h1 class="text-2xl text-center flex-1">{{ pageContent.title }}</h1>
        <FavoriteButton
          v-if="pageContent.page && getPageId(pageContent.page)"
          :pageId="getPageId(pageContent.page)"
          class="max-md:absolute top-24 right-3"
        />
      </div>
      <!-- <p>{{ pageContent.content }}</p> -->
      <!-- <div class="text-center w-full p-5 bg-gray-700" v-html="pageContent.content"></div> -->
      <div v-if="pageContent.code">
        <div
          class="text-center max-md:w-full xl:w-3/4 m-auto p-5"
          v-html="pageContent.code"
        ></div>
      </div>
    </div>

    <div class="flex justify-center items-center h-full" v-else>
      <i
        class="pi pi-spin m-5 p-5 rounded-full shadow-2xl pi-cog"
        style="font-size: 3rem"
      ></i>
    </div>
 
  </div>
</template>

<script>
import Prism from "prismjs";
import axios from "axios";
import FavoriteButton from "../../components/FavoriteButton.vue";

export default {
  components: {
    FavoriteButton,
  },
  props: ["slug"],
  data() {
    return {
      pageContent: null,
    };
  },
  watch: {
    slug(newslug, oldslug) {
      if (newslug !== oldslug) {
        this.fetchPageContent();
      }
    },
  },
  mounted() {
    this.fetchPageContent();
  },
  methods: {
    async fetchPageContent() {
      try {
        const res = await axios.get(
          `/api/page_contents?page.slug=/${encodeURIComponent(this.slug)}`
        );
        if (res.data.totalItems > 0) {
          this.pageContent = res.data.member[0];
          console.log("pageContent complet:", this.pageContent);
          console.log("pageContent.page:", this.pageContent.page);
          console.log("page ID:", this.pageContent.page?.id);
          this.$nextTick(() => {
            document.querySelectorAll("pre code").forEach((el) => {
              // Nettoie la classe : enlève espaces
              el.className = el.className.trim();

              // Si pas de language-xxx, mets un langage par défaut (ex : js)
              if (!el.className.includes("language-")) {
                el.classList.add("language-js");
              }

              // Supprime indentation inutile dans les lignes
              el.innerHTML = el.innerHTML.replace(/^\s+/gm, "");
            });

            // Maintenant on applique Prism
            Prism.highlightAll();
          });
        }
      } catch (err) {
        console.error("Erreur API:", err);
      }
    },

    getPageId(page) {
      console.log("getPageId called with:", page, "type:", typeof page);

      // Si c'est un objet avec un ID classique
      if (typeof page === "object" && page.id) {
        console.log("Found object with id:", page.id);
        return page.id;
      }

      // Si c'est un objet avec @id (ApiPlatform)
      if (typeof page === "object" && page["@id"]) {
        const iri = page["@id"];
        if (iri.includes("/api/pages/")) {
          const id = iri.split("/api/pages/")[1];
          const parsedId = parseInt(id);
          console.log("Extracted ID from @id:", parsedId);
          return parsedId;
        }
      }

      // Si c'est une URL IRI d'ApiPlatform (ex: "/api/pages/1")
      if (typeof page === "string" && page.includes("/api/pages/")) {
        const id = page.split("/api/pages/")[1];
        const parsedId = parseInt(id);
        console.log("Extracted ID from IRI string:", parsedId);
        return parsedId;
      }

      // Si c'est juste un ID
      if (typeof page === "number") {
        console.log("Direct number ID:", page);
        return page;
      }

      console.log("No valid ID found");
      return null;
    },
  },
};
</script>
