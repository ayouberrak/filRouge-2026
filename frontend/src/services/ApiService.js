import api from './api';

export const AbsenceService = {
  getByClassroom(classroomId, month) {
    return api.get(`/absences/classroom/${classroomId}`, {
      params: { month }
    });
  },
  create(data) {
    return api.post('/absences/create', data);
  },
  delete(id) {
    return api.delete(`/absences/${id}`);
  }
};

export const ActivityService = {
  create(data) {
    return api.post('/activities', data);
  },
  getByClassroom(classroomId) {
    return api.get(`/activities/classroom/${classroomId}`);
  },
  assignToStudents(activityId, studentIds) {
    return api.post(`/activities/${activityId}/assign`, { student_ids: studentIds });
  },
  assignToClassroom(activityId, classroomId) {
    return api.post(`/activities/${activityId}/assign-classroom`, { classroom_id: classroomId });
  }
};

export const BriefService = {
  async getAllList() {
    const response = await api.get('/briefs');
    return response.data;
  },
  async getById(id) {
    const response = await api.get(`/briefs/${id}`);
    return response.data;
  },
  async create(payload) {
    const response = await api.post('/briefs', payload);
    return response.data;
  },
  async update(id, payload) {
    const response = await api.put(`/briefs/${id}`, payload);
    return response.data;
  },
  async delete(id) {
    const response = await api.delete(`/briefs/${id}`);
    return response.data;
  },
  async assignClassrooms(id, classroomIds) {
    const response = await api.post(`/briefs/${id}/assign-classrooms`, {
      classroom_ids: classroomIds
    });
    return response.data;
  },
  async assignSquads(id, squadIds) {
    const response = await api.post(`/briefs/${id}/assign-squads`, {
      squad_ids: squadIds
    });
    return response.data;
  }
};

export const ChatService = {
  async getConversations() {
    return api.get('/chat/conversations');
  },
  async getMessages(conversationId) {
    return api.get(`/chat/conversations/${conversationId}/messages`);
  },
  async sendMessage(content, conversationId) {
    return api.post('/chat/messages', { content, conversation_id: conversationId });
  },
  async startConversation(userId, type = 'individual', name = null) {
    return api.post('/chat/conversations', { 
      user_id: userId, 
      type, 
      name 
    });
  },
  async searchUsers(query) {
    return api.get(`/chat/users/search?q=${encodeURIComponent(query)}`);
  },
  async markAsRead(conversationId) {
    return api.post(`/chat/conversations/${conversationId}/read`);
  }
};

export const DailyReportService = {
  getStats(classroomId) {
    return api.get(`/reports/stats/${classroomId}`);
  },
  getByClassroom(classroomId) {
    return api.get(`/reports/classroom/${classroomId}`);
  },
  submitReport(data) {
    return api.post('/reports', data);
  }
};

export const QuizService = {
  async createSession(payload) {
    const response = await api.post('/quizzes/sessions', payload);
    return response.data;
  },
  async getSessionByBrief(briefId) {
    const response = await api.get(`/quizzes/briefs/${briefId}/session`);
    return response.data;
  },
  async getQuestionsBySession(sessionId) {
    const response = await api.get(`/quizzes/sessions/${sessionId}/questions`);
    return response.data;
  }
};

export const SubmissionService = {
  async getAllByBrief(briefId) {
    const response = await api.get(`/briefs/${briefId}/submissions`);
    return response.data;
  },
  async review(livrableId, payload) {
    const response = await api.post(`/livrables/${livrableId}/reponse`, payload);
    return response.data;
  }
};
