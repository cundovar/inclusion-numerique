<template>
  <div class="question-container">
    <div class="question-card">
      <!-- En-tête de la question -->
      <div class="question-header">
        <div class="question-info">
        
          <span class="question-number">Question {{ questionNumber }}</span>
          <span class="question-topic" v-if="question.topic">{{ question.topic }}</span>
          <span class="difficulty-badge" :class="difficultyClass">
            {{ getDifficultyLabel(question.difficulty) }}
          </span>
        </div>
        <div class="timer" v-if="showTimer">
          <i class="fas fa-clock"></i>
          {{ formatTime(elapsedTime) }}
        </div>
      </div>

      <!-- Question principale -->
      <div class="question-text">
        <h3>{{ question.question }}</h3>
      </div>

      <!-- Options de réponse -->
      <div class="options-container">
        <div 
          v-for="(option, key) in question.options" 
          :key="key"
          class="option-item"
          :class="{ 
            'selected': selectedAnswer === key,
            'correct': showCorrectAnswer && key === question.correct_answer,
            'incorrect': showCorrectAnswer && selectedAnswer === key && key !== question.correct_answer
          }"
          @click="selectAnswer(key)"
        >
          <div class="option-checkbox">
            <span class="option-letter">{{ key }}</span>
            <i 
              v-if="showCorrectAnswer"
              :class="getOptionIcon(key)"
              class="option-icon"
            ></i>
          </div>
          <div class="option-text">{{ option }}</div>
        </div>
      </div>

      <!-- Explication (si disponible) -->
      <div v-if="showExplanation && explanation" class="explanation-container">
        <div class="explanation-header">
          <i class="fas fa-lightbulb"></i>
          Explication
        </div>
        <div class="explanation-text">{{ explanation }}</div>
      </div>

      <!-- Feedback immédiat -->
      <div v-if="showFeedback && selectedAnswer" class="feedback-container">
        <div 
          class="feedback-message"
          :class="{ 
            'correct': isAnswerCorrect, 
            'incorrect': !isAnswerCorrect 
          }"
        >
          <i :class="isAnswerCorrect ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
          <span v-if="isAnswerCorrect">Bonne réponse !</span>
          <span v-else>Réponse incorrecte. La bonne réponse était {{ question.correct_answer }}.</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="question-actions" v-if="!showCorrectAnswer">
      <button 
        v-if="selectedAnswer && allowValidation"
        @click="validateAnswer"
        class="btn btn-primary"
      >
        <i class="fas fa-check"></i>
        Valider la réponse
      </button>
      
      <button 
        v-if="allowSkip"
        @click="skipQuestion"
        class="btn btn-secondary"
      >
        <i class="fas fa-forward"></i>
        Passer
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QCMQuestion',
  props: {
    question: {
      type: Object,
      required: true
    },
    questionNumber: {
      type: Number,
      required: true
    },
    totalQuestions: {
      type: Number,
      required: true
    },
    showTimer: {
      type: Boolean,
      default: true
    },
    allowValidation: {
      type: Boolean,
      default: false
    },
    allowSkip: {
      type: Boolean,
      default: false
    },
    autoSubmit: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      selectedAnswer: null,
      startTime: Date.now(),
      elapsedTime: 0,
      timer: null,
      showCorrectAnswer: false,
      showExplanation: false,
      showFeedback: false,
      explanation: null,
      isAnswerCorrect: false
    };
  },
  computed: {
    difficultyClass() {
      return `difficulty-${this.question.difficulty || 'medium'}`;
    }
  },
  mounted() {
    this.startTimer();
  },
  beforeUnmount() {
    this.stopTimer();
  },
  methods: {
    selectAnswer(answer) {
      if (this.showCorrectAnswer) return;
      
      this.selectedAnswer = answer;
      
      if (this.autoSubmit) {
        this.$emit('answer', answer);
      }
    },

    validateAnswer() {
      if (!this.selectedAnswer) return;
      
      this.$emit('answer', this.selectedAnswer);
      
      if (this.allowValidation) {
        this.showFeedback = true;
        this.isAnswerCorrect = this.selectedAnswer === this.question.correct_answer;
        
        if (this.question.explanation) {
          this.explanation = this.question.explanation;
          this.showExplanation = true;
        }
      }
    },

    skipQuestion() {
      this.$emit('skip');
    },

    startTimer() {
      this.timer = setInterval(() => {
        this.elapsedTime = Date.now() - this.startTime;
      }, 1000);
    },

    stopTimer() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
      }
    },

    formatTime(ms) {
      const seconds = Math.floor(ms / 1000);
      const minutes = Math.floor(seconds / 60);
      const remainingSeconds = seconds % 60;
      
      if (minutes > 0) {
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
      }
      return `${remainingSeconds}s`;
    },

    getDifficultyLabel(difficulty) {
      const labels = {
        'easy': 'Facile',
        'medium': 'Moyen', 
        'hard': 'Difficile'
      };
      return labels[difficulty] || difficulty;
    },

    getOptionIcon(optionKey) {
      if (!this.showCorrectAnswer) return '';
      
      if (optionKey === this.question.correct_answer) {
        return 'fas fa-check-circle text-success';
      } else if (optionKey === this.selectedAnswer && optionKey !== this.question.correct_answer) {
        return 'fas fa-times-circle text-danger';
      }
      
      return '';
    },

    // Méthode pour afficher la correction (appelée depuis le parent)
    showCorrection(isCorrect, correctAnswer, explanationText) {
      this.showCorrectAnswer = true;
      this.isAnswerCorrect = isCorrect;
      
      if (explanationText) {
        this.explanation = explanationText;
        this.showExplanation = true;
      }
      
      this.stopTimer();
    }
  },

  watch: {
    question: {
      handler() {
        // Reset quand la question change
        this.selectedAnswer = null;
        this.showCorrectAnswer = false;
        this.showExplanation = false;
        this.showFeedback = false;
        this.explanation = null;
        this.startTime = Date.now();
        this.elapsedTime = 0;
        
        this.stopTimer();
        this.startTimer();
      },
      immediate: true
    }
  }
};
</script>

