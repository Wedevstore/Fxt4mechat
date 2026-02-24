import { defineStore } from 'pinia';
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();
export const useChatRoomStore = defineStore('chatRoom', {
  state: () => ({
    chatRooms: [],
    selectedChatRoomId: null,
    client_tags: [],
    contact_tags: [],
  }),
  actions: {
    setChatRooms(chatRooms) {
      this.chatRooms = chatRooms;
    },
    setSelectedChatRoomId(chatRoomId) {
      this.selectedChatRoomId = chatRoomId;
    },

    addChatRoom(chatRoom) {
      this.chatRooms.push(chatRoom);
    },
    removeChatRoom(chatRoomId) {
      this.chatRooms = this.chatRooms.filter(room => room.id !== chatRoomId);
    },

    async fetchClientTags() {
      try {
        let url = getMixinValue.getUrl("tags");
        const response = await axios.get(url);
        this.client_tags = response.data.tags;
      } catch (error) {
        console.error('Error fetching client tags:', error);
      }
    },
    async fetchContactTags() {
      try {
        const url = globalValue().getUrl('tags/contact-tags');
        const response = await axios.get(url, {
          params: {
            chat_room_id: this.selectedChatRoomId,
          },
        });
        this.contact_tags = response.data.tags;
      } catch (error) {
        console.error('Error fetching contact tags:', error);
      }
    },
    

  },
  getters: {
    getChatRoomById: (state) => (id) => {
      return state.chatRooms.find(room => room.id === id);
    },
    totalUnreadMessages: (state) => {
      return state.chatRooms.reduce((total, room) => total + room.total_unread_messages, 0);
    },

    getClientTags: (state) => {
      return state.client_tags;
    },

    getContactTags: (state) => {
      return state.contact_tags;
    },
  },
});
