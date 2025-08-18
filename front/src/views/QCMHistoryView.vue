<template>
  <div class="history-container">
    <!-- Header -->
    <div class="history-header">
      <h1 class="history-title">
        <i class="fas fa-history"></i>
        Historique des QCM
      </h1>
      <p class="history-subtitle">Consultez vos performances passées</p>
    </div>

    <!-- Filtres et stats -->
    <div class="history-controls">
      <div class="filters">
        <select v-model="filterCategory" class="filter-select">
          <option value="">Toutes les catégories</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        
        <select v-model="filterDifficulty" class="filter-select">
          <option value="">Toutes les difficultés</option>
          <option value="easy">Facile</option>
          <option value="medium">Moyen</option>
          <option value="hard">Difficile</option>
        </select>
      </div>

      <button @click="loadSessions" class="btn btn-primary">
        <i class="fas fa-sync"></i>
        Actualiser
      </button>
    </div>

    <!-- Statistiques globales -->
    <div v-if="stats" class="global-stats">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ stats.totalSessions }}</div>
          <div class="stat-label">QCM terminés</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ stats.avgScore }}%</div>
          <div class="stat-label">Score moyen</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-trophy"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ stats.bestScore }}%</div>
          <div class="stat-label">Meilleur score</div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
      </div>
      <p>Chargement de l'historique...</p>
    </div>

    <!-- Liste des sessions -->
    <div v-if="!loading && filteredSessions.length > 0" class="sessions-list">
      <div 
        v-for="session in filteredSessions" 
        :key="session.id"
        class="session-card"
        :class="getScoreClass(session)"
        @click="viewSessionDetails(session)"
      >
        <div class="session-header">
          <div class="session-title">
            <h3>{{ session.title }}</h3>
            <span class="session-date">{{ formatDate(session.created_at) }}</span>
          </div>
          
          <div class="session-score">
            <div class="score-circle" :class="getScoreClass(session)">
              {{ Math.round((session.score / session.total_questions) * 100) }}%
            </div>
          </div>
        </div>

        <div class="session-details">
          <div class="detail-item">
            <i class="fas fa-question-circle"></i>
            <span>{{ session.questions_count || session.total_questions }} questions</span>
          </div>

          <div class="detail-item" v-if="session.category_id">
            <i class="fas fa-tag"></i>
            <span>{{ getCategoryName(session.category_id) }}</span>
          </div>

          <div class="detail-item">
            <i class="fas fa-signal"></i>
            <span>{{ getDifficultyLabel(session.difficulty) }}</span>
          </div>

          <div class="detail-item" v-if="session.completed_at">
            <i class="fas fa-clock"></i>
            <span>{{ formatDuration(session) }}</span>
          </div>
        </div>

        <div class="session-progress">
          <div class="progress-bar">
            <div 
              class="progress-fill"
              :class="getScoreClass(session)"
              :style="{ width: ((session.score / session.total_questions) * 100) + '%' }"
            ></div>
          </div>
          <span class="progress-text">
            {{ session.score }}/{{ session.total_questions }} réponses correctes
          </span>
        </div>
      </div>
    </div>

    <!-- Message vide -->
    <div v-if="!loading && filteredSessions.length === 0" class="empty-state">
      <div class="empty-icon">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <h3>Aucun QCM trouvé</h3>
      <p>{{ sessions.length === 0 ? 'Vous n\'avez pas encore terminé de QCM.' : 'Aucun QCM ne correspond aux filtres sélectionnés.' }}</p>
      
      <router-link to="/qcm" class="btn btn-primary">
        <i class="fas fa-play"></i>
        Démarrer un QCM
      </router-link>
    </div>

    <!-- Erreur -->
    <div v-if="error" class="error-container">
      <div class="error-card">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Erreur</h3>
        <p>{{ error }}</p>
        <button @click="loadSessions" class="btn btn-primary">
          Réessayer
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import QCMService from '../services/qcmService.js';

