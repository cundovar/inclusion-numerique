<template>
  <div
    class="md:rounded-2xl md:mt-10 xl:mt-0 pb-96 min-h-screen w-full bg-gray-200 p-6 relative z-20"
  >


  <div class="flex qcm flex-col justify-center items-center max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-8 border border-gray-200">

    <h1 class="text-4xl font-bold text-gray-800 mb-8 flex items-center gap-3">
      <i class="fas fa-brain text-purple-600"></i>
      Formulaire QCM
    </h1>

  <AppSelect
    v-model="selected_niveau"
    cascader
    :options="niveau_qcms"
    :cascader-props="{
      value: 'titre',
      label: 'titre',
    }"
    clearable
    placeholder="Sélectionnez un niveau"
    class="w-full md:w-2/3 mb-4"
  />

  <AppSelect
    v-model="selected_language"
    cascader
    :options="language_qcms"
    :cascader-props="{
      value: 'name',
      label: 'name',
    }"
    clearable
    placeholder="Sélectionnez une langue"
    class="w-full md:w-2/3 mb-6"
  />




    <form @submit.prevent="submitForm" action="" class="w-full">
      <!-- <label for="">Niveau</label>

      <select v-model="selected_niveau" name="" id="">
        <option
          v-for="niveau in niveau_qcms"
          :key="niveau.id"
          :value="niveau.titre"
        >
          {{ niveau.titre }}
        </option>
      </select>
      <label for="">Langue</label>

      <select v-model="selected_language" name="rr" value="fff" id="">
        <option
          v-for="language in language_qcms"
          :key="language.id"
          :value="language.name"
        >
          {{ language.name }}
        </option>
      </select>-->

      <div
        v-if="selected_niveau || selected_language"
        class="bg-gradient-to-br from-blue-50 to-purple-50 flex flex-col jusrounded-xl p-6 border border-blue-200 shadow-sm"
      >
        <p class="text-gray-700 font-semibold mb-4 flex items-center gap-2">
       
          Vous avez sélectionné :
        </p>

        <ul class="flex justify-center flex-col items-center gap-3 flex-wrap mb-6">
          <li
            class="px-4 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold shadow-md flex items-center gap-2 min-w-[140px] justify-center transition-transform hover:scale-105"
            v-if="selected_niveau"
          >
            <i class="fas fa-layer-group"></i>
            {{ selected_niveau }}
          </li>
          <span class="text-2xl text-gray-400"><i class="fas fa-plus-circle"></i></span>
          <li
            class="px-4 py-2 rounded-lg bg-gradient-to-r  from-blue-600 to-blue-500 text-white font-semibold shadow-md flex items-center gap-2 min-w-[140px] justify-center transition-transform hover:scale-105"
            v-if="selected_language"
          >
            <i class="fas fa-code"></i>
            {{ selected_language }}
          </li>
          <li
            class="px-4 py-2 rounded-lg bg-gray-200 text-gray-400 font-semibold flex items-center gap-2 min-w-[140px] justify-center border-2 border-dashed border-gray-300"
            v-else
          >
            <i class="fas fa-question-circle"></i>
            ?
          </li>
        </ul>

          <AppButton
          v-if="selected_niveau && selected_language"
          type="submit"
          variant="primary"
          size="lg"
          rounded="lg"
          class="mx-auto"
        >
          <i class="fas fa-play-circle mr-2"></i>
          GO
          <i class="fas fa-arrow-right ml-2"></i>
        </AppButton>

      </div>
    </form>
  </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, watch } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { useQCMStore } from '../../stores/qcm'
import AppButton from '../../components/ui/AppButton.vue'
import AppSelect from '../../components/ui/AppSelect.vue'

const router = useRouter();
const qcmStore = useQCMStore();

const loading = ref(false);
const error = ref(null);
const qcms = ref([]);
const niveau_qcms = ref([]);
const language_qcms = ref([]);
const selectedAnswers = reactive({});
const results = reactive({});
const selected_niveau = ref(null);
const selected_language = ref(null);

const submitForm = async () => {
  // 1) Valide les sélections
  if (!selected_niveau.value || !selected_language.value) return

  // 2) Sauvegarde les filtres dans le store (pour persister entre pages)
  qcmStore.setFilterQcms({
    language: selected_language.value,
    niveau: selected_niveau.value,
  })
 

  // 3) Navigue vers la page de jeu (PlayQcmView se chargera de fetch)
  router.push({
    name: "questionQCM",
    params: { index: 0 },
    query: {
      language: selected_language.value,
      niveau: selected_niveau.value,
    },
  })
}
// 🧠 watch est une fonction de Vue 3 qui "observe" des valeurs réactives (ref, reactive, computed...)
// ➜ Chaque fois que ces valeurs changent, la fonction fournie sera automatiquement exécutée.

// Ici on surveille deux variables : `selected_niveau` et `selected_language`
// watch([selected_niveau, selected_language], ([niv, lang]) => {
//   if (niv && lang) submitForm()
// })

onMounted(async () => {
  loading.value = true;

  try {
    const niveau = await axios.get("/api/niveau_q_c_ms");
    const language = await axios.get("/api/language_q_c_ms");

    niveau_qcms.value = niveau.data.member;
    language_qcms.value = language.data.member;
  } catch (e) {
    error.value = e;
  } finally {
    loading.value = false;
  }

  console.log("qcms", qcms.value);
  console.log("niveau_qcms", niveau_qcms.value);
  console.log("language_qcms", language_qcms.value);
});
</script>


<style scoped>

.qcm form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    border: 1px solid #272222;
    padding: 10px;
    border-radius: 5px;
    width: 50%;
    margin: auto;
}
.qcm form label {
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 15px;
}
.qcm form select {
    background-color: rgb(211, 178, 178);
    border: 1px solid #272222;
    color: #272222;
    padding: 5px;
    border-radius: 5px;
    width: 50%;
    margin: auto;


}

@media (max-width: 768px) {
    .qcm form {
        width: 100%;
    }
    .qcm form select {
        width: 100%;
    }
}




</style>
