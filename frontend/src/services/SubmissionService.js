import api from './api';

const SubmissionService = {
  /**
   * Récupère tous les rendus des étudiants pour un brief donné
   */
  async getAllByBrief(briefId) {
    const response = await api.get(`/briefs/${briefId}/submissions`);
    return response.data;
  },

  /**
   * Ajoute une réponse (Review) à un livrable
   * @param {number} livrableId
   * @param {Object} payload { status: 'VALIDE'|'INVALID', message: 'Bravo...' }
   */
  async review(livrableId, payload) {
    const response = await api.post(`/livrables/${livrableId}/reponse`, payload);
    return response.data;
  }
};

export default SubmissionService;
