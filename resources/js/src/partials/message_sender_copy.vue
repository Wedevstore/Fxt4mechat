<template>
    <div class="sp-static-bar chat__sendArea">
        <div v-if="replyMessage" class="chat__replay p-0 client" id="msg__replay">
            <div class="chat__bdy">
                <div class="close__action" @click="clearReplyMessage">X</div>
                <h6 v-if="replyMessage.contact_name" class="title">{{ replyMessage.contact_name }}</h6>
                <p v-if="replyMessage.message_type === 'text'" class="replay__desc" v-html="replyMessage.value">
                </p>
                <div v-else-if="replyMessage.message_type === 'image'">
                    <img :src="replyMessage.header_image" />
                </div>
                <div v-else-if="replyMessage.message_type === 'video'">
                    <div class="icon">
                        <i class="las la-file-video-o"></i>
                    </div>
                </div>
                <div v-else-if="replyMessage.message_type === 'audio'">
                    <div class="icon">
                        <i class="las la-file-audio"></i>
                    </div>
                </div>
                <div v-else-if="replyMessage.message_type === 'document'">
                    <div class="icon">
                        <i class="las la-file-alt"></i>
                    </div>
                </div>
                <div v-else-if="replyMessage.message_type === 'location'">
                    <div id="map">
                        <img :src="getMixinValue.assetUrl('images/default/map.webp')" alt="" />
                    </div>
                </div> 
				
            </div>
        </div>
        <form class="new-chat-form" action="#" @submit.prevent="sendMessage">
            <div class="MessageField__MessageContainer">
                <textarea v-model="getMixinValue.storeData.message" @keyup.enter="sendMessage"
                    :placeholder="getMixinValue.lang.send_a_message" id="textarea">
                </textarea>
            </div>
            <div class="left-icons">
                <div class="form-icon icon-edit">
                    <i class="las la-edit"></i>
                </div>
            </div>
            <button type="submit" class="form-icon icon-send">
                <i class="las la-paper-plane"></i>
            </button>
            <button type="button" v-if="data.message_loading" class="form-icon icon-send">
                <i class="las la-spin la-spinner"></i>
            </button>
            <div class="dropDown__icon">
                <i class="las la-ellipsis-v"></i>
            </div>
            <div class="bottom-icons position-relative">
                <div style="position: absolute; display: none; bottom: 60px; right: 0" class="emoji_div" tabindex="-1">
                    <EmojiPicker :native="true" @select="onSelectEmoji" />
                </div>
                <button type="button" class="bottom-icon button-gallary">
                    <input type="file" accept="image/*,video/*,audio/*" id="image" class="input-file" name="image"
                        @change="imageUp($event)" />
                    <i class="las la-image"></i>
                </button>
                <button type="button" class="bottom-icon button-paperclip">
                    <input type="file" id="file" class="input-file" name="file" @change="fileUp($event)"
                        accept="application/pdf" />
                    <i class="las la-paperclip"></i>
                </button>
                <div class="chat_popupBox">
                    <button type="button" class="bottom-icon button-saved" @click="cannedMessages">
                        <i class="las la-plus-square"></i>
                    </button>
                    <div class="savad-item-area show-item" id="save__item" tabindex="-1" style="display: none">
                        <div class="saved-item-card">
                            <div class="header-area">
                                <h6 class="title">{{ getMixinValue.lang.saved_replies }}</h6>
                            </div>
                            <div class="body-area">
                                <ul class="span-tag-list mt-0" v-if="data.canned_responses.length > 0">
                                    <li class="cursor-pointer canned_li"
                                        v-for="(response, index) in data.canned_responses" :key="index"
                                        @click="setMessage(response)">
                                        <span class="p-tag chat-sm-text">
                                            {{ response.name }} - {{ response.reply_text }}
                                        </span>
                                    </li>
                                </ul>
                                <p class="desc" v-else>
                                    {{ getMixinValue.lang.no_saved_replies }} >
                                    <a target="_blank" :href="getMixinValue.getUrl('bot-reply/create')">
                                        {{ getMixinValue.lang.admin }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="bottom-icon picker">
                    <i class="las la-grin-hearts"></i>
                </button>
                <button type="button" class="bottom-icon button-mic mic-button-activatin"
                    @click="data.show_audio_recorder = !data.show_audio_recorder">
                    <i class="las la-microphone"></i>
                </button>
                <div class="chat_popupBox">
                    <button type="button" :title="getMixinValue.lang.reply_with_ai" class="bottom-icon robot__replay"
                        @click="openAIReplyModal"
                        >
                        <i class="las la-robot"></i>
                    </button>
                    <div class="savad-item-area robot__popup show-item" v-show="replyWithAIOpened" @modal-close="closeAIReplyModal"  tabindex="-1">
                        <div class="saved-item-card">
                            <div class="header-area">
                                <h6 class="title">{{ getMixinValue.lang.reply_with_ai }}</h6>
                            </div>
                            <div class="body-area">
                                <blockquote class="blockquote">
                                    <div v-if="data.context.length">
                                        <p v-for="(message, index) in data.context" :key="index" class="m-0 mt-1 text-muted">
                                            {{ message }}
                                        </p>
                                    </div>
                                </blockquote>
                                <div v-for="type in replyTypes" :key="type.value">
                                    <input type="radio" :id="type.value" name="reply_type" :value="type.value"
                                        v-model="replyType" :checked="type.value === 'professional'" />
                                    <label :for="type.value">{{ type.label }}</label>
                                </div>
                                <div class="btn-groups mt-3 text-end">
                                    <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
	
                                    <button type="button" v-else class="btn sg-btn-primary" @click="generateAIReply">
                                        {{ getMixinValue.lang.submit }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chat_popupBox">
                    <button type="button" :title="getMixinValue.lang.rewrite_ai_reply"
                        class="bottom-icon action-area ai__replay" @click="openAIRewriteModal">
                        <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="26"
                            height="26">
                            <path id="Layer" class="s0"
                                d="m68 15c4.3-0.2 7.9-0.1 12.1 1.1 4.1 1.2 6.8 0.3 10.9-1.1 3.4-0.1 3.4-0.1 6 0 0 3.3 0 6.6 0 10-4.5 0.5-4.5 0.5-9 1 0.5 37.6 0.5 37.6 1 76 2.6 0.3 5.3 0.7 8 1 0 3.3 0 6.6 0 10-4.3 0.2-7.4 0.2-11.6-1.1-4.1-1.1-5.6-0.3-9.4 1.1-2.7 0.2-5.3 0.1-8 0 0-3.3 0-6.6 0-10q3.1-0.3 6.2-0.7c1.8-0.1 1.8-0.1 2.8-1.3q0.2-3.5 0.2-7 0-1.1 0-2.2 0.1-3.7 0.1-7.3 0-2.5 0.1-5 0.1-6.7 0.1-13.3 0.1-6.8 0.2-13.6 0.2-13.3 0.3-26.6c-3.3-0.3-6.6-0.7-10-1 0-3.3 0-6.6 0-10z" />
                            <path id="Layer" class="s1"
                                d="m0 39c23.4 0 46.9 0 71 0 0 2.3 0 4.6 0 7-21.1 0-42.2 0-64 0 0 11.9 0 23.8 0 36 21.1 0 42.2 0 64 0 0 2.3 0 4.6 0 7-23.4 0-46.9 0-71 0 0-16.5 0-33 0-50z" />
                            <path id="Layer" class="s1"
                                d="m95 39c10.9 0 21.8 0 33 0 0 16.5 0 33 0 50-10.9 0-21.8 0-33 0 0-2.3 0-4.6 0-7 8.6 0 17.2 0 26 0 0-11.9 0-23.8 0-36-8.6 0-17.2 0-26 0 0-2.3 0-4.6 0-7z" />
                        </svg>
                    </button>
                    <div class="savad-item-area ai__popup show-item"  v-show="rewriteWithAIOpened" tabindex="-1">
                        <div class="saved-item-card">
                            <div class="header-area">
                                <h6 class="title">{{ getMixinValue.lang.rewrite_ai_reply }}</h6>
                            </div>
                            <div class="body-area">
                                <blockquote class="blockquote">
                                    <div v-if="data.context.length">
                                        <p v-for="(message, index) in data.context" :key="index" class="m-0 mt-1 text-muted">
                                            {{ message }}
                                        </p>
                                    </div>
                                </blockquote>
                                <blockquote class="blockquote">
                                    <div v-if="data.rewrite_context">
                                    {{ data.rewrite_context }}
                                    </div>
                                </blockquote>
                                <div class="custom__radio">
                                    <div v-for="type in replyTypes" :key="type.value">
                                        <input type="radio" :id="`rewrite_${type.value}`" name="reply_type" :value="type.value"
                                            v-model="selectedReplyType" />
                                        <label :for="`rewrite_${type.value}`">{{ type.label }}</label>
                                    </div>
                                </div>
                                <div class="btn-groups mt-3 text-end">
                                    <button v-if="data.rewrite_context" type="button" class="btn sg-btn-primary btn-lg mx-2" @click="useRewriteAIReply">
                                        {{ getMixinValue.lang.use }}
                                    </button>
                                    <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
                                    <button type="button" v-else class="btn sg-btn-primary btn-lg" @click="rewriteAIReply">
                                        {{ data.rewrite_context ? getMixinValue.lang.regenerate : getMixinValue.lang.generate }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!--Reply Modal -->
        <!-- <Transition>
            <Modal class="sp-modal" :isOpen="replyWithAIOpened" @modal-close="closeAIReplyModal" name="reply-modal">
                <template #header class="modal-title">
                    <div class="row w-100">
                        <div class="col-lg-6">
                            <p class="m-0 mt-3">{{ getMixinValue.lang.reply_with_ai }}</p>
                        </div>
                        <div class="col-lg-6 text-end">
                            <button @click="closeAIReplyModal" type="button" class="btn" style="font-size: 15px"><i
                                    class="las la-times"></i></button>
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="modal-body">
                        <blockquote class="blockquote">
                            <div v-if="data.context.length">
                                <p v-for="(message, index) in data.context" :key="index" class="m-0 mt-1 text-muted">
                                    {{ message }}
                                </p>
                            </div>
                        </blockquote>
                        <div v-for="type in replyTypes" :key="type.value">
                            <input type="radio" :id="type.value" name="reply_type" :value="type.value"
                                v-model="replyType" :checked="type.value === 'professional'" />
                            <label :for="type.value">{{ type.label }}</label>
                        </div>
                    </div>
                </template>
                <template #footer>
                    <div class="modal-footer mt-3">
                        <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
                        <button type="button" v-else class="btn btn-primary btn-lg" @click="generateAIReply">
                            {{ getMixinValue.lang.submit }}
                        </button>
                    </div>
                </template>
            </Modal>
        </Transition> -->

        <!--Rewrite Modal -->
        <!-- <Transition>
            <Modal class="sp-modal" :isOpen="rewriteWithAIOpened" @modal-close="closeAIRewriteModal"
                name="rewrite-modal">
                <template #header class="modal-title">
                    <div class="row w-100">
                        <div class="col-lg-6">
                            <p class="m-0 mt-3">{{ getMixinValue.lang.rewrite_ai_reply }}</p>
                        </div>
                        <div class="col-lg-6 text-end">
                            <button @click="closeAIRewriteModal" type="button" class="btn" style="font-size: 15px">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="modal-body">
                        <blockquote class="blockquote">
                            <div v-if="data.context.length">
                                <p v-for="(message, index) in data.context" :key="index" class="m-0 mt-1 text-muted">
                                    {{ message }}
                                </p>
                            </div>
                        </blockquote>
                        <blockquote class="blockquote">
                            <div v-if="data.rewrite_context">
                                {{ data.rewrite_context }}
                            </div>
                        </blockquote>
                        <div class="custom__radio">
                            <div v-for="type in replyTypes" :key="type.value">
                                <input type="radio" :id="`rewrite_${type.value}`" name="reply_type" :value="type.value"
                                    v-model="selectedReplyType" />
                                <label :for="`rewrite_${type.value}`">{{ type.label }}</label>
                            </div>
                        </div>
                    </div>
                </template>
                <template #footer>
                    <div class="modal-footer mt-3">
                        <button v-if="data.rewrite_context" type="button" class="btn btn-primary btn-lg mx-2"
                            @click="useRewriteAIReply">
                            {{ getMixinValue.lang.use }}
                        </button>
                        <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
                        <button type="button" v-else class="btn btn-primary btn-lg" @click="rewriteAIReply">
                            {{ data.rewrite_context ? getMixinValue.lang.regenerate : getMixinValue.lang.generate }}
                        </button>
                    </div>
                </template>
            </Modal>
        </Transition> -->
        <!-- SaleBot is a whatsapp cloud marketing sass script. -->
        <!-- { data.rewrite_context = null ? getMixinValue.lang.generate : getMixinValue.lang.regenerate}} -->
        <!-- Modal End -->

        <div class="audio-peeker-area" :class="{ active: data.show_audio_recorder }">
            <div class="chat-container">
                <div class="audio-container">
                    <button type="button" class="close-audio-peeker btn-pk btn-pk-round"
                        :title="getMixinValue.lang.cancel" @click="closeAudioPeeker">
                        <i class="las la-times"></i>
                    </button>
                    <button type="button" class="btn-pk btn-pk-round" id="stopRecording" @click="stopRecording"
                        v-if="recording" :title="getMixinValue.lang.stop_recording">
                        <i class="las la-stop"></i>
                    </button>
                    <button type="button" class="btn-pk btn-pk-round" id="startRecording" v-else @click="startRecording"
                        :title="getMixinValue.lang.start_recording">
                        <i class="las la-play"></i>
                    </button>
                    <audio id="audioPlayer" src="" controls></audio>
                    <button type="button" v-if="data.message_loading" class="btn-pk">
                        <i class="las la-spin la-spinner"></i>
                    </button>
                    <button v-else type="button" class="btn-pk" @click="sendRecorderAudio">
                        <i class="las la-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import EmojiPicker from "vue3-emoji-picker";
import { computed, onMounted, reactive, ref, watch } from "vue";
import globalValue from "../mixins/helper.js";
import { decodeHtmlEntities } from '../utils/utility'; // Adjust the import path accordingly
import loadingBtn from '../partials/loading_btn.vue';
import Modal from "../partials/modal.vue";
import { useReplyStore } from '../stores/replyStore';
const replyStore = useReplyStore();
const replyMessage = computed(() => replyStore.replyMessage);
const replyMessageId = computed(() => replyStore.replyMessageId);
const getMixinValue = globalValue();
const replyWithAIOpened = ref(false);
const rewriteWithAIOpened = ref(false);
const { storeData, createFormData, getUrl } = globalValue();

const props = defineProps(["chat_room_id"]);
const emit = defineEmits(["sendMessages"]);
const replyTypes = [
    { value: 'professional', label: getMixinValue.lang.professional },
    { value: 'emotional', label: getMixinValue.lang.emotional },
    { value: 'funny', label: getMixinValue.lang.funny },
    { value: 'potential', label: getMixinValue.lang.potential },
];
const replyType = ref('professional');
const selectedReplyType = ref('professional');
onMounted(() => {
    getMixinValue.storeData.receiver_id = props.chat_room_id;
});
watch(
    () => props.chat_room_id,
    () => {
        getMixinValue.storeData.receiver_id = props.chat_room_id;
    }
);
const data = reactive({
    show_emoji: false,
    show_audio_recorder: false,
    recording: false,
    message_loading: false,
    show_canned_replies: false,
    canned_responses: [],
    audio_chunks: [],
    media_recorder: null,
    replyType: 'professional',
    selectedReplyType: 'professional',
    context: [],
    rewrite_context: null,
});

function onSelectEmoji(emoji) {
    getMixinValue.storeData.message += emoji.i;
}

const clearReplyMessage = () => {
    replyStore.replyMessage = null;  // Clear the replyMessage in the store
};
const fetchMessages = async (chatRoomId, limit) => {
    try {
        // Construct the URL with the chatRoomId and limit
        const url = getMixinValue.getUrl(`contact-messages/${chatRoomId}?limit=${limit}`);
        // Fetch the data from the constructed URL
        const response = await axios.get(url);
        // Check if the response contains messages
        if (response.data && response.data.messages) {
            const lastMessages = response.data.messages;
            return lastMessages;
        } else {
            console.error('Unexpected response format:', response.data);
            return null;
        }
    } catch (error) {
        console.error('Error fetching messages:', error.message);
        return null;
    }
};


const useRewriteAIReply = async () => {
    const context = data.rewrite_context;
    if (context) {
        getMixinValue.storeData.message = context;
    }
    closeAIRewriteModal();
    data.rewrite_context = null;
};
const openAIReplyModal = async () => {
    const context = await fetchMessages(props.chat_room_id, 1);
    if (context) {
        data.context = context;
        rewriteWithAIOpened.value = false;
        replyWithAIOpened.value = !replyWithAIOpened.value;
    }
};

const openAIRewriteModal = async () => {
    const textareaValue = document.querySelector('textarea').value;
    const context = textareaValue ? [textareaValue] : '';
    // console.log(context);
    if (context) {
        data.context = context;
        replyWithAIOpened.value = false;
        rewriteWithAIOpened.value = !rewriteWithAIOpened.value;
        // rewriteWithAIOpened.value = true; // Open the modal after setting the context
    } else {
        toastr.error('Please enter message first.');
    }
};

const closeAIRewriteModal = () => {
    rewriteWithAIOpened.value = false;
};

const closeAIReplyModal = () => {
    replyWithAIOpened.value = false;
};

async function generateAIReply() {
    if (!data.replyType) return;
    getMixinValue.config.loading = true;
    let url = getMixinValue.getUrl('message/generate-ai-reply');
    let context = data.context;
    try {
        const response = await axios.post(url, {
            contact_id: props.chat_room_id,
            reply_type: replyType.value,
            context: context,
        });
        if (response.data.success) {
            const decoder = document.createElement('textarea');
            getMixinValue.storeData.message = decodeHtmlEntities(response.data.content);
            closeAIReplyModal();
        } else {
            toastr.error(response.data.error);
        }
    } catch (error) {
        console.error('Error generating reply:', error);
        getMixinValue.config.loading = false;
    } finally {
        getMixinValue.config.loading = false;
    }
}

async function rewriteAIReply() {
    if (!data.selectedReplyType) return;
    getMixinValue.config.loading = true;
    let url = getMixinValue.getUrl('message/generate-ai-rewrite-reply');
    try {
        const response = await axios.post(url, {
            contact_id: props.chat_room_id,
            reply_type: data.selectedReplyType,
            context: data.context
        });
        if (response.data.success) {
            data.rewrite_context = decodeHtmlEntities(response.data.content);
            // closeAIRewriteModal();
        } else {
            toastr.error(response.data.error);
            console.error('Error:', response.data.error);
            getMixinValue.config.loading = false;
        }
    } catch (error) {
        toastr.error(error);
        console.error('Error generating reply:', error);
    } finally {
        getMixinValue.config.loading = false;
    }
}


async function cannedMessages() {
    if (data.canned_responses.length > 0) {
        return;
    }
    let url = getMixinValue.getUrl("canned-responses");
    await axios
        .get(url)
        .then((response) => {
            if (response.data.success) {
                data.canned_responses = response.data.canned_responses;
            }
        })
        .catch((error) => {
            data.message_loading = false;
            return alert("Something went wrong");
        });
}

async function setMessage(response) {
    getMixinValue.storeData.message = response.reply_text;
}

async function sendMessage() {
    if (!getMixinValue.storeData.message.trim()) {
        return alert("Please enter message");
    }
    getMixinValue.params_data.page = 1;
    await message();

    // Clear input fields
    getMixinValue.storeData.message = ""; // Clear message input
    getMixinValue.storeData.image = null; // Clear image input
    getMixinValue.storeData.document = null; // Clear document input

    document.getElementById("file").value = "";
    document.getElementById("image").value = "";
}

async function imageUp(event) {
    getMixinValue.storeData.image = event.target.files[0];
    await message();
}

async function fileUp(event) {
    getMixinValue.storeData.document = event.target.files[0];
    await message();
}

async function message() {

const replyStore = useReplyStore();

    let config = {
        headers: {
            "Content-Type": "multipart/form-data; charset=utf-8; boundary=" + Math.random().toString().substr(2),
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
    };

    data.message_loading = true;
    let url = getMixinValue.getUrl("send-message");
    const formData = getMixinValue.createFormData();
    if (replyStore.replyMessage) {
        getMixinValue.storeData.reply_message_id = replyStore.replyMessage.id;
        formData.append('reply_message_id', replyStore.replyMessage.id);
    }
    await axios
        // .post(url, formData, config)
        .post(url, getMixinValue.createFormData(), config)
        .then((response) => {
            data.message_next_page_url = true;
            data.message_loading = false;
            if (response.data.success) {
                getMixinValue.storeData.message = "";
                getMixinValue.storeData.image = null; // Clear image input
                getMixinValue.storeData.document = null; // Clear document input
                emit("sendMessages", { message_sender: 1 });
                replyStore.clearReplyMessage(); // Clear reply message state

                return true;
            } else {
            }
        })
        .catch((error) => {
            data.message_loading = false;
            return alert("Something went wrong");
        });
}
let audio_stream, recorder, file;
let recording = ref(false);
async function startRecording() {
    try {
        audio_stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        recording.value = true;
        recorder = new MediaRecorder(audio_stream);
        recorder.ondataavailable = function (e) {
            file = e.data;
        };
        recorder.start();
    } catch (error) {
        toastr.error(error);
        // alert(error);
    }
}

function stopRecording() {
    if (recorder && recorder.state === "recording") {
        recorder.onstop = function () {
            const audio_element = document.getElementById("audioPlayer");
            audio_element.src = URL.createObjectURL(file);
        };
        recorder.stop();
        audio_stream.getAudioTracks()[0].stop();
        recording.value = false;
    }
}
function closeAudioPeeker() {
    data.show_audio_recorder = false;
    if (recorder && recorder.state === "recording") {
        recorder.stop();
        audio_stream.getAudioTracks()[0].stop();
        recording.value = false;
        file = null;
        const audio_element = document.getElementById("audioPlayer");
        audio_element.src = "";
    }
}
async function sendRecorderAudio() {
    if (!file) {
        return alert("Please record audio first");
    }
    const last_file = new File([file], "test.mp3", { type: "audio/mp3" });
    getMixinValue.storeData.image = last_file;
    await message();
    file = null;
}
</script>
