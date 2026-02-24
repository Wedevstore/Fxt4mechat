<template>
    <!-- <div> -->
    <div class="main-wrapper-header sticky-tops sticky-offset" @click="emit('closeAllModals')">
        <span class="chat-customtoggle d-lg-none">
            <span class="icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 6H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                    <path d="M21 12H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                    <path d="M18 18H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                </svg>
            </span>
        </span>
        <div class="main-author-card">
            <div class="author-image">
                <img :src="data.user.image" :alt="data.user.name" />
            </div>
            <div class="author-content">
                <h6 class="title">{{ data.user.name }}</h6>
                <p class="text">{{ data.user.phone }}</p>
            </div>
        </div>
        <div class="quick-access-area">
            <div class="badge-text text-ellipss" v-if="data.can_not_reply">
                {{ getMixinValue.lang.cannot_reply }}
            </div>
            <select class="js-example-basic-single dropdown-select-item form-select dropdown quick-access-select"
                @change="assignStaff()" aria-label="Default select example" v-model="data.user.assignee_id">
                <option value="">{{ getMixinValue.lang.unassigned }}</option>
                <option v-for="(staff, index) in props.staffs" :key="index" :value="staff.id">{{ staff.name }}</option>
            </select>

            <!-- Modal -->
            <button v-if="data.user.source == 'whatsapp'" type="button" class="documentory-modal-btn btn"
                @click="openModalById('sendTemplateModal')">
                <i class="las la-file-alt"></i>
            </button>
            <Transition v-if="data.user.source == 'whatsapp'">
                <Modal class="sp-modal" :isOpen="isModalOpened" @modal-close="closeModal" name="note-modal">
                    <template #header class="modal-title">
                        <div class="row w-100">
                            <div class="col-lg-6">
                                <p class="m-0 mt-3">{{ getMixinValue.lang.templates }}</p>
                            </div>
                            <div class="col-lg-6 text-end">
                                <button @click="closeModal" type="button" class="btn" style="font-size: 15px"><i
                                        class="las la-times"></i></button>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="modal-body">
                            <div class="row add-coupon">
                                <div class="col-lg-12" v-for="(template, index) in props.templates.data" :key="index">
                                    <a :href="getMixinValue.getUrl('send-template?template_id=' + template.id + '&contact_id=' + props.chat_room_id)"
                                        target="_blank">
                                        <div class="mb-4 canned_response_div">
                                            <p class="m-0">
                                                {{ getMixinValue.lang.title }} : <strong>{{ template.name }}</strong>
                                            </p>
                                            <span>{{ getMixinValue.lang.category }} : {{ template.category }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-12 text-center" v-if="props.templates.next_page_url">
                                    <loadingBtn v-if="props.templates.loading"></loadingBtn>
                                    <a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary"
                                        @click="loadTemplate(props.templates.next_page_url)">
                                        <span>{{ getMixinValue.lang.load_more }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #footer>
                        <div class="modal-footer mt-3">
                            <button type="button" class="btn btn-primary btn-lg" style="display: none">
                                {{ getMixinValue.lang.save }}
                            </button>
                        </div>
                    </template>
                </Modal>
            </Transition>
            <div class="action-card">
                <div class="dropdown">
                    <a class="dropdown-toggle" href="javascript:void(0);" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="las la-ellipsis-v"></i>
                    </a>
                    <ul class="dropdown-menu" style="">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" @click="blockConatct(data.user.id)"
                                :title="getMixinValue.lang.block_contact">
                                {{ getMixinValue.lang.block_contact }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" @click="clearChat(data.user.id)"
                                :title="getMixinValue.lang.clear_chat">
                                {{ getMixinValue.lang.clear_chat }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="sp-main-wrapper-content">
        <div v-for="(day, dayIndex) in data.messages" :key="dayIndex">
            <h6 class="sm-title text-center" :class="dayIndex > 0 ? 'mt--40' : ''">{{ day.date }}</h6>
            <div v-for="(message, msgIndex) in day.messages" :key="msgIndex" :class="message.class"
                :style="message.is_contact_msg ? data.audio_style_left : data.audio_style_right">

                <!-- Campaign Message -->
                <div v-if="message.is_campaign_msg" class="single-sp-card">
                    <div class="sp-card-img" :class="{ 'has-vedio-icon': message.header_video }">
                        <img v-if="message.header_image" :src="message.header_image" alt="Card Image" />
                        <a v-if="message.header_video" :href="message.header_video"
                            class="vedio-player-btn popup-video">
                            <i class="las la-play-circle"></i>
                        </a>
                    </div>
                    <div v-if="message.header_video">
                        <vue-plyr :options="data.options">
                            <video width="50" height="50" controls>
                                <source :src="message.header_video" type="video/mp4" />
                            </video>
                        </vue-plyr>
                    </div>
                    <div v-if="message.header_audio">
                        <vue-plyr>
                            <audio>
                                <source :src="message.header_audio" type="audio/mp3" />
                            </audio>
                        </vue-plyr>
                    </div>
                    <div class="card-content">
                        <h6 v-if="message.header_text" class="title">{{ message.header_text }}</h6>
                        <p class="desc" v-html="message.value"></p>
                        <p v-if="message.footer_text" class="bottom-text">{{ message.footer_text }}</p>
                        <div v-for="(button, index) in message.buttons" :key="index">
                            <a v-if="button.type == 'a'" :href="button.value" target="_blank"
                                class="card-btn card-btn-border w-100">{{ button.text }}</a>
                            <button v-else type="button" class="card-btn card-btn-border w-100">
                                {{ button.text }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Interactive Card -->
                <div v-if="message.message_type == 'interactive_button' || message.message_type == 'interactive_list'"
                    class="interactive__card">
                    <div class="header__part">
                        <div v-if="message.header_video">
                            <vue-plyr :options="data.options">
                                <video width="50" height="50" controls>
                                    <source :src="message.header_video" type="video/mp4" />
                                </video>
                            </vue-plyr>
                        </div>
                        <div v-if="message.header_audio">
                            <audio controls>
                                <source :src="message.header_audio" type="audio/mp3" />
                                Your browser does not support the audio tag.
                            </audio>
                        </div>
                        <a v-if="message.header_image" :href="message.header_image" target="_blank">
                            <img :src="message.header_image" alt="" />
                        </a>
                        <div v-if="message.header_text" class="header__txt">{{ message.header_text }}</div>
                    </div>
                    <p v-if="message.value">
                        {{ message.value }}
                    </p>
                    <div v-if="message.footer_text" class="footer__txt">
                        {{ message.footer_text }}
                    </div>
                    <div class="card-content mt-3 position-relative" v-for="(button, index) in message.buttons"
                        :key="index">
                        <a v-if="button.type == 'a'" :href="button.value" target="_blank"
                            class="card-btn card-btn-border mb-3">{{ button.text }}</a>
                        <button v-else type="button" class="card-btn card-btn-border ">
                            {{ button.text }}
                        </button>
                    </div>
                </div>
                <!-- Interactive Card -->

                <!-- Other Message -->
                <div v-else class="chat-message-width">
                    <div v-if="message.contacts.length > 0" class="single-sp-card sp-info-card">
                        <div class="card-content text-center">
                            <h6 class="title">{{ message.contacts[0].name.formatted_name }}</h6>
                            <p class="info"><i class="lab la-whatsapp"></i>{{ message.contacts[0].phones[0].phone }}</p>
                            <a :href="'https://wa.me/' + message.contacts[0].phones[0].wa_id" target="_blank"
                                class="card-btn">{{ getMixinValue.lang.chat }}</a>
                        </div>
                    </div>


                    <!-- Chat Replay -->
                    <!-- <div class="chat__replay">
                         <div class="chat__bdy">
                            <h6 class="title">You</h6>
                            <p class="replay__desc">
                                It sounds like you're referring to a tool or service named "Salebot," which is used for marketing via WhatsApp.
                                 This type of system typically allows businesses to engage with customers, send promotional messages, automate 
                                 responses, and manage customer interactions through the WhatsApp platform. If you need more specific information 
                                 or have any questions about how it works, feel free to ask!
                            </p>
                        </div>
                        <p class="replay__txt">
                            Thank you for your kind words about Bakerganj Upazila. We are delighted to hear that you find our area beautiful. Our team works hard to maintain the na tural beauty and charm of our upazila, and it is always rewarding to receive positive feedback from visitors like yourself. We hope you had
                             a pleasant experience during your time here and we look forward to welcoming you back in the future. Thank you again for your gracious compliments.
                        </p>
                     </div>

                     <div class="chat__replay client">
                         <div class="chat__bdy">
                            <h6 class="title">{{ data.user.name }}</h6>
                            <p class="replay__desc">
                                It sounds like you're referring to a tool or service named "Salebot," which is used for marketing via WhatsApp.
                                 This type of system typically allows businesses to engage with customers, send promotional messages, automate 
                                 responses, and manage customer interactions through the WhatsApp platform. If you need more specific information 
                                 or have any questions about how it works, feel free to ask!
                            </p>
                        </div>
                        <p class="replay__txt">Thank you for your kind words about Bakerganj Upazila. We are delighted to hear that you find our area beautiful. Our team works hard to maintain the na
                            tural beauty and charm of our upazila, and it is always rewarding to receive positive feedback from visitors like yourself. We hope you had a pleasant experience during your time here and we look forward to welcoming you back in the future. Thank you again for your gracious compliments.
                        </p>
                     </div> -->
                    <!-- Chat Replay -->

                    <!--  -->

                    <div v-if="message.header_video">
                        <vue-plyr :options="data.options">
                            <video width="50" height="50" controls>
                                <source :src="message.header_video" type="video/mp4" />
                            </video>
                        </vue-plyr>
                    </div>
                    <div v-if="message.header_audio">
                        <audio controls>
                            <source :src="message.header_audio" type="audio/mp3" />
                            Your browser does not support the audio tag.
                        </audio>
                    </div>

                    <!-- <div class="header__txt" v-if="message.header_text">
                        {{ message.header_text }}
                    </div> -->
                    <a v-if="message.header_image" :href="message.header_image" target="_blank">
                        <img :src="message.header_image" alt="" />
                    </a>
                    <div v-if="message.header_document && message.header_document !== ''"
                        class="single-sp-card sp-document-card">
                        <div class="card-content mt--0">
                            <div class="document-part">
                                <div class="icon">
                                    <i class="las la-file-alt"></i>
                                </div>
                                <div class="info-part" v-if="message.file_info.name">
                                    <h6 class="title">{{ message.file_info.name }}</h6>
                                    <p v-if="message.file_info.size">{{ message.file_info.size }} KB, {{
                                        message.file_info.ext }} {{ getMixinValue.lang.file }}</p>
                                </div>
                                <div v-else>
                                    {{ getMixinValue.lang.document }}
                                </div>
                            </div>
                            <a :href="message.header_document" :download="message.header_document" class="card-btn">
                                {{ getMixinValue.lang.download }}
                            </a>
                        </div>
                    </div>
                    <div v-if="message.header_location"
                        class="single-sp-chat-area single-sp-card-box mt--23 position-relative">
                        <div class="chat-map-card">
                            <div class="map-area">
                                <div id="map">
                                    <a target="_blank" :href="message.header_location">
                                        <img :src="getMixinValue.assetUrl('images/default/map.webp')" alt="" />

                                    </a>
                                </div>
                            </div>
                            <div class="card-content">
                                <a target="_blank" :href="message.header_location"
                                    class="card-btn d-flex align-items-center gap-2 justify-content-center"
                                    style="font-size: 14px;">
                                    {{ getMixinValue.lang.live_location }}<i class="las la-map-marked-alt"
                                        style="font-size: 18px;"></i> Maps
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="position-relative">
                        <div v-if="message.value" class="chat-box" :class="{ 'bg-primary': !message.is_contact_msg }"
                            v-html="message.value"></div>
                    </div>
                </div>
                <div class="action-card">
                    <div class="dropdown">
                        <a class="dropdown-toggle" href="javascript:void(0);" role="button" data-bs-toggle="dropdown"
                            aria-expanded="true">
                            <i class="las la-ellipsis-v"></i>
                        </a>
                        <ul class="dropdown-menu" data-popper-placement="bottom-start">
                            <!-- <li>
                                <a class="dropdown-item" id="action__copy" href="javascript:void(0);" title="Copy"><i
                                        class="las la-copy"></i>Copy</a>
                            </li> -->
                            <li>
                                <a class="dropdown-item" id="action__replay" href="javascript:void(0);"
                                    title="replay"><i class="las la-reply"></i>{{ getMixinValue.lang.reply }}</a>
                            </li>
                            <li>
                                <a class="dropdown-item" id="action__forward" href="javascript:void(0);"
                                    title="forward"  @click="openForwardModal"><i class="las la-share"></i>{{ getMixinValue.lang.forward }}</a>
                            </li>
                            <li>
                                <a class="dropdown-item" id="action__remove" href="javascript:void(0);"
                                    title="Remove"><i class="las la-times"></i>{{ getMixinValue.lang.remove }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <span class="chat-time-text"
                    :class="{ 'ml--10': message.is_contact_msg, 'mr--10': !message.is_contact_msg }">
                    {{ message.sent_at }}
                </span>
                <span v-if="message.source === 'telegram' && message.is_contact_msg" class="author"
                    style="font-size: 10px;">
                    {{ message.contact_name }}
                </span>
                <div class="text-danger d-block text-italic text-end" v-if="message.error">
                    {{ message.error }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sendTemplateModal" tabindex="-1" aria-labelledby="sendTemplateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ getMixinValue.lang.templates }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- <div class="modal-search mb-3">
                    <div class="search-field">
                        <input type="text" placeholder="Search" @keyup="searchTemplate" v-model="data.template_search" />
                        <button class="sp-round-btn serach-btn" type="submit"><i class="las la-search"></i></button>
                    </div>
                </div> -->
                <div class="modal-body p-0">
                    <div class="row add-coupon">
                        <div class="col-lg-12" v-for="(template, index) in props.templates.data" :key="index">
                            <a :href="getMixinValue.getUrl('send-template?template_id=' + template.id + '&contact_id=' + props.chat_room_id)"
                                target="_blank">
                                <div class="mb-3 canned_response_div">
                                    <p class="m-0">
                                        {{ getMixinValue.lang.title }} : <strong>{{ template.name }}</strong>
                                    </p>
                                    <span>{{ getMixinValue.lang.category }} : {{ template.category }}</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-12 text-center mb-4" v-if="props.templates.next_page_url">
                            <loadingBtn v-if="props.templates.loading"></loadingBtn>
                            <a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary"
                                @click="loadTemplate(props.templates.next_page_url)">
                                <span>{{ getMixinValue.lang.load_more }}</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-primary">Load More</button> -->
                    <button type="button" class="btn btn-primary" @click="closeModalById('sendTemplateModal')">
                        {{ getMixinValue.lang.close }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <Transition>
            <Modal class="sp-modal" id="chatForward" :isOpen="messageForwardOpened" @modal-close="closeForwardModal"
                name="rewrite-modal">
                <template #header class="modal-title">
                    <div class="row w-100">
                        <div class="col-lg-6">
                            <p class="m-0 mt-3">Forward To</p>
                        </div>
                        <div class="col-lg-6 text-end">
                            <button @click="closeForwardModal" type="button" class="btn" style="font-size: 15px">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="modal-body">
                        <blockquote class="blockquote">
							
                        </blockquote>
                        <div class="forward__msg">
                    <div class="forward__bdy">
                        <div class="forward__txt">
                            Thank you for your kind words about Bakerganj Upazila. We are delighted to hear that you
                            find
                            our area beautiful. Our team works hard to maintain the
                            natural beauty and charm of our upazila, and it is always rewarding to receive positive
                            feedback
                            from visitors like yourself. We hope you had a pleasant experience during your time here and
                            we
                            look forward to welcoming you back in the future. Thank you again for your gracious
                            compliments.
                        </div>
                    </div>

                    <div class="forward__bdy">
                        <div class="forward__img">
                            <img data-v-cafdae46=""
                                src="https://2.salebot.app/public/client/flow_builder/1723301461.jpg" alt="">
                        </div>

                    </div>
                    <div class="forward__bdy">
                        <div class="forward__video">
                            <div>
                                <vue-plyr>
                                    <video data-v-cafdae46="" style="width: 100%;">
                                        <source data-v-cafdae46=""
                                            src="https://2.salebot.app/public/client/flow_builder/1723301492.mp4"
                                            type="video/mp4">
                                    </video>
                                </vue-plyr>
                            </div>
                        </div>
                    </div>
                    <div class="forward__bdy">
                        <div class="forward__audio">
                            <div>
                                <vue-plyr>
                                    <audio>
                                        <source data-v-cafdae46=""
                                            src="https://2.salebot.app/public/client/flow_builder/1723301475.mp3"
                                            type="audio/mp3">
                                    </audio>
                                </vue-plyr>
                            </div>
                        </div>
                    </div>
                    <div class="modal-search mt-2">
                        <div class="search-field">
                            <input type="text" style="padding-left: 15px;" placeholder="Type your message here..." />
                        </div>
                    </div>
                </div>
                <div class="modal-search mb-3">
                    <div class="search-field">
                        <input type="text" placeholder="Search" @keyup="searchTemplate"
                            v-model="data.template_search" />
                        <button class="sp-round-btn serach-btn" type="submit"><i class="las la-search"></i></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <ul class="author-card-list">
                        <li data-v-cafdae46="">
                            <a href="javascript:void(0)" class="single-sp-author-card active" data-v-cafdae46="">
                                <div class="author-image" data-v-cafdae46="">
                                    <img src="http://127.0.0.1:8000/images/default/user.jpg" alt="Arif Teletalk"
                                        data-v-cafdae46="">
                                </div>
                                <div class="content" data-v-cafdae46="">
                                    <h6 class="title text-ellips" data-v-cafdae46="">Arif Teletalk</h6>
                                    <p class="chat-sm-text text-ellips" data-v-cafdae46="">A video was sent</p>
                                </div>
                                <span class="time-badge" data-v-cafdae46="">14 hours ago</span>
                            </a>
                        </li>
                        <li data-v-cafdae46="">
                            <a href="javascript:void(0)" class="single-sp-author-card" data-v-cafdae46="">
                                <div class="author-image" data-v-cafdae46="">
                                    <img src="http://127.0.0.1:8000/images/default/user.jpg" alt="Arif Teletalk"
                                        data-v-cafdae46="">
                                </div>
                                <div class="content" data-v-cafdae46="">
                                    <h6 class="title text-ellips" data-v-cafdae46="">Arif Teletalk</h6>
                                    <p class="chat-sm-text text-ellips" data-v-cafdae46="">A video was sent</p>
                                </div>
                                <span class="time-badge" data-v-cafdae46="">14 hours ago</span>
                            </a>
                        </li>
                        <li data-v-cafdae46="">
                            <a href="javascript:void(0)" class="single-sp-author-card" data-v-cafdae46="">
                                <div class="author-image" data-v-cafdae46="">
                                    <img src="http://127.0.0.1:8000/images/default/user.jpg" alt="Arif Teletalk"
                                        data-v-cafdae46="">
                                </div>
                                <div class="content" data-v-cafdae46="">
                                    <h6 class="title text-ellips" data-v-cafdae46="">Arif Teletalk</h6>
                                    <p class="chat-sm-text text-ellips" data-v-cafdae46="">A video was sent</p>
                                </div>
                                <span class="time-badge" data-v-cafdae46="">14 hours ago</span>
                            </a>
                        </li>
                    </ul>
                </div>
                    </div>
                </template>
                <template #footer>
                    <div class="modal-footer mt-3">
                        <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
                        <button type="button" v-else class="btn btn-primary btn-lg" @click="rewriteAIReply">
                            {{ getMixinValue.lang.send }}
                        </button>
                    </div>
                </template>
            </Modal>
        </Transition>
    <!-- </div> -->
</template>

<script setup>
import globalValue from "../mixins/helper.js";
import loadingBtn from "../partials/loading_btn.vue";
import { Howl } from "howler";

const getMixinValue = globalValue();
import { onMounted, reactive, ref, watch } from "vue";
import Modal from "@/src/partials/modal.vue";

const props = defineProps(["chat_room_id", "staffs", "messageScroller", "messageSender", "templates", "template_search", "template_loading"]);
const emit = defineEmits(["closeAllModals", "loadTemplates", "update:template_search"]);
const searchTerm = ref(props.template_search);
const messageForwardOpened = ref(false);

watch(
    () => props.messageSender,
    () => {
        getMessages();
        // searchTemplate();
    }
);
watch(
    () => props.chat_room_id,
    () => {
        getMessages();
    }
);
watch(
    () => props.messageScroller,
    () => {
        loadMessages();
    }
);

onMounted(() => {
    if (props.chat_room_id) {
        getMessages();
    }
    listenForChanges();
});

let timeoutId = null;

const openForwardModal = async () => {
    messageForwardOpened.value = true;

    console.log(messageForwardOpened.value);
};

const closeForwardModal = () => {
    messageForwardOpened.value = false;
};

function listenForChanges() {
    if (!("Echo" in window)) {
        return;
    }

    Echo.channel("message-received-" + getMixinValue.authUser.id).listen("ReceiveUpcomingMessage", async (post) => {
        if (!("Notification" in window)) {
            alert("Web Notification is not supported");
            return;
        }
        // Initialize Howl with the correct file path
        const sound = new Howl({
            src: [`${window.location.origin}/public/mp3/alert.mp3`],
            volume: 1.0,
            onloaderror: function () {
                console.error("Failed to load sound file.");
            },
            onplayerror: function () {
                console.error("Failed to play sound file.");
            },
        });
        // Fetch new messages
        try {
            await messages();
            console.log("Messages fetched successfully.");
        } catch (error) {
            console.error("Error fetching messages:", error);
        }
        // Play sound for 1 second
        sound.seek(0); // Start from the beginning
        sound.play();
        // Stop the sound after 1 second
        setTimeout(() => {
            sound.stop();
            console.log("Sound stopped after 1 second.");
        }, 1000);
    });
}

// Function to open the modal by its ID
const openModalById = (modalId) => {
    const modal = document.getElementById(modalId);
    if (modal) {
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
};
const closeModalById = (modalId) => {
    const modal = document.getElementById(modalId);
    if (modal) {
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }
    }
};

const isModalOpened = ref(false);
const openModal = () => {
    isModalOpened.value = true;
};
const closeModal = () => {
    isModalOpened.value = false;
};
const data = reactive({
    messages: [],
    message_next_page_url: false,
    message_loading: false,
    can_not_reply: true,
    template_search: "",
    reply_message: "",
    reply_message_id: "",

    user: {
        id: "",
        name: "",
        phone: "",
        image: "",
        source: "",
        last_conversation_at: "",
        assignee_id: "",
    },
    options: {
        width: "50px",
        height: "50px",
    },
    audio_style_left: {
        display: "flex",
        "align-items": "flex-start",
    },
    audio_style_right: {
        display: "flex",
        "align-items": "flex-end",
    },
    video_style: {
        display: "flex",
        "justify-content": "end",
    },
});

async function blockConatct(contact_id) {
    let config = {
        params: {
            contact_id: contact_id,
        },
    };
    let url = getMixinValue.getUrl("contact/add-blacklist/" + contact_id);
    await axios.get(url, config).then((response) => {
        getMixinValue.config.loading = false;
        if (response.data.error) {
            return alert(response.data.error);
        } else if (response.data.status) {
            toastr.success(response.data.message);
            location.reload();
        } else {
            toastr.error(response.data.message);
        }
    });
}
async function clearChat(contact_id) {
    let config = {
        params: {
            contact_id: contact_id,
        },
    };
    let url = getMixinValue.getUrl("chat/clear/" + contact_id);

    try {
        let response = await axios.get(url, config);
        getMixinValue.config.loading = false;
        if (response.data.error) {
            alert(response.data.error);
        } else if (response.data.status) {
            toastr.success(response.data.message);
            data.messages = [];
        } else {
            toastr.error(response.data.message);
        }
    } catch (error) {
        getMixinValue.config.loading = false;
        toastr.error("An error occurred while clearing the chat.");
        console.error(error);
    }
}

async function getMessages() {
    getMixinValue.params_data.page = 1;
    await messages(false);
}

async function messages(load_more) {
    let config = {
        params: {
            page: getMixinValue.params_data.page,
        },
    };

    console.log(props.chat_room_id);

    let url = getMixinValue.getUrl("message/" + props.chat_room_id);
    await axios.get(url, config).then((response) => {
        getMixinValue.config.loading = false;
        if (response.data.error) {
            return alert(response.data.error);
        } else {
            data.can_not_reply = response.data.can_not_reply;
            let new_messages = response.data.messages.reverse();
            if (load_more) {
                data.messages = new_messages.concat(data.messages);
            } else {
                data.messages = new_messages;
            }
            data.message_next_page_url = response.data.next_page_url;
            if (!load_more) {
                setUser(response.data.user);
                scrollToBottom();
            }
        }
    });
}

// async function messages(load_more) {
// 	let config = {
// 		params: {
// 			page: getMixinValue.params_data.page,
// 		},
// 	};

// 	let url = getMixinValue.getUrl("message/" + props.chat_room_id);
// 	await axios.get(url, config).then((response) => {
// 		getMixinValue.config.loading = false;
// 		if (response.data.error) {
// 			return alert(response.data.error);
// 		} else {
// 			data.can_not_reply = response.data.can_not_reply;
// 			let new_messages = response.data.messages.reverse();
// 			if (load_more) {
// 				data.messages = new_messages.concat(data.messages);
// 			} else {
// 				data.messages = new_messages;
// 			}
// 			data.message_next_page_url = response.data.next_page_url;
// 			if (!load_more) {
// 				setUser(response.data.user);
// 				scrollToBottom();
// 			}
// 		}
// 	});
// }

async function loadMessages() {
    if (data.message_next_page_url) {
        getMixinValue.params_data.page++;
        getMixinValue.config.loading = true;
        await messages(true);
        setTimeout(() => {
            let chat_content_body = document.querySelector(".sp-main-wrapper-content");
            chat_content_body.scrollTop = 500;
        }, 100);
    }
}

function setUser(user) {
    getMixinValue.storeData.receiver_id = user.receiver_id;
    data.user.id = user.receiver_id;
    data.user.name = user.name;
    data.user.phone = user.phone;
    data.user.image = user.image;
    data.user.source = user.source;
    data.user.last_conversation_at = user.last_conversation_at;
    data.user.assignee_id = user.assignee_id;
}

const scrollToBottom = () => {
    setTimeout(() => {
        let chat_content_body = document.querySelector(".sp-main-wrapper-content");
        chat_content_body.scrollTop = chat_content_body.scrollHeight;
        chat_content_body.behavior = "smooth";
    }, 0);
};

async function assignStaff() {
    let url = getMixinValue.getUrl("assign-staff");
    let form = {
        staff_id: data.user.assignee_id,
        contact_id: data.user.id,
    };
    await axios.post(url, form).then((response) => {
        if (response.data.error) {
            return alert(response.data.error);
        }
    });
}

function loadTemplate() {
    emit("loadTemplates", {
        url: props.templates.next_page_url,
        template_search: searchTerm.value,
    });
}

async function searchTemplate() {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(() => {
        emit("update:template_search", searchTerm.value);
        loadTemplates();
    }, 500);
}

async function loadTemplates() {
    getMixinValue.params_data.chat_room_page = 1;
    getMixinValue.config.loading = true;
    await loadTemplate(false);
}
</script>

<style scoped>
.modal {
    z-index: 1050;
    /* Adjust as needed */
}

.modal-backdrop {
    z-index: 999;
    /* Ensure backdrop is behind the modal */
}

.modal-backdrop.fade {
    opacity: 0 !important;
}
</style>
