import api from './api';

const QuizService = {
  /**
   * Crée une session de quiz associée à un brief avec ses questions
   * @param {Object} payload { brief_id, timer_minutes: 30, passing_score: 1, questions: [...] }
   */
  async createSession(payload) {
    const response = await api.post('/quizzes/sessions', payload);
    return response.data;
  },

  /**
   * Récupère la session de quiz pour un brief donné
   */
  async getSessionByBrief(briefId) {
    const response = await api.get(`/quizzes/briefs/${briefId}/session`);
    return response.data;
  },

  /**
   * Récupère les questions pour une session de quiz spécifique
   */
  async getQuestionsBySession(sessionId) {
    const response = await api.get(`/quizzes/sessions/${sessionId}/questions`);
    return response.data;
  }
};

export default QuizService;
