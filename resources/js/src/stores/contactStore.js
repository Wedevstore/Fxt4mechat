import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useContactStore = defineStore('contactStore', {
  state: () => ({
    contacts: [],
    selectedContact: null,
    loading: false,
    error: null,
  }),
  actions: {
    async fetchContacts() {
      this.loading = true;

      let url = getMixinValue.getUrl("contacts-by-client");

      try {
        const response = await axios.get(url);
        this.contacts = response.data;
      } catch (error) {
        this.error = error;
      } finally {
        this.loading = false;
      }
    },
    async addContact(contactData) {
      try {
        const response = await axios.post('/api/contacts', contactData);
        this.contacts.push(response.data);
      } catch (error) {
        this.error = error;
      }
    },
    async updateContact(contactId, contactData) {
      try {
        const response = await axios.put(`/api/contacts/${contactId}`, contactData);
        const index = this.contacts.findIndex(contact => contact.id === contactId);
        if (index !== -1) {
          this.contacts[index] = response.data;
        }
      } catch (error) {
        this.error = error;
      }
    },
    async deleteContact(contactId) {
      try {
        await axios.delete(`/api/contacts/${contactId}`);
        this.contacts = this.contacts.filter(contact => contact.id !== contactId);
      } catch (error) {
        this.error = error;
      }
    },
    selectContact(contact) {
      this.selectedContact = contact;
    },
    clearSelectedContact() {
      this.selectedContact = null;
    },
  },
  getters: {
    getContacts: (state) => state.contacts,
    getContactById: (state) => {
      return (contactId) => state.contacts.find(contact => contact.id === contactId);
    },
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getSelectedContact: (state) => state.selectedContact,
  },
});
