<template>
	<div>
		<div class="left-sidebar-header sticky-top sticky-offset overflow-auto">
			<nav class="chat-tab-one">
				<div class="nav nav-tabs border-0" id="nav-chat-tab" role="tablist">
					<button
						@click="getChatRooms()"
						class="nav-link active"
						id="nav-chatlist-tab"
						data-bs-toggle="tab"
						data-bs-target="#nav-chatlist"
						type="button"
						role="tab"
						aria-controls="nav-chatlist"
						aria-selected="true"
					>
						{{ getMixinValue.lang.conversations }}
					</button>
					<button
						class="nav-link"
						id="nav-contactlist-tab"
						data-bs-toggle="tab"
						@click="getContacts"
						data-bs-target="#nav-contactlist"
						type="button"
						role="tab"
						aria-controls="nav-contactlist"
						aria-selected="false"
					>
						{{ getMixinValue.lang.contacts }}
					</button>
					<span class="close__flyout">
						<i class="las la-times"></i>
					</span>
				</div>
			</nav>
		</div>
		<div class="left-sidebar-content">
			<div class="tab-content" id="nav-chat-tabContent">
				<div class="tab-pane fade active show" id="nav-chatlist" role="tabpanel" aria-labelledby="nav-chatlist-tab">
					<div class="sticky__item">
						<div class="select-boxlist d-flex">
							<select
							  class="form-select js-example-basic-single dropdown-select-item"
							  aria-label="Default select example"
							  v-model="selectedDevice"
							  @change="activateDevice"
							  v-if="isWebChat == true"
							>
							  <option value="">{{ getMixinValue.lang.all_devices }}</option>
							  <option
								v-for="device in data.devices"
								:key="device.id"
								:value="device.id"
							  >
								{{ device.name }}
								<!-- <span v-if="device.active_for_chat === 1"> (Selected)</span> -->
							  </option>
							</select>

							<select class="form-select js-example-basic-single dropdown-select-item" aria-label="Default select example" v-model="data.chatroom_source" @change="getChatRooms()" v-if="isWebChat == false">
								<option value="">{{ getMixinValue.lang.all_channel }}</option>
								<option value="whatsapp">{{ getMixinValue.lang.whatsapp }}</option>
								<option value="telegram">{{ getMixinValue.lang.telegram }}</option>
								<option value="messenger">{{ getMixinValue.lang.messenger }}</option>
								<option value="instagram">{{ getMixinValue.lang.instagram }}</option>
							</select>
							<select class="form-select js-example-basic-single dropdown-select-item" aria-label="Default select example" v-model="data.chat_room_tag" @change="getChatRooms()" v-if="isWebChat == false">
								<option value="">{{ getMixinValue.lang.all_tags }}</option>
								<option v-for="(tag, index) in chatRoomStore.client_tags" :key="index" :value="tag.id">{{ tag.title }}</option>
							</select>
							<select class="form-select js-example-basic-single dropdown-select-item" aria-label="Default select example" @change="getChatRooms()" v-model="data.chat_room_assignee_id" v-if="isWebChat == false">
								<option value="">{{ getMixinValue.lang.all_agent }}</option>
								<option v-for="(staff, index) in props.staffs" :key="index" :value="staff.id">{{ staff.name }}</option>
							</select>
						</div>
						<div class="search-field">
							<input type="text" :placeholder="getMixinValue.lang.search" @keyup="searchRooms" v-model="data.chat_room_search" />
							<button class="sp-round-btn serach-btn" type="submit"><i class="las la-search"></i></button>
						</div>
					</div>
					<ul class="author-card-list">
						<li v-for="(user, index) in data.chat_rooms" :key="index" @click="chatRoom(user, index)">
							<a href="javascript:void(0)" class="single-sp-author-card" :class="data.selected_chat_room_id == user.id ? 'active' : ''">
								<div class="author-image">
									<img :src="user.image" :alt="user.name" />
								</div>
								<div class="content">
									<h6 class="title text-ellips">{{ user.name }}</h6>
									<p class="chat-sm-text text-ellips" v-if="user.has_msg">{{ user.message.title }}</p>
								</div>
								<span v-if="user.has_msg" class="time-badge">{{ user.message.created_at }}</span>
								<span v-if="user.total_unread_messages > 0 && data.selected_chat_room_id != user.id" class="unread-badge">{{ user.total_unread_messages }}</span>
							</a>
						</li>
					</ul>
					<div class="col-md-12 text-center btm__btn" v-if="data.chat_room_next_page_url">
						<loadingBtn v-if="getMixinValue.config.loading" class="w-100"></loadingBtn>
						<a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary w-100" @click="loadChatRooms">
							<span>{{ getMixinValue.lang.load_more }}</span>
						</a>
					</div>
				</div>
				<div class="tab-pane fade" id="nav-contactlist" role="tabpanel" aria-labelledby="nav-contactlist-tab">
					<div class="sticky__item">
						<div class="select-boxlist d-flex">
							<select
							  class="form-select js-example-basic-single dropdown-select-item"
							  aria-label="Default select example"
							  v-model="selectedDevice"
							  @change="activateDevice"
							  v-if="isWebChat == true"
							>
							  <option value="">{{ getMixinValue.lang.all_devices }}</option>
							  <option
								v-for="device in data.devices"
								:key="device.id"
								:value="device.id"
							  >
								{{ device.name }}
								<!-- <span v-if="device.active_for_chat === 1"> (Active)</span> -->
							  </option>
							</select>
							<select class="form-select dropdown-select-item" aria-label="Default select example" v-model="data.contact_source" @change="getContacts()" v-if="isWebChat == false">
								<option value="">{{ getMixinValue.lang.all_channel }}</option>
								<option value="whatsapp">{{ getMixinValue.lang.whatsapp }}</option>
								<option value="telegram">{{ getMixinValue.lang.telegram }}</option>
								<option value="messenger">{{ getMixinValue.lang.messenger }}</option>
								<option value="instagram">{{ getMixinValue.lang.instagram }}</option>
							</select>
							<select class="form-select dropdown-select-item" aria-label="Default select example" @change="getContacts" v-model="data.contact_tag" v-if="isWebChat == false">
								<option value="">{{ getMixinValue.lang.all_tags }}</option>
								<option v-for="(tag, index) in chatRoomStore.client_tags" :key="index" :value="tag.id">{{ tag.title }}</option>
							</select>
							<select class="form-select dropdown-select-item" aria-label="Default select example" @change="getContacts()" v-model="data.contact_assignee_id" v-if="isWebChat == false">
								<option value="">{{ getMixinValue.lang.all_agent }}</option>
								<option v-for="(staff, index) in props.staffs" :key="index" :value="staff.id">{{ staff.name }}</option>
							</select>
						</div>
						<div class="search-field">
							<input type="text" :placeholder="getMixinValue.lang.search" @keyup="searchContacts" v-model="data.contact_search" />
							<button class="sp-round-btn serach-btn" type="submit"><i class="las la-search"></i></button>
						</div>
					</div>
					<ul class="author-card-list">
						<li v-for="(contact, index) in data.contacts" :key="index" @click="chatRoom(contact)">
							<a href="javascript:void(0)" class="single-sp-author-card" :class="data.selected_chat_room_id == contact.id ? 'active' : ''">
								<div class="author-image">
									<img :src="contact.image" :alt="contact.name" />
								</div>
								<div class="content">
									<h6 class="title">{{ contact.name }}</h6>
									<p>
										<i v-if="contact.source == 'telegram'" class="lab la-telegram"></i>
										<i v-else class="lab la-whatsapp"></i>
										<span v-if="contact.source == 'telegram'"> {{ contact.group_chat_id }}</span>
										<span v-else>{{ contact.phone }}  </span>

									</p>
								</div>
							</a>
						</li>
					</ul>
					<div class="col-md-12 text-center btm__btn" v-if="data.contact_next_page_url">
						<loadingBtn v-if="getMixinValue.config.loading" class="w-100"></loadingBtn>
						<a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary w-100" @click="loadContacts">
							<span>{{ getMixinValue.lang.load_more }}</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { onMounted, reactive, watch, ref  } from "vue";
