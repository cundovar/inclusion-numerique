const BASE_URL = 'https://localhost:8002/api'; // Port du microservice IA

class QCMService {
  async generateQCM(options = {}) {
    const response = await fetch(`${BASE_URL}/qcm/generate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        categoryId: options.categoryId || null,
        difficulty: options.difficulty || 'medium',
        questionsCount: options.questionsCount || 10,
        userId: options.userId || null
      })
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.error || 'Erreur lors de la génération du QCM');
    }

    return await response.json();
  }

  async getSession(sessionId) {
    const response = await fetch(`${BASE_URL}/qcm/session/${sessionId}`);
    
    if (!response.ok) {
      throw new Error('Session QCM non trouvée');
    }

    return await response.json();
  }

  async submitAnswer(sessionId, questionId, answer, timeSpent = 0, userId = null) {
    const response = await fetch(`${BASE_URL}/qcm/session/${sessionId}/answer`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        questionId,
        answer,
        timeSpent,
        userId
      })
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.error || 'Erreur lors de l\'envoi de la réponse');
    }

    return await response.json();
  }

  async completeSession(sessionId) {
    const response = await fetch(`${BASE_URL}/qcm/session/${sessionId}/complete`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      }
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.error || 'Erreur lors de la finalisation du QCM');
    }

    return await response.json();
  }

  async getUserSessions(userId, limit = 10) {
    const params = new URLSearchParams();
    if (userId) params.append('userId', userId);
    if (limit) params.append('limit', limit);

    const response = await fetch(`${BASE_URL}/qcm/sessions?${params}`);
    
    if (!response.ok) {
      throw new Error('Erreur lors de la récupération des sessions');
    }

    return await response.json();
  }

  async checkServiceHealth() {
    try {
      const response = await fetch(`${BASE_URL}/content/health`);
      return response.ok;
    } catch (error) {
      return false;
    }
  }

  // Méthodes utilitaires
  getDifficultyLabel(difficulty) {
    const labels = {
      'easy': 'Facile',
      'medium': 'Moyen',
      'hard': 'Difficile'
    };
    return labels[difficulty] || difficulty;
  }

  calculateGrade(score, total) {
    if (total === 0) return 'N/A';
    
    const percentage = (score / total) * 100;
    
    if (percentage >= 90) return 'Excellent';
    if (percentage >= 80) return 'Très bien';
    if (percentage >= 70) return 'Bien';
    if (percentage >= 60) return 'Assez bien';
    if (percentage >= 50) return 'Passable';
    return 'Insuffisant';
  }

  getGradeColor(score, total) {
    const percentage = (score / total) * 100;
    
    if (percentage >= 80) return 'success';
    if (percentage >= 60) return 'warning';
    return 'danger';
  }
}

export default new QCMService();