<style scoped>
.question-container {
  max-width: 800px;
  margin: 0 auto;
}

.question-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e1e8ed;
  margin-bottom: 20px;
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
  flex-wrap: wrap;
  gap: 15px;
}

.question-info {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.question-number {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 14px;
}

.question-topic {
  background: #f8f9fa;
  color: #6c757d;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
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

.timer {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #6c757d;
  font-weight: 500;
}

.timer i {
  font-size: 14px;
}

.question-text {
  margin-bottom: 25px;
}

.question-text h3 {
  color: #2c3e50;
  font-size: 1.4rem;
  font-weight: 600;
  line-height: 1.5;
  margin: 0;
}

.options-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.option-item {
  display: flex;
  align-items: center;
  padding: 16px;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #fdfdfe;
}

.option-item:hover {
  border-color: #3498db;
  background: #f8f9ff;
}

.option-item.selected {
  border-color: #3498db;
  background: #e3f2fd;
}

.option-item.correct {
  border-color: #27ae60;
  background: #d5f4e6;
}

.option-item.incorrect {
  border-color: #e74c3c;
  background: #ffeaea;
}

.option-checkbox {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 40px;
  margin-right: 15px;
  position: relative;
}

.option-letter {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 2px solid #3498db;
  border-radius: 50%;
  font-weight: 700;
  font-size: 16px;
  color: #3498db;
  transition: all 0.3s ease;
}

.option-item.selected .option-letter {
  background: #3498db;
  color: white;
}

.option-item.correct .option-letter {
  background: #27ae60;
  border-color: #27ae60;
  color: white;
}

.option-item.incorrect .option-letter {
  background: #e74c3c;
  border-color: #e74c3c;
  color: white;
}

.option-icon {
  position: absolute;
  top: -5px;
  right: -5px;
  font-size: 18px;
}

.option-text {
  flex: 1;
  font-size: 16px;
  color: #2c3e50;
  line-height: 1.4;
}

.explanation-container {
  margin-top: 25px;
  padding: 20px;
  background: #f8f9fa;
  border-left: 4px solid #3498db;
  border-radius: 8px;
}

.explanation-header {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #3498db;
  font-weight: 600;
  margin-bottom: 10px;
}

.explanation-text {
  color: #495057;
  line-height: 1.5;
}

.feedback-container {
  margin-top: 20px;
}

.feedback-message {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 15px;
  border-radius: 8px;
  font-weight: 500;
}

.feedback-message.correct {
  background: #d5f4e6;
  color: #155724;
  border: 1px solid #27ae60;
}

.feedback-message.incorrect {
  background: #ffeaea;
  color: #721c24;
  border: 1px solid #e74c3c;
}

.question-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 20px;
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

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-secondary {
  background: #95a5a6;
  color: white;
}

.text-success {
  color: #27ae60 !important;
}

.text-danger {
  color: #e74c3c !important;
}

@media (max-width: 768px) {
  .question-card {
    padding: 20px;
  }
  
  .question-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .question-text h3 {
    font-size: 1.2rem;
  }
  
  .option-item {
    padding: 12px;
  }
  
  .option-checkbox {
    min-width: 35px;
    margin-right: 12px;
  }
  
  .option-letter {
    width: 28px;
    height: 28px;
    font-size: 14px;
  }
  
  .question-actions {
    flex-direction: column;
  }
}
</style>