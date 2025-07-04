<template>
    <div class="flex p-0  pr-48  gap-3 absolute w-full max-md:hidden justify-end items-end text-amber-200  top-3">

        <div class=" h-full flex flex-col gap-1 items-center justify-center">
        <a  href="https://github.com/poleS-dev" target="_blank">
        <i  class="pi pi-github m-auto cursor-pointer " style="font-size: 2.3rem"></i>
    </a>
    <p class="text-center shadow-[0_0_10px_0_rgba(0,0,0,0.5)]  font-bold" > {{ capitalize("github") }}</p>
    


</div>
    <div class="" v-for="logo in logos" :key="logo.alt">

       
        <a class="w-10 h-10  rounded-full overflow-hidden " :href="logo.src" target="_blank">
            <img class=" h-full   " :src="logo.img" :alt="logo.alt">
        </a>
        <p class="text-center shadow-[0_0_10px_0_rgba(0,0,0,0.5)] font-bold"> {{ capitalize(logo.name) }}</p>
     
        
    </div>

    </div>
</template>




<script setup>
import { capitalize } from "../../utlis/stringsUtlis"


const logos =[

    {
        src: "https://developer.mozilla.org/en-US/docs/Web/HTML",
        alt: "mdnhtml",
        img:new URL('../../assets/images/logos/mdn.png', import.meta.url).href,
        name:"html"
    },
    {
        src: "https://developer.mozilla.org/en-US/docs/Web/CSS",
        alt: "mdncss",
        img: new URL('../../assets/images/logos/mdn.png', import.meta.url).href,
        name:"css"
    },
    {
        src: "https://developer.mozilla.org/en-US/docs/Web/JavaScript",
        alt: "mdnjs",
        img: new URL('../../assets/images/logos/mdn.png', import.meta.url).href,
        name:"js"
    },

//     @/assets/... → @ = alias pour ton src/
// ✅ import.meta.url → fonctionne avec Vite / ESM




]




</script>

<style scoped>
img{
    object-fit: cover;
}

a{
    overflow: hidden;
    display: inline-block;
}
</style>