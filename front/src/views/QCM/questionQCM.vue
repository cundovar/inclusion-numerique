<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <div v-if="loading" class="text-center text-xl">Chargement...</div>
    <div v-else-if="error" class="text-red-500 text-center">Erreur: {{ error.message }}</div>
    <div v-else-if="currentQcm" class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
      <h1 class="text-3xl font-bold text-blue-600 mb-6">{{ currentQcm.titre }}</h1>

      <div class="text-gray-500 mb-6">
        Question {{ currentIndex + 1 }} / {{ qcmStore.qcms.length }}
      </div>

      <!-- Choix de réponses -->
      <div v-if="currentQcm.choicesQCMs && currentQcm.choicesQCMs.length" class="space-y-3 mb-6">
        <transition-group name="fade-slide" tag="div" class="space-y-3">
          <div
            v-if="!soumissionBouton"
            v-for="(choice, index) in currentQcm.choicesQCMs"
            :key="'choice-' + choice.id"
            :style="{ transitionDelay: `${index * 50}ms` }"
            class="border border-gray-300 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer transition-all duration-300 hover:scale-105 hover:shadow-lg"
            @click="selectAnswer(choice.id)"
          >
            <el-radio v-model="selectedChoice" :label="choice.id">
              {{ choice.question }}
            </el-radio>
          </div>
        </transition-group>

      
      </div>

      <div v-else-if="currentQcm.choicesQCMs && !currentQcm.choicesQCMs.length" class="text-red-500 mb-6">
        Aucun choix disponible pour cette question
      </div>

      <!-- Icône cadenas séparateur -->
      <transition name="scale-fade">
        <div v-if="soumissionBouton" class="flex justify-center my-6">
          <div class="bg-gray-200 rounded-full p-4 shadow-md">
            <i class="fas fa-lock text-gray-600 text-3xl"></i>
          </div>
        </div>
      </transition>

      <!-- resultats-->
      <transition name="bounce-in">
        <div v-if="showResultat" class="my-6">
          <div v-if="isQuestionIsCorrect" class="bg-green-100 border-2 border-green-500 rounded-lg p-1 text-center animate-pulse-once">
            <i class="fas fa-check-circle text-green-500 text-xl"></i>

          </div>
          <div v-else class="bg-red-100 border-2 border-red-500 rounded-lg p-1 text-center shake">
            <i class="fas fa-times-circle text-red-500 text-xl"></i>
            <div v-if="currentQcm.choicesQCMs && !isQuestionIsCorrect">
              <span v-for="(CorrectChoice ,index) in currentQcm.choicesQCMs">
                <div v-if="CorrectChoice.isCorrect" :key="index">
                  la bonne reponse est :
                  <p class="text-green-500 font-bold">{{ CorrectChoice.question }}</p>
                  <div class="border mr-2 ml-2 mt-2 mb-2 rounded-xl p-5">
                    
                    <p>{{ CorrectChoice.explication }}</p>
                  
                  </div>

                </div> 

              </span>
              <div v-for="(choice ,index) in currentQcm.choicesQCMs" :key="index">

                <div class="" v-if="choice.isCorrect==false" >
                 
                  <div class="flex flex-wrap m-2 justify-center align-center">
                    <p class="text-red-500 font-bold">{{ choice.question }} : </p>
                    <p>{{ choice.explication }}</p>
                  </div>
                </div> 
              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- <div v-if="isCorrectQCM() === selectedChoice" class="text-green-500">
        Correct
      </div>
      <div v-else class="text-red-500">
        Incorrect
      </div> -->

      <div class="flex justify-between items-center">
        <button
        @click="response()"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          reponse
        </button>
        <button
        @click="nextQuestion()"
        v-if="soumissionBouton && pageSuivante"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          question suivante
        </button>
        <button
        @click="nextQuestion()"
        v-if="soumissionBouton && !pageSuivante"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          fin
        </button>





      </div>

    </div>
    <div v-else class="text-center text-gray-500">
     EN COUR DE CONSTRUCTION, IL ME FAUT AJOUTER DES QUESTIONS !!! 
    </div>
 
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { useQCMStore } from '../../store/QCM'
import { useRouter, useRoute } from 'vue-router'


