import api from './api';

class ChatService {
  async getConversations() {
    return api.get('/chat/conversations');
  }

  async getMessages(conversationId) {
    return api.get(`/chat/conversations/${conversationId}/messages`);
  }

  async sendMessage(content, conversationId) {
    return api.post('/chat/messages', { content, conversation_id: conversationId });
  }

  async startConversation(userId, type = 'individual', name = null) {
    return api.post('/chat/conversations', { 
      user_id: userId, 
      type, 
      name 
    });
  }

  async searchUsers(query) {
    return api.get(`/chat/users/search?q=${encodeURIComponent(query)}`);
  }

  async markAsRead(conversationId) {
    return api.post(`/chat/conversations/${conversationId}/read`);
  }
}

export default new ChatService();
