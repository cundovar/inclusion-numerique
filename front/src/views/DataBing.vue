<template>


<h1> {{ titre}} </h1>


<p>
    
    C’est le lien entre tes données JavaScript et le DOM HTML. En Vue, cela se fait via des expressions dans le template comme {{ message }} ou des directives comme v-bind.

</p>


<p>
    aussi lier des attributs HTML avec v-bind (ou :) :

</p>

<p> exemple avec img :src="imageUrl"</p>

<p>  src={{imageUrl}}</p> pas accepter attribut <html></html>

<h3> les composants </h3>

<First/>


</template>



<script setup>
import First from './components/First.vue'
// echange de donné variable 

const titre="titre de la page"

const message ="message"


</script>


<style scoped></style>