const qcmStore = useQCMStore()
const router = useRouter()
const route = useRoute()
const showResultat = ref(false)
const isQuestionIsCorrect = ref(false)

// petit état local pour la vue
const loading = ref(false)
const error = ref(null)
const selectedChoice = ref(null)
const soumissionBouton = ref(false)

// Fonction pour sélectionner une réponse
const selectAnswer = (choiceId) => {
  selectedChoice.value = choiceId
  // qcmStore.setAnswer(currentIndex.value, choiceId)
  // console.log("✅ Réponse sélectionnée:", choiceId)
  return choiceId
}

const response = () => {
  if (!selectedChoice.value) return // pas de choix sélectionné

  const choixSelect = currentQcm.value.choicesQCMs.find(
    (choice) => choice.id === selectedChoice.value
  )

  // Vérifier que le choix a bien été trouvé
  if (!choixSelect) {
    console.error("Choix non trouvé")
    return
  }

  // Sauvegarder la réponse dans le store
  qcmStore.setAnswer(currentIndex.value, selectedChoice.value)

  // Vérification de la réponse
  isQuestionIsCorrect.value = choixSelect.isCorrect || false

  showResultat.value = true
  soumissionBouton.value = true
}



const nextQuestion = () => {
  const next = currentIndex.value + 1

  // Réinitialiser l'état
  showResultat.value = false
  isQuestionIsCorrect.value = false
  selectedChoice.value = null
  soumissionBouton.value = false

  if (next < qcmStore.qcms.length) {
    // Il y a encore des questions
    router.push({
      name: 'questionQCM',
      params: { index: next },
      query: { language: language.value, niveau: niveau.value },
    })
  } else {
    // Fin du quiz - naviguer vers les résultats
    router.push({
      name: 'resultQCM',
    })
  }
}





// 1) Lire index et filtres depuis l'URL (query) avec fallback sur le store
const currentIndex = computed(() => Number(route.params.index || 0))
const language = computed(() => route.query.language ?? qcmStore.filterQcms?.language ?? null)
const niveau   = computed(() => route.query.niveau   ?? qcmStore.filterQcms?.niveau   ?? null)

// Question actuelle
const currentQcm = computed(() => {
  const qcm = qcmStore.qcms[currentIndex.value] || null
  console.log("🎮 Question actuelle (currentQcm):", qcm)
  console.log("📍 Index actuel:", currentIndex.value)
  console.log("🔑 Clés disponibles dans qcm:", qcm ? Object.keys(qcm) : "null")
  console.log("❓ choicesQCMs:", qcm?.choicesQCMs)
  return qcm
})

// Vérifier si c'est la dernière question
const pageSuivante = computed(() => {
  return (currentIndex.value + 1) < qcmStore.qcms.length
})

// 📊 Comment ça fonctionne :

//   | Situation    | currentIndex | qcmStore.qcms.length | pageSuivante | Bouton affiché      |
//   |--------------|--------------|----------------------|--------------|---------------------|
//   | Question 1/5 | 0            | 5                    | true ✅       | "Question suivante" |
//   | Question 3/5 | 2            | 5                    | true ✅       | "Question suivante" |
//   | Question 5/5 | 4            | 5                    | false ❌      | "Fin"               |

//   ---
//   🎉 Résultat final :

//   1. ✅ Question 1 à 4 → Affiche "Question suivante"
//   2. ✅ Question 5 (dernière) → Affiche "Fin"
//   3. ✅ Clic sur "Fin" → Redirige vers /qcm/result


