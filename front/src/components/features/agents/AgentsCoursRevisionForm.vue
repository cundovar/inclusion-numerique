<template>
  <section class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">
      Réviser un cours avec l'agent IA
    </h2>
    <p class="text-gray-500 mb-6">
      Indique l'ID du cours, le type de révision et un commentaire précis.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <AppSelect
        v-model="form.coursId"
        label="Cours (titre)"
        placeholder="Choisis un cours"
        :options="coursOptions"
        option-label="label"
        option-value="value"
        filterable
        clearable
        required
        :error="errors.coursId"
        @change="(value) => { loadCoursDetails(value); loadRevisions(value); }"
      />

      <AppSelect
        v-model="form.typeRevision"
        label="Type de révision"
        placeholder="Choisis un type"
        :options="typeOptions"
        option-label="label"
        option-value="value"
        required
        :error="errors.typeRevision"
      />
    </div>

    <div class="mt-6">
      <AppInput
        v-model="form.commentaire"
        type="textarea"
        label="Commentaire"
        placeholder="Ex: Ajouter un exemple pratique dans la section théorie"
        :rows="4"
        required
        :error="errors.commentaire"
      />
    </div>

    <div class="mt-4 flex items-center gap-2">
      <input
        id="appliquerDirectement"
        type="checkbox"
        v-model="form.appliquerDirectement"
        class="h-4 w-4"
      />
      <label for="appliquerDirectement" class="text-sm text-gray-700">
        Appliquer directement la révision
      </label>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-4">
      <AppButton
        variant="primary"
        text-content="Proposer la révision"
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
      <p class="font-semibold mb-2">Révision créée</p>
      <ul class="space-y-1">
        <li><strong>ID:</strong> {{ result.revisionId }}</li>
        <li><strong>Type:</strong> {{ result.typeRevision }}</li>
        <li><strong>Appliquée:</strong> {{ result.appliquee ? 'oui' : 'non' }}</li>
      </ul>
    </div>

    <div v-if="coursActuel?.code" class="mt-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-2">HTML actuel du cours</h3>
      <AppInput
        v-model="coursActuel.code"
        type="textarea"
        :rows="10"
        readonly
      />
    </div>

    <div class="mt-8" v-if="form.coursId">
      <h3 class="text-lg font-semibold text-gray-800 mb-2">Révisions existantes</h3>
      <p v-if="revisionsLoading" class="text-gray-500">Chargement...</p>
      <p v-else-if="!revisions.length" class="text-gray-500">Aucune révision.</p>

      <div v-else class="space-y-4">
        <div
          v-for="revision in revisions"
          :key="revision.id"
          class="border rounded p-4 bg-white"
        >
          <div class="flex flex-wrap items-center gap-3 mb-3 text-sm">
            <span class="font-semibold">#{{ revision.id }}</span>
            <span>{{ revision.typeRevision }}</span>
            <span>{{ revision.appliquee ? 'appliquée' : 'en attente' }}</span>
          </div>

          <AppInput
            v-model="revision.commentaire"
            type="textarea"
            label="Commentaire"
            :rows="3"
          />

          <AppInput
            v-model="revision.nouveauCode"
            type="textarea"
            label="Nouveau code (HTML)"
            :rows="6"
          />

          <div class="mt-4 flex flex-wrap gap-3">
            <AppButton
              variant="outline"
              text-content="Enregistrer la révision"
              @click="saveRevision(revision)"
            />
            <AppButton
              variant="primary"
              text-content="Appliquer la révision"
              :disabled="revision.appliquee"
              @click="applyRevision(revision.id)"
            />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { appliquerRevision, getCours, listCours, listRevisions, reviserCours, updateRevision } from '@/services/agentsCoursService'

const form = reactive({
  coursId: '',
  typeRevision: '',
  commentaire: '',
  appliquerDirectement: false
})

const errors = reactive({
  coursId: '',
  typeRevision: '',
  commentaire: ''
})

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const result = ref(null)
const cours = ref([])
const coursActuel = ref(null)
const revisions = ref([])
const revisionsLoading = ref(false)

const typeOptions = [
  { label: 'Correction', value: 'correction' },
  { label: 'Amélioration', value: 'amelioration' },
  { label: 'Retour élève', value: 'retour_eleve' },
  { label: 'Mise à jour techno', value: 'maj_techno' }
]

const coursOptions = computed(() =>
  (cours.value || []).map((item) => ({
    label: item.titre || item.title || `Cours #${item.id}`,
    value: item.id
  }))
)

const loadCours = async () => {
  try {
    const data = await listCours({ statut: 'publie' })
    cours.value = data
  } catch (error) {
    errorMessage.value = error?.response?.data?.error || error.message
  }
}

const loadCoursDetails = async (courseId) => {
  if (!courseId) {
    coursActuel.value = null
    return
  }

  try {
    coursActuel.value = await getCours(courseId)
  } catch (error) {
    errorMessage.value = error?.response?.data?.error || error.message
  }
}

const loadRevisions = async (courseId) => {
  if (!courseId) {
    revisions.value = []
    return
  }

  try {
    revisionsLoading.value = true
    revisions.value = await listRevisions(courseId)
  } catch (error) {
    errorMessage.value = error?.response?.data?.error || error.message
  } finally {
    revisionsLoading.value = false
  }
}

const validate = () => {
  errors.coursId = form.coursId ? '' : "L'ID du cours est requis"
  errors.typeRevision = form.typeRevision ? '' : 'Le type de révision est requis'
  errors.commentaire = form.commentaire ? '' : 'Le commentaire est requis'

  return !errors.coursId && !errors.typeRevision && !errors.commentaire
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
    const response = await reviserCours({
      coursId: Number(form.coursId),
      typeRevision: form.typeRevision,
      commentaire: form.commentaire,
      appliquerDirectement: form.appliquerDirectement
    })

    result.value = response
    successMessage.value = 'Révision créée avec succès.'
    await loadCoursDetails(form.coursId)
    await loadRevisions(form.coursId)
  } catch (error) {
    errorMessage.value = error?.response?.data?.error || error.message
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.coursId = ''
  form.typeRevision = ''
  form.commentaire = ''
  form.appliquerDirectement = false
  errorMessage.value = ''
  successMessage.value = ''
  result.value = null
  coursActuel.value = null
  errors.coursId = ''
  errors.typeRevision = ''
  errors.commentaire = ''
}

const applyRevision = async (revisionId) => {
  try {
    await appliquerRevision(revisionId)
    successMessage.value = 'Révision appliquée.'
    await loadRevisions(form.coursId)
  } catch (error) {
    errorMessage.value = error?.response?.data?.error || error.message
  }
}

const saveRevision = async (revision) => {
  try {
    await updateRevision(revision.id, {
      commentaire: revision.commentaire,
      nouveauCode: revision.nouveauCode,
      typeRevision: revision.typeRevision
    })
    successMessage.value = 'Révision mise à jour.'
    await loadRevisions(form.coursId)
  } catch (error) {
    errorMessage.value = error?.response?.data?.error || error.message
  }
}

onMounted(loadCours)
</script>
