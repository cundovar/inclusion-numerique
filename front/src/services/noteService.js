import api from './api'

export const noteService = {
  /**
   * Recupere toutes les notes de l'utilisateur
   */
  async getMyNotes() {
    const { data } = await api.get('/notes/my-notes')
    return data
  },

  /**
   * Recupere la note d'une page
   */
  async getNoteByPage(pageId) {
    const { data } = await api.get(`/notes/page/${pageId}`)
    return data
  },

  /**
   * Cree ou met a jour une note
   */
  async createOrUpdate(pageId, content) {
    const { data } = await api.post('/notes', { pageId, content })
    return data
  },

  /**
   * Supprime une note
   */
  async delete(id) {
    await api.delete(`/notes/${id}`)
  }
}