const emit = defineEmits(["fetchUserMessages"]);
const props = defineProps(["staffs", "tags"]);
import { useReplyStore } from '../stores/replyStore';
const replyStore = useReplyStore();
import { useForwardStore } from '../stores/forwardStore';
const forwardStore = useForwardStore();
import { useChatRoomStore } from "../stores/chatRoomStore"; // Adjust the path as needed
const chatRoomStore = useChatRoomStore();
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();
import loadingBtn from "../partials/loading_btn.vue";
let timeoutId = null;

function listenForChanges() {
	if (!("Echo" in window)) {
		return;
	}
	Echo.channel("message-received-" + getMixinValue.authUser.id).listen("ReceiveUpcomingMessage", async (post) => {
		if (!("Notification" in window)) {
			toastr.error("Web Notification is not supported");
			// alert("Web Notification is not supported");
			return;
		}
		await chatRooms();
	});
}
const data = reactive({
	selected_chat_room_id: chatRoomStore.selectedChatRoomId,
	chat_rooms: [],
	chat_room_next_page_url: true,
	contacts: [],
	contact_next_page_url: true,
	chatroom_source: "",
	contact_source: "",
	chat_room_assignee_id: "",
	contact_assignee_id: "",
	chat_room_tag: "",
	contact_tag: "",
	contact_search: "",
	chat_room_search: "",
	current_user: {
		id: "",
		name: "",
		phone: "",
		image: "",
		source: "",
		last_conversation_at: "",
	},
	message_scroller: 0,
	devices: "",
});
const selectedDevice = ref("");
const message = ref('');
const isWebChat = ref(false);