// 2) Charger les questions au montage (si besoin)
onMounted(async () => {
  console.log("🚀 onMounted - début")
  console.log("🔍 Filtres depuis URL:", { language: language.value, niveau: niveau.value })
  console.log("💾 QCMs déjà en store:", qcmStore.qcms)
  console.log("🎯 Filtres en store:", qcmStore.filterQcms)

  // Si tu utilises pinia-plugin-persistedstate, pas besoin de qcmStore.load()
  // qcmStore.load?.()

  // Si pas de filtres, on ne peut pas fetch
  if (!language.value || !niveau.value) {
    console.log("❌ Pas de filtres, impossible de fetch")
    // Option : rediriger vers la page de sélection
    // router.replace({ name: 'qcm-form' })
    return
  }

  // TEMPORAIRE: Forcer le refetch pour tester
  // if (qcmStore.qcms?.length) {
  //   console.log("✅ Questions déjà chargées, pas de refetch")
  //   return
  // }
  console.log("🔄 Forçage du refetch pour tester les nouveaux groupes")

  loading.value = true
  error.value = null
  try {
    const { data } = await axios.get('/api/q_c_ms', {
      params: {
        'languageQCM.name': language.value,
        'niveauQCM.titre':  niveau.value,
        page: 1,
        itemsPerPage: 100, // ajuste si besoin
      },
    })
    const list = data['hydra:member'] ?? data.member ?? data

    console.log("📦 Data brute reçue:", data)
    console.log("📋 Liste QCMs extraite:", list)
    console.log("🔢 Nombre de QCMs:", list?.length)



qcmStore.setQcms(list) // charger toutes les questions
qcmStore.AleatoireQCM() // melanger les questions
const dixQuestionAleatoire = qcmStore.qcms.slice(0, 10)//prendre 10 qestions 
qcmStore.setQcms(dixQuestionAleatoire)// charger les 10 questions
qcmStore.AleatoireChoices() // melanger les choix

    console.log("💾 QCMs dans le store après setQcms:", qcmStore.qcms)
    console.log("🎯 Question actuelle (index 0):", qcmStore.qcms[0])

    // garde aussi les filtres en store pour persister
    qcmStore.setFilterQcms({ language: language.value, niveau: niveau.value })
  } catch (e) {
    error.value = e
    console.error('Fetch QCM failed:', e)
  } finally {
    loading.value = false
  }

  // Sécurité : si l’index est hors borne, on revient à 0
  if (currentIndex.value < 0 || currentIndex.value >= qcmStore.qcms.length) {
    router.replace({
      name: 'questionQCM',
      params: { index: 0 },
      query: { language: language.value, niveau: niveau.value },
    })
  }
})
</script>

<style scoped>
/* Animation fade-slide pour les choix qui disparaissent */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.5s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(30px) scale(0.9);
}

/* Animation scale-fade pour le message de confirmation */
.scale-fade-enter-active {
  animation: scale-fade-in 0.5s ease-out;
}

.scale-fade-leave-active {
  animation: scale-fade-in 0.3s ease-in reverse;
}

@keyframes scale-fade-in {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Animation bounce-in pour les résultats */
.bounce-in-enter-active {
  animation: bounce-in 0.6s ease-out;
}

.bounce-in-leave-active {
  animation: bounce-in 0.3s ease-in reverse;
}

@keyframes bounce-in {
  0% {
    opacity: 0;
    transform: scale(0.3);
  }
  50% {
    transform: scale(1.1);
  }
  70% {
    transform: scale(0.9);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Animation shake pour les réponses incorrectes */
.shake {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% {
    transform: translateX(0);
  }
  10%, 30%, 50%, 70%, 90% {
    transform: translateX(-10px);
  }
  20%, 40%, 60%, 80% {
    transform: translateX(10px);
  }
}

/* Animation pulse pour les réponses correctes */
.animate-pulse-once {
  animation: pulse-once 0.6s ease-in-out;
}

@keyframes pulse-once {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}
</style>
