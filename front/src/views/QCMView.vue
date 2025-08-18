<template>
  <div class="qcm-container">
    <!-- Header -->
    <div class="qcm-header">
      <h1 class="qcm-title">
        <i class="fas fa-brain"></i>
        QCM Intelligent
      </h1>
      <p class="qcm-subtitle">Testez vos connaissances avec des questions générées par IA</p>
    </div>

    <!-- Configuration du QCM -->
    <div v-if="!session && !loading" class="qcm-config">
      <div class="config-card">
        <h2>Configuration du QCM</h2>
        
        <form @submit.prevent="startQCM" class="config-form">
          <div class="form-group">
            <label for="category">Catégorie :</label>
            <select 
              id="category" 
              v-model="config.categoryId" 
              class="form-control"
            >
              <option value="">Toutes les catégories</option>
              <option 
                v-for="category in categories" 
                :key="category.id" 
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="difficulty">Difficulté :</label>
            <select 
              id="difficulty" 
              v-model="config.difficulty" 
              class="form-control"
            >
              <option value="easy">Facile</option>
              <option value="medium">Moyen</option>
              <option value="hard">Difficile</option>
            </select>
          </div>

          <div class="form-group">
            <label for="questionsCount">Nombre de questions :</label>
            <select 
              id="questionsCount" 
              v-model="config.questionsCount" 
              class="form-control"
            >
              <option value="5">5 questions</option>
              <option value="10">10 questions</option>
              <option value="15">15 questions</option>
              <option value="20">20 questions</option>
            </select>
          </div>

          <button 
            type="submit" 
            class="btn btn-primary btn-large"
            :disabled="generatingQCM"
          >
            <i v-if="generatingQCM" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-play"></i>
            {{ generatingQCM ? 'Génération en cours...' : 'Démarrer le QCM' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading || (session && (!session.questions || session.questions.length === 0))" class="loading-container">
      <div class="loading-spinner">
        <i class="fas fa-cog fa-spin"></i>
      </div>
      <p>{{ loadingMessage || 'Chargement des questions...' }}</p>
    </div>

    <!-- Session QCM -->
    <div v-if="session && session.questions && session.questions.length > 0 && !showResults" class="qcm-session">
      <!-- Progress Bar -->
      <div class="progress-container">
        <div class="progress-info">
          <span>Question {{ currentQuestionIndex + 1 }} / {{ session.questions.length }}</span>
          <span>{{ Math.round(progress) }}%</span>
        </div>
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ width: progress + '%' }"
          ></div>
        </div>
      </div>

      <!-- Question actuelle -->
      <QCMQuestion
        v-if="currentQuestion"
        :question="currentQuestion"
        :questionNumber="currentQuestionIndex + 1"
        :totalQuestions="session.questions.length"
        @answer="handleAnswer"
        :key="currentQuestion.id"
      />

      <!-- Navigation -->
      <div class="qcm-navigation">
        <button 
          v-if="currentQuestionIndex > 0"
          @click="previousQuestion"
          class="btn btn-secondary"
        >
          <i class="fas fa-arrow-left"></i>
          Précédent
        </button>
        
        <button 
          v-if="currentQuestionIndex < session.questions.length - 1"
          @click="nextQuestion"
          class="btn btn-primary"
          :disabled="!hasAnswered"
        >
          Suivant
          <i class="fas fa-arrow-right"></i>
        </button>

        <button 
          v-if="currentQuestionIndex === session.questions.length - 1 && hasAnswered"
          @click="finishQCM"
          class="btn btn-success"
        >
          <i class="fas fa-check"></i>
          Terminer le QCM
        </button>
      </div>
    </div>

    <!-- Résultats -->
    <QCMResults
      v-if="showResults && results"
      :results="results"
      :session="session"
      @restart="restartQCM"
      @viewHistory="goToHistory"
    />

    <!-- Erreur -->
    <div v-if="error" class="error-container">
      <div class="error-card">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Erreur</h3>
        <p>{{ error }}</p>
        <button @click="resetQCM" class="btn btn-primary">
          Recommencer
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import QCMService from '../services/qcmService.js';
import QCMQuestion from './components/QCMQuestion.vue';
import QCMResults from './components/QCMResults.vue';

export default {
  name: 'QCMView',
  components: {
    QCMQuestion,
    QCMResults
  },
  data() {
    return {
      config: {
        categoryId: null,
        difficulty: 'medium',
        questionsCount: 10
      },
      categories: [
        { id: 1, name: 'Symfony' },
        { id: 2, name: 'Vue.js' },
        { id: 3, name: 'React' },
        { id: 4, name: 'WordPress' }
      ],
      session: null,
      currentQuestionIndex: 0,
      userAnswers: {},
      loading: false,
      generatingQCM: false,
      loadingMessage: '',
      error: null,
      showResults: false,
      results: null,
      startTime: null
    };
  },
  computed: {
    currentQuestion() {
      if (!this.session || !this.session.questions) return null;
      return this.session.questions[this.currentQuestionIndex];
    },
    progress() {
      if (!this.session) return 0;
      return ((this.currentQuestionIndex + 1) / this.session.questions.length) * 100;
    },
    hasAnswered() {
      return this.currentQuestion && 
             this.userAnswers[this.currentQuestion.id] !== undefined;
    }
  },
  async created() {
    // Récupérer les catégories depuis l'API principale
    await this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await fetch('/api/categories');
        const data = await response.json();
        if (data['hydra:member']) {
          this.categories = data['hydra:member'];
        }
      } catch (error) {
        console.error('Erreur lors de la récupération des catégories:', error);
        // Garder les catégories par défaut
      }
    },

    async startQCM() {
      this.generatingQCM = true;
      this.loadingMessage = 'Génération des questions par IA...';
      this.error = null;

      try {
        this.session = await QCMService.generateQCM({
          categoryId: this.config.categoryId,
          difficulty: this.config.difficulty,
          questionsCount: parseInt(this.config.questionsCount),
          userId: this.getCurrentUserId()
        });

        // Si les questions ne sont pas encore chargées, les récupérer
        if (!this.session.questions || this.session.questions.length === 0) {
          this.loadingMessage = 'Récupération des questions...';
          this.session = await QCMService.getSession(this.session.id);
        }

        this.currentQuestionIndex = 0;
        this.userAnswers = {};
        this.startTime = Date.now();
        
      } catch (error) {
        this.error = error.message;
      } finally {
        this.generatingQCM = false;
        this.loadingMessage = '';
      }
    },

    async handleAnswer(answer) {
      if (!this.currentQuestion) return;

      const questionStartTime = this.questionStartTime || Date.now();
      const timeSpent = Date.now() - questionStartTime;

      this.userAnswers[this.currentQuestion.id] = {
        answer,
        timeSpent: Math.round(timeSpent / 1000) // en secondes
      };

      try {
        const response = await QCMService.submitAnswer(
          this.session.id,
          this.currentQuestion.id,
          answer,
          Math.round(timeSpent / 1000),
          this.getCurrentUserId()
        );

        // Stocker la réponse correcte pour affichage
        this.userAnswers[this.currentQuestion.id].isCorrect = response.is_correct;
        this.userAnswers[this.currentQuestion.id].correctAnswer = response.correct_answer;
        this.userAnswers[this.currentQuestion.id].explanation = response.explanation;

      } catch (error) {
        console.error('Erreur lors de l\'envoi de la réponse:', error);
        // Continuer même si l'envoi échoue
      }
    },

    nextQuestion() {
      if (this.currentQuestionIndex < this.session.questions.length - 1) {
        this.currentQuestionIndex++;
        this.questionStartTime = Date.now();
      }
    },

    previousQuestion() {
      if (this.currentQuestionIndex > 0) {
        this.currentQuestionIndex--;
      }
    },

    async finishQCM() {
      this.loading = true;
      this.loadingMessage = 'Calcul des résultats...';

      try {
        this.results = await QCMService.completeSession(this.session.id);
        this.showResults = true;
      } catch (error) {
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    restartQCM() {
      this.resetQCM();
    },

    resetQCM() {
      this.session = null;
      this.currentQuestionIndex = 0;
      this.userAnswers = {};
      this.error = null;
      this.showResults = false;
      this.results = null;
      this.loading = false;
      this.generatingQCM = false;
    },

    goToHistory() {
      this.$router.push('/qcm/history');
    },

    getCurrentUserId() {
      // À adapter selon votre système d'authentification
      return localStorage.getItem('userId') || null;
    }
  },

  mounted() {
    this.questionStartTime = Date.now();
  }
};
</script>

<style scoped>
.qcm-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.qcm-header {
  text-align: center;
  margin-bottom: 40px;
}

.qcm-title {
  color: #2c3e50;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 10px;
}

.qcm-title i {
  color: #3498db;
  margin-right: 15px;
}

.qcm-subtitle {
  color: #7f8c8d;
  font-size: 1.2rem;
  font-weight: 300;
}

.config-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e1e8ed;
}

