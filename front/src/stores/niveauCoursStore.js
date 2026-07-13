import { defineStore } from "pinia";
import { ref } from "vue";
import { useNIveauCourApi } from "../composables/api/useNiveauCoursApi";

export const useNiveauStore=defineStore('niveau',()=>{
    const niveaux=ref([])
    const selectedNiveau=ref(null)
    const loading =ref(false)

    //api
    const {getNiveaux}=useNIveauCourApi()


    //action

    async function fetchNiveaux(){
        if(niveaux.value.length > 0 )return
        loading.value=true
        try{
            niveaux.value = await getNiveaux()
        }catch(error){
            console.error('erreur chargement niveau de cours',error)
        }finally{
            loading.value=false
        }
    }

    function setNiveau(niveau) {
    selectedNiveau.value = selectedNiveau.value === niveau ? null : niveau
  }

  function resetNiveau() {
    selectedNiveau.value = null
  }

  return {
    // État
    niveaux,
    selectedNiveau,
    loading,
    // Actions
    fetchNiveaux,
    setNiveau,
    resetNiveau
  }
},{
    persist: {
        key: 'filter-niveau',
        pick:['selectedNiveau']
    }
})