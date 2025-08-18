<template>
  <div class="results-container">
    <!-- Header des résultats -->
    <div class="results-header">
      <div class="score-circle" :class="gradeColorClass">
        <div class="score-percentage">
          {{ Math.round(percentage) }}%
        </div>
        <div class="score-text">{{ gradeText }}</div>
      </div>
      
      <h2 class="results-title">Résultats du QCM</h2>
      <p class="results-subtitle">{{ session.title }}</p>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ results.score }}</div>
          <div class="stat-label">Bonnes réponses</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ results.total - results.score }}</div>
          <div class="stat-label">Erreurs</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ totalTimeFormatted }}</div>
          <div class="stat-label">Temps total</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ averageTimePerQuestion }}s</div>
          <div class="stat-label">Temps/question</div>
        </div>
      </div>
    </div>

    <!-- Graphique de progression -->
    <div class="progress-chart">
      <h3>Répartition des résultats</h3>
      <div class="progress-bar-container">
        <div class="progress-segment correct" :style="{ width: correctPercentage + '%' }">
          <span v-if="correctPercentage > 15">{{ results.score }} correctes</span>
        </div>
        <div class="progress-segment incorrect" :style="{ width: incorrectPercentage + '%' }">
          <span v-if="incorrectPercentage > 15">{{ results.total - results.score }} erreurs</span>
        </div>
      </div>
    </div>

    <!-- Détail des questions -->
    <div class="questions-review">
      <h3>Révision des questions</h3>
      
      <div class="filter-tabs">
        <button 
          @click="filterType = 'all'"
          :class="{ active: filterType === 'all' }"
          class="filter-tab"
        >
          Toutes ({{ session.questions.length }})
        </button>
        <button 
          @click="filterType = 'correct'"
          :class="{ active: filterType === 'correct' }"
          class="filter-tab correct"
        >
          Correctes ({{ correctAnswersCount }})
        </button>
        <button 
          @click="filterType = 'incorrect'"
          :class="{ active: filterType === 'incorrect' }"
          class="filter-tab incorrect"
        >
          Erreurs ({{ incorrectAnswersCount }})
        </button>
      </div>

      <div class="questions-list">
        <div 
          v-for="(question, index) in filteredQuestions" 
          :key="question.id"
          class="question-review-item"
          :class="{ 
            'correct': isQuestionCorrect(question),
            'incorrect': !isQuestionCorrect(question)
          }"
        >
          <div class="question-review-header">
            <div class="question-review-number">
              <span>Question {{ index + 1 }}</span>
              <i 
                :class="isQuestionCorrect(question) ? 'fas fa-check-circle' : 'fas fa-times-circle'"
                :style="{ color: isQuestionCorrect(question) ? '#27ae60' : '#e74c3c' }"
              ></i>
            </div>
            <div class="question-difficulty">
              <span class="difficulty-badge" :class="'difficulty-' + (question.difficulty || 'medium')">
                {{ getDifficultyLabel(question.difficulty) }}
              </span>
            </div>
          </div>

          <div class="question-review-content">
            <h4 class="question-text">{{ question.question }}</h4>
            
            <div class="answers-comparison">
              <div class="answer-item">
                <span class="answer-label">Votre réponse :</span>
                <span 
                  class="answer-value"
                  :class="{ 
                    'correct': isQuestionCorrect(question),
                    'incorrect': !isQuestionCorrect(question)
                  }"
                >
                  {{ getUserAnswer(question) }} - {{ getOptionText(question, getUserAnswer(question)) }}
                </span>
              </div>
              
              <div v-if="!isQuestionCorrect(question)" class="answer-item">
                <span class="answer-label">Bonne réponse :</span>
                <span class="answer-value correct">
                  {{ question.correct_answer }} - {{ getOptionText(question, question.correct_answer) }}
                </span>
              </div>
            </div>

            <div v-if="question.explanation" class="question-explanation">
              <div class="explanation-header">
                <i class="fas fa-lightbulb"></i>
                Explication
              </div>
              <p>{{ question.explanation }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="results-actions">
      <button @click="$emit('restart')" class="btn btn-primary">
        <i class="fas fa-redo"></i>
        Refaire un QCM
      </button>
      
      <button @click="$emit('viewHistory')" class="btn btn-secondary">
        <i class="fas fa-history"></i>
        Voir l'historique
      </button>
      
      <button @click="shareResults" class="btn btn-info">
        <i class="fas fa-share"></i>
        Partager
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QCMResults',
  props: {
    results: {
      type: Object,
      required: true
    },
    session: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      filterType: 'all' // 'all', 'correct', 'incorrect'
    };
  },
  computed: {
    percentage() {
      return (this.results.score / this.results.total) * 100;
    },
    
    gradeText() {
      if (this.percentage >= 90) return 'Excellent';
      if (this.percentage >= 80) return 'Très bien';
      if (this.percentage >= 70) return 'Bien';
      if (this.percentage >= 60) return 'Assez bien';
      if (this.percentage >= 50) return 'Passable';
      return 'À retravailler';
    },
    
    gradeColorClass() {
      if (this.percentage >= 80) return 'grade-excellent';
      if (this.percentage >= 60) return 'grade-good';
      return 'grade-poor';
    },
    
    correctPercentage() {
      return (this.results.score / this.results.total) * 100;
    },
    
    incorrectPercentage() {
      return ((this.results.total - this.results.score) / this.results.total) * 100;
    },
    
    totalTimeFormatted() {
      const totalTime = this.calculateTotalTime();
      const minutes = Math.floor(totalTime / 60);
      const seconds = totalTime % 60;
      
      if (minutes > 0) {
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
      }
      return `${seconds}s`;
    },
    
    averageTimePerQuestion() {
      const totalTime = this.calculateTotalTime();
      return Math.round(totalTime / this.results.total);
    },
    
    correctAnswersCount() {
      return this.session.questions.filter(q => this.isQuestionCorrect(q)).length;
    },
    
    incorrectAnswersCount() {
      return this.session.questions.filter(q => !this.isQuestionCorrect(q)).length;
    },
    
    filteredQuestions() {
      const questions = [...this.session.questions];
      
      switch (this.filterType) {
        case 'correct':
          return questions.filter(q => this.isQuestionCorrect(q));
        case 'incorrect':
          return questions.filter(q => !this.isQuestionCorrect(q));
        default:
          return questions;
      }
    }
  },
  methods: {
    calculateTotalTime() {
      // Calculer le temps total basé sur les user_answers
      let totalTime = 0;
      
      this.session.questions.forEach(question => {
        const answers = question.user_answers || [];
        answers.forEach(answer => {
          totalTime += answer.time_spent || 0;
        });
      });
      
      return totalTime;
    },
    
    getUserAnswer(question) {
      const answers = question.user_answers || [];
      if (answers.length > 0) {
        return answers[answers.length - 1].selected_answer; // Dernière réponse
      }
      return 'Non répondu';
    },
    
    isQuestionCorrect(question) {
      const userAnswer = this.getUserAnswer(question);
      return userAnswer === question.correct_answer;
    },
    
    getOptionText(question, optionKey) {
      return question.options[optionKey] || '';
    },
    
    getDifficultyLabel(difficulty) {
      const labels = {
        'easy': 'Facile',
        'medium': 'Moyen',
        'hard': 'Difficile'
      };
      return labels[difficulty] || difficulty;
    },
    
    shareResults() {
      const shareText = `J'ai obtenu ${this.results.score}/${this.results.total} (${Math.round(this.percentage)}%) au QCM "${this.session.title}" ! 🎯`;
      
      if (navigator.share) {
        navigator.share({
          title: 'Mes résultats QCM',
          text: shareText,
          url: window.location.href
        });
      } else {
        // Fallback: copier dans le presse-papier
        navigator.clipboard.writeText(shareText).then(() => {
          // Vous pouvez ajouter une notification ici
          alert('Résultats copiés dans le presse-papier !');
        });
      }
    }
  }
};
</script>

