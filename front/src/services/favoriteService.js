import api from './api'

export const favoriteService = {
  /**
   * Verifie si une page est en favori
   */
  async checkFavorite(pageId) {
    const { data } = await api.get(`/me-favorites/check/${pageId}`)
    return data
  },

  /**
   * Ajoute ou retire une page des favoris
   */
  async toggleFavorite(pageId) {
    const { data } = await api.post(`/me-favorites/toggle/${pageId}`)
    return data
  },

  /**
   * Recupere la liste des favoris de l'utilisateur
   */
  async getMyFavorites() {
    const { data } = await api.get('/me-favorites/list-my-favorites')
    return data
  },

  /**
   * Supprime un favori
   */
  async delete(favoriteId) {
    await api.delete(`/me-favorites/${favoriteId}`)
  }
}
