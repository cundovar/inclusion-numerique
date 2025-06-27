<template>
  <main class=" pb-96 ">
    <div v-if="pageContent">
      <h1 class="text-2xl w-full text-center p-5 border mb-5">{{ pageContent.title }}</h1>
      <div class="text-center w-full p-5 bg-gray-600" v-html="pageContent.menu.label"></div>
      <!-- <p>{{ pageContent.content }}</p> -->
      <div class="text-center w-full p-5 bg-gray-700" v-html="pageContent.content"></div>
      <div v-if="pageContent.code">
        <div v-html="pageContent.code"></div>
      </div>
    </div>
    <div v-else>
        <p>spinner</p>
    </div>
  </main>
    </template>
  
  <script>
  import axios from 'axios';
  export default {
  props: ['slug'],
  data() {
    return {
      pageContent: null,
    };
  },
  watch: {

    // si le slug change appelle la fonction fetchPageContent
    // afin que le contenu change 
    slug(newslug , oldslug){
      if(newslug !== oldslug){
        this.fetchPageContent();
      }
    }
  },
  mounted() {
    this.fetchPageContent();
  },
  methods: {
    async fetchPageContent() {
      try {
        const res = await axios.get(`/api/page_contents?page.slug=/${encodeURIComponent(this.slug)}`);

        console.log(res.data);
        if (res.data.totalItems > 0) {
          this.pageContent = res.data.member[0];
        }
      } catch (err) {
        console.error('Erreur API:', err);
      }
    }
  }
};


  </script>