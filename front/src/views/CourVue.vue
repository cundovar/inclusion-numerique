<template>

    <h1 class=" text-xl ">
        test 
    </h1>

<p>

    ref pour les valeur simples ( string number ) 

</p>


    <p class="text-pink-400"> ceci est un test </p>
  {{ message }}

  <h1>

directive vue essentielle
  </h1>

  <p>
    v-bind = lier une valeur dynamiquement avec sont attribu
  </p>

  <p>
    version longue 
  </p>

  <p>
    v-on:click = "counter++"
    v-on:input = "..."
    v-on:submit = "..."
    v-on:change = "..." 
    v-on:blur = "..."
    v-on:focus = "..."
    v-on:mouseover = "..."

  </p>

  <p>
    version courte
  </p>

  <p>
    @click = "counter++"
    @input = "..."
    @submit = "..."
    @change = "..." 
    @blur = "..."
    @focus = "..."
    @mouseover = "..."

  </p>

  <p>
    exemple
  </p>
  <div>

  </div>


</template>

<script setup>
import {ref} from 'vue'

//export default {
//    data(){
//     return{
//         mess:'je suis message usant le v-on court '
//     }
// }
// methods:{
//     onInput(event){
//         this.mess=event.target.value
//     },
//     onEnter(){
//         alert('tu as appuyer sur entrer')
// }
// }
//     },

// ancienne methodes vue 2


const message =ref('test varaible hook ref')

const counter=ref(0);
counter.value++ 

//
const user = reactive({ nom:"aa", age:25 })
user.age++
//  script de input oninput





</script>

<style scoped >

</style>


<!-- ref pour les valeur simples ( string number ) -->
 <!-- reactiv() pour les objets complexe -->
