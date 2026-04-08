import api from './api';

const BriefService = {
  /**
   * Récupère la liste de tous les briefs (Vue Coach)
   */
  async getAllList() {
    const response = await api.get('/briefs?all=true');
    return response.data;
  },

  /**
   * Récupère un brief spécifique avec ses détails
   */
  async getById(id) {
    const response = await api.get(`/briefs/${id}`);
    return response.data;
  },

  /**
   * Crée un nouveau brief complet
   */
  async create(payload) {
    const response = await api.post('/briefs', payload);
    return response.data;
  },

  /**
   * Met à jour un brief existant
   */
  async update(id, payload) {
    const response = await api.put(`/briefs/${id}`, payload);
    return response.data;
  },

  /**
   * Supprime un brief (Action destructive)
   */
  async delete(id) {
    const response = await api.delete(`/briefs/${id}`); // Note: non visible dans api.php, à vérifier
    return response.data;
  },

  /**
   * Assigne le brief à des classes spécifiques
   */
  async assignClassrooms(id, classroomIds) {
    const response = await api.post(`/briefs/${id}/assign-classrooms`, {
      classroom_ids: classroomIds
    });
    return response.data;
  }
};

export default BriefService;