onMounted(async () => {
  await getChatRooms();
  listenForChanges();

  loadDevices();

  isWebChat.value = window.location.href.includes('/client/web-chat');

  if (isWebChat.value) {
	data.chatroom_source = 'whatsapp'
	getChatRooms()
  }

  let query_params = new URLSearchParams(window.location.search);
  let contact_id = query_params.get("contact");

  if (contact_id) {
    let contactElement = document.getElementById("contact");
    if (contactElement) {
      try {
        const parsedContact = JSON.parse(contactElement.value);
        chatRoom(parsedContact);
        chatRoomStore.setSelectedChatRoomId(parsedContact.id);
      } catch (error) {
        console.error("Failed to parse contact:", error);
      }
    } else {
      console.error("Contact element not found.");
    }
  } else {
    if (data.chat_rooms && data.chat_rooms.length > 0) {
    //   chatRoom(data.chat_rooms[0]);
    //   chatRoomStore.setSelectedChatRoomId(data.chat_rooms[0].id);
    } else {
      console.error("No chat rooms available.");
    }
  }
});
// Watch the store's selectedChatRoomId and update local state
watch(() => chatRoomStore.selectedChatRoomId, (newId) => {
  if (newId !== data.selected_chat_room_id) {
    data.selected_chat_room_id = newId;
    const selectedChatRoom = data.chat_rooms.find(room => room.id === newId);
    if (selectedChatRoom) {
      chatRoom(selectedChatRoom);
    }
  }
});
async function getChatRooms() {
	getMixinValue.params_data.chat_room_page = 1;
	getMixinValue.config.loading = true;
	await chatRooms(false);
}
async function searchRooms() {
	if (timeoutId) {
		clearTimeout(timeoutId);
	}

	timeoutId = setTimeout(() => {
		getChatRooms();
	}, 500);
}
async function searchContacts() {
	if (timeoutId) {
		clearTimeout(timeoutId);
	}

	timeoutId = setTimeout(() => {
		getContacts();
	}, 1000);
}

