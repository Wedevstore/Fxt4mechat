// stores/replyStore.js
import { defineStore } from 'pinia';
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();

export const useReplyStore = defineStore('replyStore', {
  state: () => ({
    replyMessage: '',
    replyMessageId: '',
  }),
  actions: {
    setReplyMessage(message, id) {
      this.replyMessage = message;
      this.replyMessageId = id;
    },
    clearReplyMessage() {
      this.replyMessage = '';
      this.replyMessageId = '';
      getMixinValue.storeData.reply_message_id = null
    },
  },
});
