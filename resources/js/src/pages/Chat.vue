<template>
	<main class="main-wrapper chat_wrapper">
		<div class="chatpage-wrapper">
			<div class="container-fluid">
				<div class="row row--0">
					<!-- Left Sidebar -->
					<div class="sp-left-sidebar popup-dashboardleft-section" style="margin-left: 80px">
						<div class="inner-wrapper position-relative">
							<left_sidebar @fetch-user-messages="fetchMessages" :staffs="data.staffs" :tags="data.tags"></left_sidebar>
						</div>
						<span class="sidebar-toggler chat-customtoggle d-none">
							<span class="icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M16 6H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
									<path d="M21 12H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
									<path d="M18 18H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
								</svg>
							</span>
						</span>
					</div>
					
					<!-- Chat Main -->
					<div>
						<div class="sp-chat-main-wrapper" ref="chatBody" v-if="data.chat_room_id">
							<chat_messages
								@load-templates="loadMoreTemplate"
								@load-webTemplates="loadMoreWebTemplate"
								:templates="data.templates"
								:webTemplates="data.webTemplates"
								:chat_room_id="data.chat_room_id"
								:staffs="data.staffs"
								:messageScroller="data.message_scroller"
								:messageSender="data.message_sender"
							></chat_messages>
							<msg_sender @send-messages="sendMessages" :chat_room_id="data.chat_room_id"></msg_sender>
						</div>
						<div class="sp-chat-main-wrapper" v-else>
							<span class="chat-customtoggle chat-customtoggle-phone d-lg-none">
								<span class="icon">
									<svg width="100%" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M16 6H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										</path>
										<path d="M21 12H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										</path>
										<path d="M18 18H3" stroke="#7E7F92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										</path>
									</svg>
								</span>
							</span>
							<h5 style="display: flex; text-align: center; justify-content: center; height: 100vh; margin-top: 20px;">No chat rooms available.</h5>
						</div>
					</div>
					<!-- Right Sidebar -->
					<right_sidebar v-if="data.chat_room_id" :chat_room_id="data.chat_room_id"></right_sidebar>
				</div>
			</div>
		</div>
	</main>
</template>
<script setup>
import { ref, onMounted, reactive } from "vue";
import globalValue from "../mixins/helper.js";
import left_sidebar from "../partials/left_sidebar.vue";
import right_sidebar from "../partials/right_sidebar.vue";
import msg_sender from "../partials/message_sender.vue";
import chat_messages from "../partials/messages.vue";
import { useChatRoomStore } from '../stores/chatRoomStore'; // Adjust the path as needed
const chatRoomStore = useChatRoomStore();

const emit = defineEmits(["closeAllModals", "loadNewMessages"]);
const getMixinValue = globalValue();
const chatBody = ref(null);
onMounted(() => {
	getStaffs();
	fetchClientTags();
	templates();
	webTemplates();
	var chatContentBody = chatBody.value;
	if (chatContentBody) {
		// Add scroll event listener only if the element exists
		chatContentBody.addEventListener("scroll", function () {
			if (this.scrollTop == 0) {
				data.message_scroller++;
			}
		});
	}
});
async function fetchClientTags() {
  await chatRoomStore.fetchClientTags();
}
const data = reactive({
	canned_responses: [],
	templates: {
		data: [],
		loading: false,
		next_page_url: false,
	},
	webTemplates: {
		data: [],
		loading: false,
		next_page_url: false,
	},
	staffs: [],
	tags: [],
	chat_room_id: "",
	message_scroller: 0,
	message_sender: 0,
});
async function getStaffs() {
	if (data.staffs.length > 0) {
		return;
	}
	let url = getMixinValue.getUrl("staffs-by-client");
	await axios.get(url).then((response) => {
		getMixinValue.config.loading = false;
		if (response.data.error) {
			toastr.error(response.data.error);
			return ;
		} else {
			data.staffs = response.data.staffs;
		}
	});
}

async function templates(load_more) {
	data.templates.loading = true;
	let url = getMixinValue.getUrl("whatsapp-templates");
	if (load_more) {
		url = load_more;
	}
	await axios.get(url).then((response) => {
		data.templates.loading = false;
		if (response.data.success) {
			data.templates.data = load_more ? data.templates.data.concat(response.data.templates) : response.data.templates;
			data.templates.next_page_url = response.data.next_page_url;
		}
	});
}
async function webTemplates(load_more) {
	data.webTemplates.loading = true;
	let url = getMixinValue.getUrl("web/whatsapp-templates");
	if (load_more) {
		url = load_more;
	}
	await axios.get(url).then((response) => {
		data.webTemplates.loading = false;
	
		if (response.data.success) {
			data.webTemplates.data = load_more ? data.webTemplates.data.concat(response.data.webTemplates) : response.data.webTemplates;
			// console.log('response web template>>>>>>>>', data.webTemplates.data);
			data.webTemplates.next_page_url = response.data.next_page_url;
			// console.log('response web page url>>>>>>>>', data.webTemplates.data);
		}
	});
}
function fetchMessages(args) {
	data.chat_room_id = args.chat_room_id;
}
function sendMessages(args) {
	data.message_sender += args.message_sender;
}
function loadMoreTemplate(args) {
	templates(args.url);
}
function loadMoreWebTemplate(args) {
	webTemplates(args.url);
}
</script>

