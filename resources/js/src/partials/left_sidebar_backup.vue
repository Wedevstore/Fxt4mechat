<template>
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
				<div class="select-boxlist d-flex">
					<select class="form-select js-example-basic-single dropdown-select-item" aria-label="Default select example" v-model="data.chatroom_source" @change="getChatRooms()">
						<option value="">{{ getMixinValue.lang.all_channel }}</option>
						<option value="whatsapp">{{ getMixinValue.lang.whatsapp }}</option>
						<option value="telegram">{{ getMixinValue.lang.telegram }}</option>
						<option value="messenger">{{ getMixinValue.lang.messenger }}</option>
					</select>
					<select class="form-select js-example-basic-single dropdown-select-item" aria-label="Default select example" v-model="data.chat_room_tag" @change="getChatRooms()">
						<option value="">{{ getMixinValue.lang.all_tags }}</option>
						<option v-for="(tag, index) in props.tags" :key="index" :value="tag.id">{{ tag.title }}</option>
					</select>
					<select class="form-select js-example-basic-single dropdown-select-item" aria-label="Default select example" @change="getChatRooms()" v-model="data.chat_room_assignee_id">
						<option value="">{{ getMixinValue.lang.all_agent }}</option>
						<option v-for="(staff, index) in props.staffs" :key="index" :value="staff.id">{{ staff.name }}</option>
					</select>
				</div>
				<div class="search-field">
					<input type="text" :placeholder="getMixinValue.lang.search" @keyup="searchRooms" v-model="data.chat_room_search" />
					<button class="sp-round-btn serach-btn" type="submit"><i class="las la-search"></i></button>
				</div>
				<ul class="author-card-list">
					<!--                      <li v-for="(user, index) in data.chat_rooms" @click="chatRoom(user)">
									<a href="javascript:void(0)" class="single-sp-author-card">
									  <div class="author-image has-active-status">
										<img :src="user.image" alt="Author Image">
									  </div>
									  <div class="content">
										<h6 class="title">{{ user.name }}</h6>
										<p><i class="las la-edit"></i>Marina is typing...</p>
										<span class="unread-badge">2</span>
									  </div>
									</a>
								  </li>-->
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
				<div class="col-md-12 text-center" v-if="data.chat_room_next_page_url">
					<loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
					<a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary" @click="loadChatRooms">
						<span>{{ getMixinValue.lang.load_more }}</span>
					</a>
				</div>   
			</div>
			<div class="tab-pane fade" id="nav-contactlist" role="tabpanel" aria-labelledby="nav-contactlist-tab">     
				<div class="select-boxlist d-flex">
					<select class="form-select dropdown-select-item" aria-label="Default select example" v-model="data.contact_source" @change="getContacts()">
						<option value="">{{ getMixinValue.lang.all_channel }}</option>
						<option value="whatsapp">{{ getMixinValue.lang.whatsapp }}</option>
						<option value="telegram">{{ getMixinValue.lang.telegram }}</option>
						<option value="messenger">{{ getMixinValue.lang.messenger }}</option>
					</select>
					<select class="form-select dropdown-select-item" aria-label="Default select example" @change="getContacts" v-model="data.contact_tag">
						<option value="">{{ getMixinValue.lang.all_tags }}</option>
						<option v-for="(tag, index) in props.tags" :key="index" :value="tag.id">{{ tag.title }}</option>
					</select>
					<select class="form-select dropdown-select-item" aria-label="Default select example" @change="getContacts()" v-model="data.contact_assignee_id">
						<option value="">{{ getMixinValue.lang.all_agent }}</option>
						<option v-for="(staff, index) in props.staffs" :key="index" :value="staff.id">{{ staff.name }}</option>
					</select>
				</div>
				<div class="search-field">
					<input type="text" :placeholder="getMixinValue.lang.search" @keyup="searchContacts" v-model="data.contact_search" />
					<button class="sp-round-btn serach-btn" type="submit"><i class="las la-search"></i></button>
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
				<div class="col-md-12 text-center" v-if="data.contact_next_page_url">
					<loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
					<a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary" @click="loadContacts">
						<span>{{ getMixinValue.lang.load_more }}</span>
					</a>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { onMounted, reactive } from "vue";
const emit = defineEmits(["fetchUserMessages"]);
const props = defineProps(["staffs", "tags"]);

onMounted(() => {
	getChatRooms();
	listenForChanges();
	let query_params = new URLSearchParams(window.location.search);
	let contact_id = query_params.get("contact");
	if (contact_id) {
		let contact = document.getElementById("contact").value;
		chatRoom(JSON.parse(contact));
	}
});
function listenForChanges() {
	if (!("Echo" in window)) {
		return;
	}
	Echo.channel("message-received-" + getMixinValue.authUser.id).listen("ReceiveUpcomingMessage", async (post) => {
		if (!("Notification" in window)) {
			alert("Web Notification is not supported");
			return;
		}
		await chatRooms();
	});
}
const data = reactive({
	selected_chat_room_id: "",
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
});
let timeoutId = null;

import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();
import loadingBtn from "../partials/loading_btn.vue";
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

	console.log(data.chatroom_source);

	let url = getMixinValue.getUrl("chat-rooms");
	await axios.get(url, config).then((response) => {
		getMixinValue.config.loading = false;
		if (response.data.error) {
			return alert(response.data.error);
		} else {
			let chat_rooms = response.data;
			if (load_more) {
				data.chat_rooms = data.chat_rooms.concat(chat_rooms.chat_rooms);
			} else {
				data.chat_rooms = chat_rooms.chat_rooms;
				// Emit the first chat room ID if chat_rooms is not empty
				if (data.chat_rooms.length > 0) {
					emit("fetchUserMessages", { chat_room_id: data.chat_rooms[0].id });
				}
			}
			data.chat_room_next_page_url = chat_rooms.next_page_url;
		}
	});
}

async function chatRoom(user, index) {
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
	// Set total_unread_messages to 0 for the clicked chat_room
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
			return alert(response.data.error);
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
</script>