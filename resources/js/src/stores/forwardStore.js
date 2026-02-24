import { defineStore } from 'pinia';

export const useForwardStore = defineStore('forwardStore', {
  state: () => ({
    messagesToForward: null,   // Array to store messages selected for forwarding
    isForwarding: false,     // Boolean to track if forwarding is in progress
    forwardingContact: null, // The contact to which messages will be forwarded
  }),
  actions: {
    addMessageToForward(message, messageType) {
      // Check if the message already exists in the messagesToForward array
        // Add the message and its type to the messagesToForward array
        this.messagesToForward = message;
      
    },
    removeMessageFromForward(message) {
      // Filter out the message from the messagesToForward array
      this.messagesToForward = null;
      // this.messagesToForward = this.messagesToForward.filter(msg => msg.message !== message);
    },
    clearMessagesToForward() {
      // Clear the messagesToForward array
      this.messagesToForward = [];
    },
    setForwardingStatus(status) {
      // Set the forwarding status
      this.isForwarding = status;
    },
    setForwardingContact(contact) {
      // Set the forwarding contact
      this.forwardingContact = contact;
    },
    clearForwardingContact() {
      // Clear the forwarding contact
      this.forwardingContact = null;
    },
    forwardMessages() {
      // Check if there are messages to forward and a forwarding contact
      if (this.messagesToForward.length > 0 && this.forwardingContact) {
        this.setForwardingStatus(true);
        // Implement the logic to forward messages to the contact
        setTimeout(() => {
          // Log the messages and the forwarding contact
          console.log(`Messages forwarded to ${this.forwardingContact}:`, this.messagesToForward);
          // Clear the messages and forwarding contact after forwarding
          this.clearMessagesToForward();
          this.clearForwardingContact();
          this.setForwardingStatus(false);
        }, 1000);
      } else {
        // Log a warning if no messages or forwarding contact are selected
        console.warn('No messages or forwarding contact selected.');
      }
    }
  },
  getters: {
    getMessagesToForward: (state) => state.messagesToForward,
    getIsForwarding: (state) => state.isForwarding,
    getForwardingContact: (state) => state.forwardingContact,
    getMessageTypesToForward: (state) => state.messagesToForward.map(msg => msg.messageType),
  },
});
