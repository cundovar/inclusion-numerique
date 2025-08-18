<template>

<div class="bg-amber-200 p-2">




  <div class="w-full max-w-sm min-w-[200px] ">      
    <p class="font-bold ">vocabulaire par langage ou technologie :</p>
    <div class="relative">
      <select
      @change="navigateToPage($event, 'vocabulaire')"
          class="w-full bg-amber-100 placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded pl-3 pr-8 py-1.5 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-400 shadow-sm focus:shadow-md appearance-none cursor-pointer">
          <option value="">choisir un langage</option>
         
  
            <option v-for="tech in contentSelect.vocabulaire" :key="tech" :value="tech">{{ tech }}</option>
  
     
      </select>
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="h-5 w-5 ml-1 absolute top-2 right-2.5 text-slate-700">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
      </svg>
    </div>
  </div>
   
  <div class="w-full max-w-sm min-w-[200px] mt-4">      
    <p class="font-bold">fonctions utiles par langage :</p>
    <div class="relative">
      <select
      @change="navigateToPage($event, 'functions')"
          class="w-full bg-amber-100 placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded pl-3 pr-8 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-400 shadow-sm focus:shadow-md appearance-none cursor-pointer">
  
          <option value="">choisir un langage</option>
          <option v-for="func in contentSelect.functions" :key="func" :value="func">{{ func }}</option>
      </select>
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="h-5 w-5 ml-1 absolute top-2.5 right-2.5 text-slate-700">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
      </svg>
    </div>
  </div>
   
</div>

</template>

<script setup>
import { useRouter } from 'vue-router';


const router = useRouter();


const contentSelect={
  vocabulaire: ['html', 'css', 'javascript', 'php'],
  functions: ['javascript', 'php'],
}


const navigateToPage = (event, category) => {
  const selectedValue = event.target.value;
  
  if(selectedValue && category){
    router.push(`/pages/${selectedValue}-${category}`)
  } else {
    alert("veuillez choisir un langage")
  }
}

</script>

 

    