<style scoped>
.results-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}

.results-header {
  text-align: center;
  margin-bottom: 40px;
}

.score-circle {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  margin: 0 auto 20px;
  border: 8px solid;
  position: relative;
}

.grade-excellent {
  background: linear-gradient(135deg, #d5f4e6, #27ae60);
  border-color: #27ae60;
}

.grade-good {
  background: linear-gradient(135deg, #fff3cd, #f39c12);
  border-color: #f39c12;
}

.grade-poor {
  background: linear-gradient(135deg, #ffeaea, #e74c3c);
  border-color: #e74c3c;
}

.score-percentage {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
}

.score-text {
  font-size: 1rem;
  font-weight: 600;
  color: white;
  margin-top: 5px;
}

.results-title {
  color: #2c3e50;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 10px;
}

.results-subtitle {
  color: #7f8c8d;
  font-size: 1.1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e1e8ed;
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3498db, #2980b9);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
}

.stat-label {
  color: #7f8c8d;
  font-size: 0.9rem;
  font-weight: 500;
}

.progress-chart {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e1e8ed;
  margin-bottom: 30px;
}

.progress-chart h3 {
  color: #2c3e50;
  margin-bottom: 20px;
}

.progress-bar-container {
  display: flex;
  height: 40px;
  border-radius: 20px;
  overflow: hidden;
  background: #ecf0f1;
}

.progress-segment {
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 14px;
  transition: width 0.3s ease;
}

.progress-segment.correct {
  background: linear-gradient(135deg, #27ae60, #229954);
}

.progress-segment.incorrect {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
}

.questions-review {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e1e8ed;
  margin-bottom: 30px;
}

.questions-review h3 {
  color: #2c3e50;
  margin-bottom: 20px;
}

.filter-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.filter-tab {
  padding: 8px 16px;
  border: 2px solid #ddd;
  background: white;
  border-radius: 20px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.filter-tab.active {
  background: #3498db;
  border-color: #3498db;
  color: white;
}

.filter-tab.correct.active {
  background: #27ae60;
  border-color: #27ae60;
}

.filter-tab.incorrect.active {
  background: #e74c3c;
  border-color: #e74c3c;
}

.questions-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.question-review-item {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.question-review-item.correct {
  border-color: #27ae60;
  background: #f8fff9;
}

.question-review-item.incorrect {
  border-color: #e74c3c;
  background: #fffafa;
}

.question-review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.question-review-number {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: #2c3e50;
}

.difficulty-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.difficulty-easy { 
  background: #d4edda; 
  color: #155724; 
}

.difficulty-medium { 
  background: #fff3cd; 
  color: #856404; 
}

.difficulty-hard { 
  background: #f8d7da; 
  color: #721c24; 
}

.question-text {
  color: #2c3e50;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 15px;
}

.answers-comparison {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 15px;
}

.answer-item {
  display: flex;
  gap: 10px;
  align-items: center;
}

.answer-label {
  font-weight: 600;
  color: #6c757d;
  min-width: 130px;
}

.answer-value {
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 500;
}

.answer-value.correct {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.answer-value.incorrect {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.question-explanation {
  background: #f8f9fa;
  border-left: 4px solid #3498db;
  padding: 15px;
  border-radius: 6px;
}

.explanation-header {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #3498db;
  font-weight: 600;
  margin-bottom: 8px;
}

.question-explanation p {
  color: #495057;
  margin: 0;
  line-height: 1.5;
}

.results-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  flex-wrap: wrap;
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

.btn-secondary {
  background: #95a5a6;
  color: white;
}

.btn-info {
  background: linear-gradient(135deg, #17a2b8, #138496);
  color: white;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
  .results-container {
    padding: 15px;
  }
  
  .score-circle {
    width: 120px;
    height: 120px;
  }
  
  .score-percentage {
    font-size: 2rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  
  .stat-card {
    padding: 15px;
    flex-direction: column;
    text-align: center;
  }
  
  .filter-tabs {
    flex-direction: column;
  }
  
  .question-review-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .answers-comparison {
    flex-direction: column;
  }
  
  .answer-item {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .results-actions {
    flex-direction: column;
  }
}
</style>