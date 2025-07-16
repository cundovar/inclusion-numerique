<template>
  <div v-if="course">
    <CourseDetail 
      :course="course" 
      :allCourses="allCourses"
      icon-class="fab fa-wordpress"
    />
  </div>
  <div v-else-if="loading" class="loading-container">
    <div class="loading-spinner">
      <i class="fab fa-wordpress spinning"></i>
      <p>Chargement du cours...</p>
    </div>
  </div>
  <div v-else class="error-container">
    <div class="error-message">
      <i class="fas fa-exclamation-triangle"></i>
      <h2>Cours non trouvé</h2>
      <p>Le cours demandé n'existe pas ou n'est pas disponible.</p>
      <button @click="goBack" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Retour aux cours WordPress
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import CourseDetail from './components/CourseDetail.vue'
import wordpressCoursesData from '../data/wordpress-courses.json'

const route = useRoute()
const router = useRouter()

const course = ref(null)
const allCourses = ref([])
const loading = ref(true)

onMounted(() => {
  loadCourse()
})

watch(() => route.params.id, () => {
  loadCourse()
})

const loadCourse = () => {
  loading.value = true
  
  // Charger tous les cours
  allCourses.value = wordpressCoursesData.courses.map(c => ({
    id: c.id,
    title: c.title,
    page: { slug: `/wordpress/${c.id}` },
    category: c.category,
    description: c.description,
    content: c.content
  }))
  
  // Trouver le cours spécifique
  const courseId = parseInt(route.params.id)
  const foundCourse = allCourses.value.find(c => c.id === courseId)
  
  if (foundCourse) {
    course.value = foundCourse
  } else {
    course.value = null
  }
  
  loading.value = false
}

const goBack = () => {
  router.push('/pages/wordpress')
}
</script>

<style scoped>
.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #21759b 0%, #1e6b8c 100%);
}

.loading-spinner {
  text-align: center;
  color: white;
}

.loading-spinner i {
  font-size: 60px;
  margin-bottom: 20px;
  color: #21759b;
}

.spinning {
  animation: spin 2s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.loading-spinner p {
  font-size: 18px;
  margin: 0;
}

.error-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 20px;
}

.error-message {
  text-align: center;
  background: white;
  padding: 40px;
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  max-width: 500px;
}

.error-message i {
  font-size: 60px;
  color: #ffc107;
  margin-bottom: 20px;
}

.error-message h2 {
  color: #2c3e50;
  font-size: 24px;
  margin: 0 0 15px 0;
}

.error-message p {
  color: #6c757d;
  font-size: 16px;
  margin: 0 0 30px 0;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #21759b;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.back-button:hover {
  background: #1e6b8c;
  transform: translateY(-2px);
}
</style>