.config-card h2 {
  color: #2c3e50;
  margin-bottom: 25px;
  font-size: 1.8rem;
}

.config-form .form-group {
  margin-bottom: 20px;
}

.config-form label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #34495e;
}

.form-control {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #3498db;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-secondary {
  background: #95a5a6;
  color: white;
}

.btn-success {
  background: linear-gradient(135deg, #27ae60, #229954);
  color: white;
}

.btn-large {
  width: 100%;
  padding: 16px;
  font-size: 18px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.progress-container {
  margin-bottom: 30px;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
}

.progress-bar {
  height: 8px;
  background: #ecf0f1;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3498db, #2980b9);
  transition: width 0.3s ease;
}

.qcm-navigation {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 30px;
}

.loading-container {
  text-align: center;
  padding: 60px 20px;
}

.loading-spinner i {
  font-size: 3rem;
  color: #3498db;
  margin-bottom: 20px;
}

.error-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
}

.error-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e74c3c;
}

.error-card i {
  font-size: 3rem;
  color: #e74c3c;
  margin-bottom: 20px;
}

.error-card h3 {
  color: #e74c3c;
  margin-bottom: 15px;
}

@media (max-width: 768px) {
  .qcm-container {
    padding: 15px;
  }
  
  .qcm-title {
    font-size: 2rem;
  }
  
  .config-card {
    padding: 20px;
  }
  
  .qcm-navigation {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>