export default {
  name: 'QCMHistoryView',
  data() {
    return {
      sessions: [],
      categories: [
        { id: 1, name: 'Symfony' },
        { id: 2, name: 'Vue.js' },
        { id: 3, name: 'React' },
        { id: 4, name: 'WordPress' }
      ],
      stats: null,
      loading: true,
      error: null,
      filterCategory: '',
      filterDifficulty: ''
    };
  },
  computed: {
    filteredSessions() {
      let filtered = [...this.sessions];
      
      if (this.filterCategory) {
        filtered = filtered.filter(s => s.category_id == this.filterCategory);
      }
      
      if (this.filterDifficulty) {
        filtered = filtered.filter(s => s.difficulty === this.filterDifficulty);
      }
      
      return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }
  },
  async created() {
    await this.loadSessions();
    await this.fetchCategories();
  },
  methods: {
    async loadSessions() {
      this.loading = true;
      this.error = null;
      
      try {
        const userId = this.getCurrentUserId();
        this.sessions = await QCMService.getUserSessions(userId, 50);
        this.calculateStats();
      } catch (error) {
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchCategories() {
      try {
        const response = await fetch('/api/categories');
        const data = await response.json();
        if (data['hydra:member']) {
          this.categories = data['hydra:member'];
        }
      } catch (error) {
        console.error('Erreur lors de la récupération des catégories:', error);
      }
    },

    calculateStats() {
      if (this.sessions.length === 0) {
        this.stats = null;
        return;
      }

      const completedSessions = this.sessions.filter(s => s.status === 'completed');
      
      if (completedSessions.length === 0) {
        this.stats = { totalSessions: 0, avgScore: 0, bestScore: 0 };
        return;
      }

      const totalSessions = completedSessions.length;
      const scores = completedSessions.map(s => (s.score / s.total_questions) * 100);
      const avgScore = Math.round(scores.reduce((sum, score) => sum + score, 0) / totalSessions);
      const bestScore = Math.round(Math.max(...scores));

      this.stats = { totalSessions, avgScore, bestScore };
    },

    getScoreClass(session) {
      const percentage = (session.score / session.total_questions) * 100;
      
      if (percentage >= 80) return 'excellent';
      if (percentage >= 60) return 'good';
      return 'poor';
    },

    getCategoryName(categoryId) {
      const category = this.categories.find(c => c.id == categoryId);
      return category ? category.name : 'Non spécifiée';
    },

    getDifficultyLabel(difficulty) {
      const labels = {
        'easy': 'Facile',
        'medium': 'Moyen',
        'hard': 'Difficile'
      };
      return labels[difficulty] || difficulty || 'Non spécifiée';
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },

    formatDuration(session) {
      if (!session.completed_at || !session.created_at) return 'N/A';
      
      const start = new Date(session.created_at);
      const end = new Date(session.completed_at);
      const duration = Math.floor((end - start) / 1000); // en secondes
      
      const minutes = Math.floor(duration / 60);
      const seconds = duration % 60;
      
      if (minutes > 0) {
        return `${minutes}m ${seconds}s`;
      }
      return `${seconds}s`;
    },

    viewSessionDetails(session) {
      // Pour l'instant, on peut juste afficher une alerte
      // Plus tard, on pourrait naviguer vers une page de détail
      alert(`Détails du QCM:\nScore: ${session.score}/${session.total_questions}\nPourcentage: ${Math.round((session.score / session.total_questions) * 100)}%`);
    },

    getCurrentUserId() {
      return localStorage.getItem('userId') || null;
    }
  }
};
</script>

<style scoped>
.history-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.history-header {
  text-align: center;
  margin-bottom: 40px;
}

.history-title {
  color: #2c3e50;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 10px;
}

.history-title i {
  color: #3498db;
  margin-right: 15px;
}

.history-subtitle {
  color: #7f8c8d;
  font-size: 1.2rem;
  font-weight: 300;
}

.history-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 15px;
}

.filters {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.filter-select {
  padding: 10px 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: border-color 0.3s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #3498db;
}

.global-stats {
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

.sessions-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.session-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e1e8ed;
  cursor: pointer;
  transition: all 0.3s ease;
}

.session-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.session-card.excellent {
  border-left: 4px solid #27ae60;
}

.session-card.good {
  border-left: 4px solid #f39c12;
}

.session-card.poor {
  border-left: 4px solid #e74c3c;
}

.session-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.session-title h3 {
  color: #2c3e50;
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0 0 5px 0;
}

.session-date {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.score-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  color: white;
}

.score-circle.excellent {
  background: linear-gradient(135deg, #27ae60, #229954);
}

.score-circle.good {
  background: linear-gradient(135deg, #f39c12, #e67e22);
}

.score-circle.poor {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
}

.session-details {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 15px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6c757d;
  font-size: 0.9rem;
}

.detail-item i {
  width: 16px;
  text-align: center;
}

.session-progress {
  margin-top: 15px;
}

.progress-bar {
  height: 8px;
  background: #ecf0f1;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-fill {
  height: 100%;
  transition: width 0.3s ease;
}

.progress-fill.excellent {
  background: linear-gradient(90deg, #27ae60, #229954);
}

.progress-fill.good {
  background: linear-gradient(90deg, #f39c12, #e67e22);
}

.progress-fill.poor {
  background: linear-gradient(90deg, #e74c3c, #c0392b);
}

.progress-text {
  font-size: 0.9rem;
  color: #6c757d;
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

.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon i {
  font-size: 4rem;
  color: #bdc3c7;
  margin-bottom: 20px;
}

.empty-state h3 {
  color: #2c3e50;
  margin-bottom: 10px;
}

.empty-state p {
  color: #7f8c8d;
  margin-bottom: 30px;
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
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

@media (max-width: 768px) {
  .history-container {
    padding: 15px;
  }
  
  .history-title {
    font-size: 2rem;
  }
  
  .history-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filters {
    justify-content: center;
  }
  
  .global-stats {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  
  .stat-card {
    padding: 15px;
    flex-direction: column;
    text-align: center;
  }
  
  .session-header {
    flex-direction: column;
    gap: 15px;
  }
  
  .session-details {
    flex-direction: column;
    gap: 10px;
  }
}
</style>