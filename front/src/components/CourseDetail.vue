<template>
  <div class="course-detail-container">
    <div class="course-header">
      <button @click="goBack" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Retour
      </button>
      
      <div class="course-title-section">
        <div class="course-icon">
          <i :class="iconClass"></i>
        </div>
        <h1 class="course-title">{{ course.title }}</h1>
        <p class="course-description">{{ course.description }}</p>
      </div>
    </div>

    <div class="course-content">
      <div class="content-section">
        <h2>Introduction</h2>
        <p class="introduction">{{ course.content.introduction }}</p>
      </div>

      <div class="content-section" v-if="course.content.steps">
        <h2>Étapes à suivre</h2>
        <ol class="steps-list">
          <li v-for="step in course.content.steps" :key="step" class="step-item">
            {{ step }}
          </li>
        </ol>
      </div>

      <div class="content-section" v-if="course.content.code">
        <h2>Exemple de code</h2>
        <div class="code-block">
          <div class="code-header">
            <span class="language">{{ getLanguage() }}</span>
            <button @click="copyCode" class="copy-button">
              <i class="fas fa-copy"></i>
              {{ copied ? 'Copié !' : 'Copier' }}
            </button>
          </div>
          <pre><code class="language-javascript">{{ course.content.code }}</code></pre>
        </div>
      </div>

      <div class="content-section navigation-section">
        <div class="course-navigation">
          <button 
            v-if="previousCourse" 
            @click="navigateToCourse(previousCourse.id)"
            class="nav-button prev-button"
          >
            <i class="fas fa-chevron-left"></i>
            {{ previousCourse.title }}
          </button>
          
          <button 
            v-if="nextCourse" 
            @click="navigateToCourse(nextCourse.id)"
            class="nav-button next-button"
          >
            {{ nextCourse.title }}
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  course: {
    type: Object,
    required: true
  },
  iconClass: {
    type: String,
    default: 'fas fa-book'
  },
  allCourses: {
    type: Array,
    default: () => []
  }
})

const router = useRouter()
const copied = ref(false)

const previousCourse = ref(null)
const nextCourse = ref(null)

onMounted(() => {
  setupNavigation()
})

const setupNavigation = () => {
  if (props.allCourses.length > 0) {
    const currentIndex = props.allCourses.findIndex(c => c.id === props.course.id)
    if (currentIndex > 0) {
      previousCourse.value = props.allCourses[currentIndex - 1]
    }
    if (currentIndex < props.allCourses.length - 1) {
      nextCourse.value = props.allCourses[currentIndex + 1]
    }
  }
}

const goBack = () => {
  router.go(-1)
}

const copyCode = async () => {
  try {
    await navigator.clipboard.writeText(props.course.content.code)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Erreur lors de la copie:', err)
  }
}

const getLanguage = () => {
  return 'JavaScript'
}

const navigateToCourse = (courseId) => {
  router.push(`${courseId}`)
}
</script>

<style scoped>
.course-detail-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 20px;
  padding-bottom: 120px;
}

.course-header {
  max-width: 1000px;
  margin: 0 auto 40px;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
  margin-bottom: 20px;
}

.back-button:hover {
  background: #5a6268;
  transform: translateY(-2px);
}

.course-title-section {
  text-align: center;
  background: white;
  padding: 40px;
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.course-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #61dafb, #21232a);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.course-icon i {
  font-size: 40px;
  color: white;
}

.course-title {
  font-size: 36px;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 15px 0;
}

.course-description {
  font-size: 18px;
  color: #6c757d;
  margin: 0;
  font-style: italic;
}

.course-content {
  max-width: 1000px;
  margin: 0 auto;
}

.content-section {
  background: white;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

.content-section h2 {
  color: #2c3e50;
  font-size: 24px;
  font-weight: 600;
  margin: 0 0 20px 0;
  padding-bottom: 10px;
  border-bottom: 2px solid #61dafb;
}

.introduction {
  font-size: 16px;
  line-height: 1.8;
  color: #495057;
  text-align: justify;
}

.steps-list {
  padding-left: 20px;
}

.step-item {
  font-size: 16px;
  line-height: 1.7;
  color: #495057;
  margin-bottom: 10px;
  padding-left: 10px;
}

.step-item::marker {
  color: #61dafb;
  font-weight: bold;
}

.code-block {
  background: #2d3748;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #4a5568;
}

.code-header {
  background: #1a202c;
  padding: 12px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #4a5568;
}

.language {
  color: #a0aec0;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.copy-button {
  background: #61dafb;
  color: #1a202c;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}

.copy-button:hover {
  background: #4fd1c7;
  transform: translateY(-1px);
}

.code-block pre {
  padding: 20px;
  margin: 0;
  overflow-x: auto;
  font-family: 'Fira Code', 'Consolas', monospace;
  font-size: 14px;
  line-height: 1.5;
}

.code-block code {
  color: #e2e8f0;
  background: none;
}

.navigation-section {
  background: transparent;
  box-shadow: none;
  padding: 0;
}

.course-navigation {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.nav-button {
  flex: 1;
  max-width: 300px;
  padding: 20px;
  background: white;
  border: 2px solid #61dafb;
  border-radius: 12px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #2c3e50;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 10px;
}

.nav-button:hover {
  background: #61dafb;
  color: white;
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(97, 218, 251, 0.3);
}

.next-button {
  justify-content: flex-end;
  margin-left: auto;
}

.prev-button {
  justify-content: flex-start;
  margin-right: auto;
}

/* Responsive */
@media (max-width: 768px) {
  .course-detail-container {
    padding: 16px;
  }
  
  .course-title-section {
    padding: 25px;
  }
  
  .course-title {
    font-size: 28px;
  }
  
  .course-description {
    font-size: 16px;
  }
  
  .content-section {
    padding: 20px;
  }
  
  .course-navigation {
    flex-direction: column;
  }
  
  .nav-button {
    max-width: none;
  }
  
  .next-button,
  .prev-button {
    margin: 0;
    justify-content: center;
  }
}
</style>