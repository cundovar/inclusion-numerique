<template>
  <div
    class="md:rounded-2xl md:mt-10 xl:mt-0 pb-96 text-gray-500 min-h-screen w-full bg-gray-200 p-6"
  >


  <div class="flex qcm flex-col justify-center items-center border">

    <h1 class="text-3xl h-20 font-bold m">fomulaire qcm</h1>

  <el-cascader
    v-model="selected_niveau"
    :options="niveau_qcms"
    :props="{
      value: 'titre',
      label: 'titre',
    }"
    clearable
    placeholder="Select Niveau"
  />

  <el-cascader
  class="mt-4 w-1/2 mx-auto border border-gray-300 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer transition"
    v-model="selected_language"
    :options="language_qcms"
    :props="{
      value: 'name',
      label: 'name',
    }"
    clearable
    placeholder="Select Language"
  />




    <form @submit.prevent="submitForm" action="" class="flex flex-col gap-2">
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
        class="flex border flex-wrap items-center gap-10"
      >
        <p>vous avez selectionné :</p>

        <ul class="flex justify-center items-center gap-2">
          <li
            class="px-2 py-1 rounded border flex justify-center bg-black text-white items-center w-32"
            v-if="selected_niveau"
          >
            {{ selected_niveau }}
          </li>
          <span class="text-xl text-gray-600"><i class="fas fa-plus-circle"></i></span>
          <li
            class="px-2 py-1 rounded border flex justify-center bg-black text-white items-center w-32"
            v-if="selected_language"
          >
            {{ selected_language }}
          </li>
          <li
            class="px-2 py-1 rounded border flex justify-center items-center w-32"
            v-else
          >
            ?
          </li>
        </ul>

     
          <button
          v-if="selected_niveau && selected_language"
          type="submit"
          class="bg-blue-500 text-white flex justify-center items-center w-20 px-3 py-1 rounded hover:bg-blue-600"
        >
          GO
        </button>
        
      </div>
    </form>
  </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, watch } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import {useQCMStore} from '../../store/QCM'
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