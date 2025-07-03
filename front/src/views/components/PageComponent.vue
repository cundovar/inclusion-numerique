<template>
  <div  class=" pb-96 -z-10 shadow-2xl bg-blue-200 rounded-2xl  ">
    <div v-if="pageContent">
      <div class=" md:ml-20 p-5" v-html=" 'accueil/' +pageContent.menu.label"></div>
      <h1 class="text-2xl text-center p-5 max-md:p-0 mb-5">  {{ pageContent.title }}</h1>
      <!-- <p>{{ pageContent.content }}</p> -->
      <!-- <div class="text-center w-full p-5 bg-gray-700" v-html="pageContent.content"></div> -->
      <div class="" v-if="pageContent.code">
        <div class="text-center  max-md:w-full xl:w-3/4 m-auto  p-5"  v-html="pageContent.code"></div>
      </div>
    </div>
    <div class="flex justify-center items-center h-full" v-else>
      <i class="pi pi-spin m-5 p-5 rounded-full  shadow-2xl pi-cog" style="font-size: 3rem"></i>
    </div>
  </div>
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