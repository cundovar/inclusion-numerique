<template>
  <section class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">
      Générer un cours avec l'agent IA
    </h2>
    <p class="text-gray-500 mb-6">
      Remplis les champs puis lance la création du cours. L'API Node est séparée de Symfony.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <AppInput
        v-model="form.titre"
        label="Titre"
        placeholder="Ex: Introduction à Node.js"
        required
        :error="errors.titre"
      />

      <AppInput
        v-model="form.duree"
        label="Durée"
        placeholder="Ex: 2h, 3h30"
        required
        :error="errors.duree"
      />

      <AppSelect
        v-model="form.technologie"
        label="Technologie"
        placeholder="Choisis une technologie"
        :options="technologieOptions"
        option-label="label"
        option-value="value"
        filterable
        clearable
        required
        :error="errors.technologie"
      />

      <AppSelect
        v-model="form.niveau"
        label="Niveau"
        placeholder="Choisis un niveau"
        :options="niveauOptions"
        option-label="label"
        option-value="value"
        filterable
        clearable
        required
        :error="errors.niveau"
      />
    </div>

    <!-- Menu : existant ou nouveau -->
    <div class="mt-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Menu *</label>
      <div class="flex gap-4 mb-3">
        <button
          type="button"
          :class="[
            'px-4 py-2 rounded text-sm font-medium transition-colors',
            menuMode === 'existing'
              ? 'bg-blue-500 text-white'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
          @click="menuMode = 'existing'"
        >
          Menu existant
        </button>
        <button
          type="button"
          :class="[
            'px-4 py-2 rounded text-sm font-medium transition-colors',
            menuMode === 'new'
              ? 'bg-blue-500 text-white'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
          @click="menuMode = 'new'"
        >
          Nouveau menu
        </button>
      </div>

      <AppSelect
        v-if="menuMode === 'existing'"
        v-model="form.menuId"
        placeholder="Choisis un menu"
        :options="menuOptions"
        option-label="label"
        option-value="value"
        filterable
        clearable
        :error="errors.menu"
      />

      <AppInput
        v-else
        v-model="form.nouveauMenuLabel"
        placeholder="Ex: Introduction Docker"
        :error="errors.menu"
      />
    </div>

    <div class="mt-6">
      <AppInput
        v-model="form.description"
        type="textarea"
        label="Description (optionnelle)"
        placeholder="Décris rapidement le cours"
        :rows="4"
      />
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-4">
      <AppButton
        variant="primary"
        text-content="Créer le cours"
        :loading="loading"
        @click="submit"
      />
      <AppButton
        variant="outline"
        text-content="Réinitialiser"
        :disabled="loading"
        @click="resetForm"
      />
    </div>

    <div v-if="errorMessage" class="mt-6 text-red-600">
      {{ errorMessage }}
    </div>

    <div v-if="successMessage" class="mt-6 text-green-600">
      {{ successMessage }}
    </div>

    <div v-if="result" class="mt-6 bg-gray-50 border rounded p-4 text-sm">
      <p class="font-semibold mb-2">Cours créé</p>
      <ul class="space-y-1">
        <li><strong>ID:</strong> {{ result.id }}</li>
        <li><strong>Titre:</strong> {{ result.titre }}</li>
        <li><strong>Technologie:</strong> {{ result.technologie }}</li>
        <li><strong>Niveau:</strong> {{ result.niveau }}</li>
        <li><strong>Durée:</strong> {{ result.duree }}</li>
        <li><strong>Statut:</strong> {{ result.statut }}</li>
      </ul>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { creerCours, listerMenus, listerNiveaux, listerTechnologies } from '@/services/agentsCoursService'

const form = reactive({
  titre: '',
  technologie: '',
  niveau: '',
  duree: '',
  description: '',
  menuId: '',
  nouveauMenuLabel: ''
})

const errors = reactive({
  titre: '',
  technologie: '',
  niveau: '',
  duree: '',
  menu: ''
})

const menuMode = ref('existing')

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const result = ref(null)

const technologies = ref([])
const niveaux = ref([])
const menus = ref([])

const technologieOptions = computed(() =>
  (technologies.value || []).map((item) => ({
    label: item.name,
    value: item.name
  }))
)

const niveauOptions = computed(() =>
  (niveaux.value || []).map((item) => ({
    label: item.name,
    value: item.name
  }))
)

const menuOptions = computed(() =>
  (menus.value || []).map((item) => ({
    label: item.categoryName ? `${item.label} (${item.categoryName})` : item.label,
    value: item.id
  }))
)

const loadOptions = async () => {
  try {
    const [techs, levels, menusList] = await Promise.all([
      listerTechnologies(),
      listerNiveaux(),
      listerMenus()
    ])
    technologies.value = techs
    niveaux.value = levels
    menus.value = menusList
  } catch (error) {
    errorMessage.value = error?.response?.data?.error || error.message
  }
}

const validate = () => {
  errors.titre = form.titre ? '' : 'Le titre est requis'
  errors.technologie = form.technologie ? '' : 'La technologie est requise'
  errors.niveau = form.niveau ? '' : 'Le niveau est requis'
  errors.duree = form.duree ? '' : 'La durée est requise'

  if (menuMode.value === 'existing') {
    errors.menu = form.menuId ? '' : 'Sélectionne un menu'
  } else {
    errors.menu = form.nouveauMenuLabel ? '' : 'Saisis le label du menu'
  }

  return !errors.titre && !errors.technologie && !errors.niveau && !errors.duree && !errors.menu
}

const submit = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  result.value = null

  if (!validate()) {
    return
  }

  try {
    loading.value = true
    const payload = {
      titre: form.titre,
      technologie: form.technologie,
      niveau: form.niveau,
      duree: form.duree,
      description: form.description || undefined
    }

    if (menuMode.value === 'existing') {
      payload.menuId = form.menuId
    } else {
      payload.nouveauMenuLabel = form.nouveauMenuLabel
    }

    const response = await creerCours(payload)

    result.value = response
    successMessage.value = 'Cours créé avec succès.'
  } catch (error) {
    errorMessage.value = error?.response?.data?.error || error.message
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.titre = ''
  form.technologie = ''
  form.niveau = ''
  form.duree = ''
  form.description = ''
  form.menuId = ''
  form.nouveauMenuLabel = ''
  menuMode.value = 'existing'
  errorMessage.value = ''
  successMessage.value = ''
  result.value = null
  errors.titre = ''
  errors.technologie = ''
  errors.niveau = ''
  errors.duree = ''
  errors.menu = ''
}

onMounted(loadOptions)
</script>
