<template>
  <div class="md:rounded-2xl md:mt-10 xl:mt-0 pb-96 text-blue-500 min-h-screen w-full bg-pink-200 p-6">
    <div v-for="(qcm, qcmIndex) in qcms" :key="qcmIndex" class="mb-8 bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-bold mb-4 text-gray-800">{{ qcm.title }}</h2>

      <div v-for="(choice, choiceIndex) in qcm.choices" :key="choiceIndex" class="mb-3">
        <label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded">
          <input
            type="radio"
            :name="`qcm-${qcmIndex}`"
            :value="choice.label"
            v-model="selectedAnswers[qcmIndex]"
            class="w-4 h-4"
          >
          <span class="text-gray-700">{{ choice.label }}</span>
        </label>
      </div>

      <button
        v-if="selectedAnswers[qcmIndex]"
        @click="checkAnswer(qcmIndex)"
        class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Valider
      </button>

      <div v-if="results[qcmIndex]" class="mt-4 p-4 rounded" :class="results[qcmIndex].isCorrect ? 'bg-green-100' : 'bg-red-100'">
        <p class="font-bold" :class="results[qcmIndex].isCorrect ? 'text-green-800' : 'text-red-800'">
          {{ results[qcmIndex].isCorrect ? '✓ Correct !' : '✗ Incorrect' }}
        </p>
        <p class="mt-2 text-gray-700">{{ results[qcmIndex].explanation }}</p>
        <p class="mt-2 text-sm text-gray-600">{{ qcm.solution }}</p>
      </div>
    </div>
  </div>
</template>



<script setup>
import { ref, onMounted, reactive } from 'vue'

const loading = ref(false)
const error = ref(null)
const qcms = ref([])
const selectedAnswers = reactive({})
const results = reactive({})

const checkAnswer = (qcmIndex) => {
  const qcm = qcms.value[qcmIndex]
  const selectedChoice = qcm.choices.find(c => c.label === selectedAnswers[qcmIndex])

  results[qcmIndex] = {
    isCorrect: selectedChoice.isCorrect,
    explanation: selectedChoice.explanation
  }
}

onMounted(async () => {
  loading.value = true

  try {
    qcms.value = await fetch("/api/qcm")
      .then(res => res.json())
      .catch(e => error.value = e)
  } catch(e) {
    error.value = e
  } finally {
    loading.value = false
  }
})
</script>