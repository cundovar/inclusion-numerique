//const BASE_URL = 'https://localhost:8003/api'; // Port du microservice Symfony de recherche IA LOCAL
const BASE_URL = 'https://ia-search.varascundo.com/api'; // Port du microservice Symfony de recherche IA PROD 

class IntelligentSearchService {
  async search(query, limit = 10) {
    const response = await fetch(`${BASE_URL}/search/intelligent`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        query,
        limit
      })
    });

    if (!response.ok) {
      const error = await response.json();
      console.log('❌ Erreur backend:', error);
      throw new Error(error.error || 'Erreur lors de la recherche intelligente');
    }

    const result = await response.json();
    console.log('📦 Réponse complète du backend:', result);
    console.log('🔍 Query recherchée:', result.query);
    console.log('📊 Nombre de résultats:', result.total);
    console.log('⏱️ Temps d\'exécution:', result.execution_time + 'ms');
    if (result.analysis) {
      console.log('🧠 Analyse IA:', result.analysis);
    }
    console.log('📄 Résultats détaillés:', result.results);

    return result;
  }

  // Méthode de recherche simple pour compatibilité
  async simpleSearch(query, limit = 10) {
    const params = new URLSearchParams({
      q: query,
      limit: limit.toString()
    });

    const response = await fetch(`${BASE_URL}/search?${params}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.error || 'Erreur lors de la recherche');
    }

    return await response.json();
  }

  async checkServiceHealth() {
    try {
      const response = await fetch(`${BASE_URL}/search/health`);
      return response.ok;
    } catch (error) {
      return false;
    }
  }

  // Formater les résultats pour l'affichage
  formatResults(results) {
    return results.map(result => ({
      ...result,
      formattedScore: this.formatRelevanceScore(result.relevanceScore),
      category: result.category?.name || 'Non catégorisé',
      pageSlug: result.page?.slug || '',
      pageTitle: result.page?.title || result.title,
      displaySummary: result.summary || result.content?.substring(0, 150) + '...',
      hasCode: !!result.code,
      scoreColor: this.getScoreColor(result.relevanceScore),
      formattedDate: result.createdAt ? new Date(result.createdAt).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric'
      }) : ''
    }));
  }

  // Nouvelle méthode pour les couleurs des scores
  getScoreColor(score) {
    if (score >= 80) return 'bg-green-500';
    if (score >= 60) return 'bg-blue-500';
    if (score >= 40) return 'bg-yellow-500';
    if (score >= 20) return 'bg-orange-500';
    return 'bg-red-500';
  }

  formatRelevanceScore(score) {
    if (score >= 50) return 'Très pertinent';
    if (score >= 30) return 'Pertinent';
    if (score >= 15) return 'Moyennement pertinent';
    return 'Peu pertinent';
  }

  // Suggérer des termes de recherche basés sur l'analyse IA
  generateSearchSuggestions(analysis) {
    if (!analysis) return [];

    const suggestions = [];
    
    // Ajouter les concepts comme suggestions
    if (analysis.concepts) {
      suggestions.push(...analysis.concepts.slice(0, 3));
    }

    // Ajouter les technologies
    if (analysis.technologies) {
      suggestions.push(...analysis.technologies.slice(0, 2));
    }

    return [...new Set(suggestions)]; // Supprimer les doublons
  }
}

export default new IntelligentSearchService();