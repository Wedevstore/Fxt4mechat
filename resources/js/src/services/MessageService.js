// services/MessageService.js
import axios from 'axios';
class MessageService {
  // Fetch all messages
  getMessages(page = 1, searchQuery = '',chatRoomID) {
    return axios.get('/api/messages', {
      params: {
        page,
        search: searchQuery,
      },
    });
  }
  // Add a new message
  addMessage(message) {
    return axios.post('/api/messages', message);
  }

  // Update an existing message
  updateMessage(message) {
    return axios.put(`/api/messages/${message.id}`, message);
  }

  // Delete a message by ID
  deleteMessage(messageId) {
    return axios.delete(`/api/messages/${messageId}`);
  }
}

export default new MessageService();