async function loadChatRooms() {
	getMixinValue.params_data.chat_room_page = getMixinValue.params_data.chat_room_page + 1;
	getMixinValue.config.loading = true;
	await chatRooms(true);
}
async function chatRooms(load_more) {
	let config = {
		params: {
			q: data.chat_room_search,
			type: data.chatroom_source,
			page: getMixinValue.params_data.chat_room_page,
			assignee_id: data.chat_room_assignee_id,
			tag_id: data.chat_room_tag,
		},
	};
	// console.log(data.chat_room_tag);
	let url = getMixinValue.getUrl("chat-rooms");
	
	await axios.get(url, config).then((response) => {
		getMixinValue.config.loading = false;
		if (response.data.error) {
			toastr.error(response.data.error);
		} else {
			let chat_rooms = response.data;
			if (load_more) {
				data.chat_rooms = data.chat_rooms.concat(chat_rooms.chat_rooms);
			} else {
				data.chat_rooms = chat_rooms.chat_rooms;
			}
			data.chat_room_next_page_url = chat_rooms.next_page_url;
		}
	});
}

async function chatRoom(user, index) {
	forwardStore.removeMessageFromForward();
	replyStore.clearReplyMessage();
	// chatRoomStore.setSelectedChatRoomId(user.id);

	if (data.current_user.id === user.id) {
		return;
	}
	if (!user.id) {
		return;
	}
	data.selected_chat_room_id = user.id;
	data.message_next_page_url = true;
	getMixinValue.params_data.page = 1;
	emit("fetchUserMessages", { chat_room_id: data.selected_chat_room_id });
	chatRoomStore.setSelectedChatRoomId(user.id);
	await chatRoomStore.fetchContactTags();
	data.chat_rooms.forEach((chatRoom) => {
		if (chatRoom.id === user.id) {
			chatRoom.total_unread_messages = 0;
		}
	});
}


async function getContacts() {
	getMixinValue.config.loading = true;
	getMixinValue.params_data.contact_page = 1;
	await contacts();
}

async function loadContacts() {
	getMixinValue.params_data.contact_page = getMixinValue.params_data.contact_page + 1;
	getMixinValue.config.loading = true;
	await contacts(true);
}
async function contacts(load_more) {
	let config = {
		params: {
			q: data.contact_search,
			type: data.contact_source,
			page: getMixinValue.params_data.contact_page,
			assignee_id: data.contact_assignee_id,
			tag_id: data.contact_tag,
		},
	};

	let url = getMixinValue.getUrl("contacts-by-client");
	await axios.get(url, config).then((response) => {
		getMixinValue.config.loading = false;
		if (response.data.error) {
			toastr.error(response.data.error);
			// return alert(response.data.error);
		} else {
			let users = response.data;
			if (load_more) {
				data.contacts = data.contacts.concat(users.contacts);
			} else {
				data.contacts = users.contacts;
			}
			data.contact_next_page_url = users.next_page_url;
		}
	});
}

// async function loadDevices() {
// 	try {
// 		// If getMixinValue.getUrl() returns an axios promise, you can await it directly
// 		const response = await axios.get(getMixinValue.getUrl("all/devices"));

// 		if (response.data?.success) {
// 			data.devices = response.data.data;
// 			console.log("devices loaded >>>", data.devices);
// 		} else {
// 			console.warn("No devices found or invalid response:", response.data);
// 		}
// 	} catch (error) {
// 		console.error("Error loading devices:", error);
// 	}
// }
async function loadDevices() {
	try {
		const response = await axios.get(getMixinValue.getUrl("all/devices"));
		if (response.data?.success) {
			let devices = response.data.data;
			if (devices.length > 0) {
				devices.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
				devices = devices.map((device, index) => {
					device.active_for_chat = index === 0 ? 1 : 0;
					return device;
				});
				data.devices = devices;
				// Optional: set selectedDevice to the active one
				selectedDevice.value = devices[0].id;
				// console.log("devices loaded >>>", data.devices);
			}
		} else {
			console.warn("No devices found or invalid response:", response.data);
		}
	} catch (error) {
		console.error("Error loading devices:", error);
	}
}


const activateDevice = async () => {
	if (!selectedDevice.value) return
	try {
	const response = await axios.post(getMixinValue.getUrl(`device/active/${selectedDevice.value}`));
		message.value = response.data.message
		toastr.success(message.value);
	} catch (error) {
		message.value = 'Failed to activate device'
	}
}

</script>