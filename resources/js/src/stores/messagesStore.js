import { defineStore } from 'pinia';
import MessageService from '@/services/MessageService';
export const useMessagesStore = defineStore('messages', {
  state: () => ({
    messages: [],
    totalMessages: 0, // To store total messages count
    currentPage: 1,
    totalPages: 1,
    searchQuery: '',
    error: null,
    loading: false,
    chatRoomID: null,
  }),

  getters: {
    allMessages: (state) => state.messages,
    getMessageById: (state) => (id) => state.messages.find((message) => message.id === id),
    isLoading: (state) => state.loading,
    hasError: (state) => state.error !== null,
  },

  actions: {
    async fetchMessages({ page = 1, searchQuery = '',chatRoomID= '' } = {}) {
      this.loading = true;
      this.currentPage = page;
      this.searchQuery = searchQuery;

      try {
        const response = await MessageService.getMessages(page, searchQuery);
        this.messages = response.data.messages;
        this.totalMessages = response.data.total; // Assuming API response includes total messages
        this.totalPages = response.data.last_page; // Assuming API response includes total pages
        this.error = null;
        return Promise.resolve(response);
      } catch (error) {
        this.error = error;
        return Promise.reject(error);
      } finally {
        this.loading = false;
      }
    },

    async addMessage(message) {
      try {
        const response = await MessageService.addMessage(message);
        this.messages.push(response.data);
        this.error = null;
        return Promise.resolve(response);
      } catch (error) {
        this.error = error;
        return Promise.reject(error);
      }
    },

    async updateMessage(message) {
      try {
        const response = await MessageService.updateMessage(message);
        const index = this.messages.findIndex((m) => m.id === message.id);
        if (index !== -1) {
          this.messages[index] = response.data;
        }
        this.error = null;
        return Promise.resolve(response);
      } catch (error) {
        this.error = error;
        return Promise.reject(error);
      }
    },

    async deleteMessage(messageId) {
      try {
        await MessageService.deleteMessage(messageId);
        this.messages = this.messages.filter((message) => message.id !== messageId);
        this.error = null;
        return Promise.resolve();
      } catch (error) {
        this.error = error;
        return Promise.reject(error);
      }
    },

    selectMessage(message) {
      this.selectedMessage = message;
    },
  },
});
