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
			<button v-if="(data.user.source == 'whatsapp' || data.user.source == 'messenger') && isWebChat == false" type="button" class="documentory-modal-btn btn"
				@click="openModalById('sendTemplateModal')">
				<i class="las la-file-alt"></i>
			</button>

			<!-- web template -->
			<button v-if="(data.user.source == 'whatsapp' || data.user.source == 'messenger') && isWebChat == true" type="button" class="documentory-modal-btn btn"
				@click="openWebModalById('sendWebTemplateModal')">
				<i class="las la-file-alt"></i>
			</button>

			<Transition v-if="data.user.source == 'whatsapp' || data.user.source == 'messenger'">
				<Modal class="sp-modal" :isOpen="isModalOpened" @modal-close="closeModal" name="note-modal">
					<template #header class="modal-title">
						<div class="row w-100">
							<div class="col-lg-6">
								<p class="m-0 mt-3">{{ getMixinValue.lang.templates }}</p>
							</div>
							<div class="col-lg-6 text-end">
								<button @click="closeModal" type="button" class="btn" style="font-size: 15px">
									<i class="las la-times"></i>
								</button>
							</div>
						</div>
					</template>
					<template #content>
						<div class="modal-body">
							<div class="row add-coupon">

								<div class="col-lg-12" v-for="(template, index) in props.templates.data" :key="index">
									<a :href="getMixinValue.getUrl('send-template?template_id=' + template.id + '&contact_id=' + props.chat_room_id)" target="_blank">
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
							<a class="dropdown-item" href="javascript:void(0);" @click="blockContact(data.user.id)"
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
			<div
			v-for="(message, msgIndex) in day.messages"
			:key="msgIndex"		
			:class="message.class"
			:style="message.is_contact_msg ? data.audio_style_left : data.audio_style_right"
			>
				<div v-if="message.context && message.context.type" class="chat__replay client">
					<a href="javascript:void(0);" :class="message.context.id" @click="scrollToMessage(message.context.id)">
					<div class="chat__bdy">
						<!-- {{ message.context }} -->
						<div v-if="message.context.type==='image'">
							<img :src="message.context.message">
						</div>
						<div v-else-if="message.context.type==='audio'">
							<i class="las la-file-audio"></i>						
						</div>
						<div v-else-if="message.context.type==='video'">
							<i class="las la-file-video"></i>
						</div>	
						<div v-else-if="message.context.type==='document'">
							<i class="las la-file-pdf"></i>
						</div>
						<div v-else-if="message.context.type==='location'">
							<i class="las la-map-marked"></i>
						</div> 
						<div v-else-if="message.context.type==='document'">
							<i class="las la-file"></i>
						</div>
						<p v-else class="replay__desc">
							{{ message.context.message }}
						</p>
					
					</div>
				</a>
				</div>
				<!-- Campaign Message -->
				<div :ref="(el) => setMessageRef(message.id, el)" :id="message.id" v-if="message.is_campaign_msg" class="single-sp-card">
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
						{{ message.header_audio }}
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
				<div :ref="(el) => setMessageRef(message.id, el)" :id="message.id" v-else-if="message.message_type == 'interactive_button' || message.message_type == 'interactive_list'"
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
							<vue-plyr>
								<audio>
									<source :src="message.header_audio" type="audio/mp3" />
								</audio>
							</vue-plyr>
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
				<div :ref="(el) => setMessageRef(message.id, el)" :id="message.id" v-else class="chat-message-width">
					<div v-if="message.contacts.length > 0" class="single-sp-card sp-info-card">
						<div class="card-content text-center">
							<h6 class="title">{{ message.contacts[0].name.formatted_name }}</h6>
							<p class="info"><i class="lab la-whatsapp"></i>{{ message.contacts[0].phones[0].phone }}</p>
							<a :href="'https://wa.me/' + message.contacts[0].phones[0].wa_id" target="_blank"
								class="card-btn">{{ getMixinValue.lang.chat }}</a>
						</div>
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
					<div class="header__txt" v-if="message.header_text">
						{{ message.header_text }}
					</div>
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
									<p v-if="message.file_info.size">
										{{ message.file_info.size }} KB, 
										{{ message.file_info.ext }} 
										{{ getMixinValue.lang.file }}
									</p>
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
									{{ getMixinValue.lang.live_location }}
									<i class="las la-map-marked-alt" style="font-size: 18px;"></i> 
										Maps
								</a>
							</div>
						</div>
					</div>
					<div class="chat-box" v-for="(button, index) in message.buttons" :key="index">
							{{ button.text }}
					</div> 
					<div class="position-relative">
						<div v-if="message.value" class="chat-box" :class="{ 'bg-primary': !message.is_contact_msg }"
							v-html="message.value"></div>
					</div>
				</div>
				<!-- <div class="action-card"  v-if="message.source === 'whatsapp' && message.is_contact_msg"> -->
				<div class="action-card"  v-if="message.source === 'whatsapp'">
					<div class="dropdown">
						<a class="dropdown-toggle" href="javascript:void(0);" role="button" data-bs-toggle="dropdown"
							aria-expanded="true">
							<i class="las la-ellipsis-v"></i>
						</a>
						<ul class="dropdown-menu" data-popper-placement="bottom-start">
							<li v-if="message.value">
								<a class="dropdown-item" @click="copyMessageToClipboard(message.value)" href="javascript:void(0);" 
									:title="getMixinValue.lang.copy">
									<i class="las la-copy"></i> 
									{{getMixinValue.lang.copy }}
								</a>
							</li> 
							<li v-if="message.is_contact_msg">
								<a class="dropdown-item" @click="setReplyMessage(msgIndex, message)"
									href="javascript:void(0);" :title="getMixinValue.lang.replay">
									<i class="las la-reply"></i>
									{{getMixinValue.lang.reply }}
								</a>
							</li>
							<li v-if="shouldShowForwardOption(message)">
								<a class="dropdown-item" href="javascript:void(0);" :title="getMixinValue.lang.forward" @click="openForwardModal(msgIndex, message)">
									<i class="las la-share"></i>
									{{ getMixinValue.lang.forward }}
								</a>
							</li>
							<li>
								<a class="dropdown-item" @click="deleteMessage(msgIndex, message)"
									href="javascript:void(0);" :title="getMixinValue.lang.remove">
									<i class="las la-times"></i>
									{{ getMixinValue.lang.remove }}
								</a>
							</li>
						</ul>
					</div>
				</div>
				<!-- <span class="chat-time-text"
					:class="{ 'ml--10': message.is_contact_msg, 'mr--10': !message.is_contact_msg }">
					{{ message.sent_at }} 
				</span> -->
				<span
					class="chat-time-text"
					:class="{ 'ml--10': message.is_contact_msg, 'mr--10': !message.is_contact_msg }"
					style="display: inline-flex; align-items: center;"
				>
					{{ message.sent_at }} 

					<!-- Message Status -->
					<span style="margin-left: 4px;">
						<!-- Seen -->
						<i
							v-if="message.is_seen"
							class="la la-check-double"
							style="font-size: 13px; color: #25D366;"
						></i>

						<!-- Not Seen -->
						<i
							v-else
							class="la la-check"
							style="font-size: 13px; color: #9e9e9e;"
						></i>
					</span>
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
				<div class="modal-body p-0">
					<div class="row add-coupon">
							<div class="col-lg-12" v-for="(template, index) in props.templates.data" :key="index">
								<span v-if="template.type == 'whatsapp' && data.user.source == 'whatsapp'">
									<a :href="getMixinValue.getUrl('send-template?template_id=' + template.id + '&contact_id=' + props.chat_room_id)"
										target="_blank">
										<div class="mb-3 canned_response_div">
											<p class="m-0">
												{{ getMixinValue.lang.title }} : <strong>{{ template.name }}</strong>
											</p>
											<span>{{ getMixinValue.lang.category }} : {{ template.category }}</span>
										</div>
									</a>
								</span>	
							</div>
							<div class="col-lg-12" v-for="(template, index) in props.templates.data" :key="index">
								<span v-if="template.type == 'messenger' && data.user.source == 'messenger'">
									<a :href="getMixinValue.getUrl('messenger/send-template?template_id=' + template.id + '&contact_id=' + props.chat_room_id)"
										target="_blank">
										<div class="mb-3 canned_response_div">
											<p class="m-0">
												{{ getMixinValue.lang.title }} : <strong>{{ template.name }}</strong>
											</p>
											<span>{{ getMixinValue.lang.category }} : {{ template.category }}</span>
										</div>
									</a>
								</span>	
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

	<!-- web template model -->
	<div class="modal fade" id="sendWebTemplateModal" tabindex="-1" aria-labelledby="sendWebTemplateModalLabel"
		aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title">{{ getMixinValue.lang.web_templates }}</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body p-0">
					<div class="row add-coupon">

							<!-- <pre>{{ props.webTemplates.data }}</pre> -->

							<div class="col-lg-12" v-for="(template, index) in props.webTemplates.data" :key="index">
								<span v-if="template.status == 1">
									<a :href="getMixinValue.getUrl('web/send-template?template_id=' + template.id + '&contact_id=' + props.chat_room_id)"
										target="_blank">
										<div class="mb-3 canned_response_div">
											<p class="m-0">
												{{ getMixinValue.lang.title }} : <strong>{{ template.name }}</strong>
											</p>
										</div>
									</a>
								</span>	
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
					<button type="button" class="btn btn-primary" @click="closeWebModalById('sendWebTemplateModal')">
						{{ getMixinValue.lang.close }}
					</button>
				</div>
			</div>
		</div>
	</div>

	<Transition>
		<Modal class="sp-modal" id="chatForward" :isOpen="messageForwardModalOpened" @modal-close="closeForwardModal"
			name="messageForward-modal">
			<template #header class="modal-title">
				<div class="row w-100">
					<div class="col-6">
						<p class="m-0 mt-3">{{ getMixinValue.lang.forward_to }} <span v-if="forwardingContact">{{
		forwardingContact.name }}</span></p>
					</div>
					<div class="col-6 text-end">
						<button @click="closeForwardModal" type="button" class="btn" style="font-size: 15px">
							<i class="las la-times"></i>
						</button>
					</div>
				</div>
			</template>
			<template #content class="pb-0" style="margin-bottom: 0;">
				<div class="forward__header">
					<div class="forward__msg">
						<div class="forward__bdy">
							<div v-if="messagesToForward.message_type === 'text'" class="forward__txt"
								v-html="messagesToForward.value"></div>
							<div class="forward__img" v-else-if="messagesToForward.message_type === 'image'">
								<img :src="messagesToForward.header_image" alt="Forwarded Image">
							</div>
							<div class="forward__video" v-else-if="messagesToForward.message_type === 'video'">
								<vue-plyr>
									<video style="width: 100%;">
										<source :src="messagesToForward.header_video" type="video/mp4">
									</video>
								</vue-plyr>
							</div>
							<div class="forward__audio" v-else-if="messagesToForward.message_type === 'audio'">
								<vue-plyr>
									<audio>
										<source :src="messagesToForward.header_audio" type="audio/mp3">
									</audio>
								</vue-plyr>
							</div>
							<div class="forward__document" v-else-if="messagesToForward.message_type === 'document'">
								<div class="icon">
									<i class="las la-file-alt"></i>
								</div>
								<a :href="messagesToForward.header_document" target="_blank">Open Document</a>
							</div>
							<div class="forward__location" v-else-if="messagesToForward.message_type === 'location'">
								<div id="map">
									<img :src="messagesToForward.header_location" alt="Location Map" />
								</div>
							</div>
							<div class="interactive__card" v-else-if="messagesToForward.message_type === 'interactive_button'">
								<div class="header__txt" v-if="messagesToForward.header_text">
									{{ messagesToForward.header_text }}
								</div>
								<p>
									{{ messagesToForward.value }}
								</p>
								<div class="footer__txt" v-if="messagesToForward.footer_text">
									{{ messagesToForward.footer_text }}
								</div>
							</div>
						</div>
					</div>
					<div class="modal-search mb-3">
						<div class="search-field">
							<input type="text" placeholder="Search" @keyup="searchContacts"
								v-model="data.contact_search" />
							<button class="sp-round-btn serach-btn" type="submit"><i class="las la-search"></i></button>
						</div>
					</div>
				</div>
				<div class="modal__scroll p-0">
					<ul class="author-card-list mb-0">
						<li v-for="(contact, index) in data.contacts.filter(contact => contact.type === 'whatsapp')" :key="index" @click="selectContact(contact)">
							<a href="javascript:void(0)" class="single-sp-author-card"
								:class="data.forward_message_id == contact.id ? 'active' : ''">
								<div class="author-image">
									<img :src="contact.image" :alt="contact.name" />  
								</div>
								<div class="content">
									<h6 class="title">{{ contact.name }}</h6>
									<p>
										<i class="lab la-whatsapp"></i>
										<span>{{ contact.phone }}</span>
									</p>
								</div>
							</a>
						</li>
					</ul>

				</div>
			</template>
			<template #footer>
				<div class="modal-footer mt-3">
					<loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
					<button v-else type="button" :disabled="!canSendMessages" class="btn btn-primary btn-lg" @click="sendForwardMessage">
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
import { useReplyStore } from '../stores/replyStore';
const getMixinValue = globalValue();
import { ref, computed, onMounted, reactive,watch, nextTick } from "vue"; // Import nextTick

import Modal from "@/src/partials/modal.vue";
const props = defineProps(["chat_room_id", "staffs", "messageScroller", "messageSender", "templates", "template_search", "template_loading", "webTemplates"]);
const emit = defineEmits(["closeAllModals", "loadTemplates", "loadWebTemplates", "update:template_search"]);
const searchTerm = ref(props.template_search);
const messageForwardModalOpened = ref(false);
const isWebChat = ref(false)
const replyStore = useReplyStore();
import { useForwardStore } from '../stores/forwardStore';
const forwardStore = useForwardStore();
import { useChatRoomStore } from "../stores/chatRoomStore";
const chatRoomStore = useChatRoomStore();
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
const messagesToForward = computed(() => forwardStore.getMessagesToForward);
const isForwarding = computed(() => forwardStore.getIsForwarding);
const forwardingContact = computed(() => forwardStore.getForwardingContact);
const messageTypesToForward = computed(() => forwardStore.getMessageTypesToForward);

const data = reactive({
	forward_message_id: "",
	messages: [],
	message_next_page_url: false,
	message_loading: false,
	can_not_reply: true,
	template_search: "",
	reply_message: "",
	reply_message_id: "",

	contacts: [],

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

const removeMessage = (message) => {
	forwardStore.removeMessageFromForward(message);
};
const forwardMessages = () => {
	forwardStore.forwardMessages();
};

const canSendMessages = computed(() => {
  return forwardStore.messagesToForward !== null && forwardStore.forwardingContact !== null;
});
let timeoutId = null;
// Function to create FormData object
const createFormData = (messageIds, contactId) => {
  const formData = new FormData();
  formData.append('messageIds', JSON.stringify(messageIds)); // Send as JSON string
  formData.append('contactId', contactId);
  return formData;
};
const sendForwardMessage = async () => {
	getMixinValue.config.loading = true;
  const messageIds = forwardStore.messagesToForward.id; // Assuming each message has a unique ID
  const contactId = forwardStore.forwardingContact.id;
  const config = {
    headers: {
      "Content-Type": "multipart/form-data; charset=utf-8; boundary=" + Math.random().toString().substr(2),
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
    },
  };
  data.message_loading = true;
  let url = getMixinValue.getUrl("send-forward-message");
  const formData = createFormData(messageIds, contactId);
  try {
    const response = await axios.post(url, formData, config);
    data.message_loading = false;
    if (response.data.status) {
      forwardStore.clearMessagesToForward();
      forwardStore.clearForwardingContact();
      forwardStore.messagesToForward = null;
      forwardStore.forwardingContact = null;
	  messageForwardModalOpened.value = false;  
	  toastr.success(response.data.message);
	  data.chat_room_id = contactId;

	  const forwardedChatRoomId = contactId;
    	chatRoomStore.setSelectedChatRoomId(forwardedChatRoomId); // Update the selected chat room ID in the store

    } else {
		toastr.error("Failed to forward messages. Please try again.");
		getMixinValue.config.loading = false;
    }
  } catch (error) {  
	toastr.error("Something went wrong. Please try again later.");
	getMixinValue.config.loading = false;
  }
};
function openForwardModal(msgIndex, message) {
	messageForwardModalOpened.value = true;
	forwardStore.addMessageToForward(message, message.message_type);
	getContacts();
}
// Function to handle closing of forward modal
const closeForwardModal = () => {
	forwardStore.removeMessageFromForward();
	forwardStore.clearForwardingContact();
	messageForwardModalOpened.value = false;
};
// New method to delete a message
const deleteMessage = async (msgIndex, message) => {
	Swal.fire({
		title: 'Are you sure?',
		text: "You won't be able to revert this!",
		icon: 'warning',
		confirmButtonColor: "#d33",
  		cancelButtonColor: "#6e7d88",
		showCancelButton: true,
		confirmButtonText: 'Yes, delete it!',
		cancelButtonText: 'No, cancel!',
	}).then(async (result) => {
		if (result.isConfirmed) {
			try {
				let url = getMixinValue.getUrl(`chat/delete/${message.id}`);
				const response = await axios.delete(url);
				// console.log(data.messages);
				if (response.data.success) {
					forwardStore.removeMessageFromForward(message);
					// console.log(message);
					const dateGroup = data.messages.find(group => 
                        group.messages.some(msg => msg.id === message.id)
                    );
                    if (dateGroup) {
                        const messageIndex = dateGroup.messages.findIndex(msg => msg.id === message.id);
                        if (messageIndex !== -1) {
                            dateGroup.messages.splice(messageIndex, 1); // Remove the message from the array
                        }
                        // Remove the date group if no messages are left
                        if (dateGroup.messages.length === 0) {
                            const dateGroupIndex = data.messages.indexOf(dateGroup);
                            if (dateGroupIndex !== -1) {
                                data.messages.splice(dateGroupIndex, 1);
                            }
                        }
                    }

					Swal.fire(
						'Deleted!',
						'Your message has been deleted.',
						'success'
					);
				} else {
					Swal.fire(
						'Error!',
						'Error deleting message: ' + response.data.message,
						'error'
					);
				}
			} catch (error) {
				Swal.fire(
					'Error!',
					'Error deleting message: ' + error.message,
					'error'
				);
			}
		} else if (result.isDismissed) {
		}
	});
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
// web template open
const openWebModalById = (modalId) => {
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

// web template close
const closeWebModalById = (modalId) => {
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

const selectContact = (contact) => {
	forwardStore.setForwardingContact(contact);
    data.forward_message_id = contact.id;
};
async function blockContact(contact_id) {
	// Show a confirmation dialog
	const result = await Swal.fire({
		title: 'Are you sure?',
		text: "Do you want to block this contact?",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Yes, block it!',
		cancelButtonText: 'No, cancel!',
	});

	if (result.isConfirmed) {
		let config = {
			params: {
				contact_id: contact_id,
			},
		};
		let url = getMixinValue.getUrl("contact/add-blacklist/" + contact_id);

		try {
			let response = await axios.get(url, config);
			getMixinValue.config.loading = false;

			if (response.data.error) {
				Swal.fire({
					icon: 'error',
					title: 'Oops...',
					text: response.data.error,
				});
			} else if (response.data.status) {

				Swal.fire({
					icon: 'success',
					title: 'Blocked!',
					text: response.data.message,
				}).then(() => {
					window.location.href = "/client/chat";
				}); 
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: response.data.message,
				});
			}
		} catch (error) {
			getMixinValue.config.loading = false;
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: 'An error occurred while blocking the contact.',
			});
			console.error(error);
		}
	} else {
		console.log('Block contact action was canceled.');
	}
}

async function copyMessageToClipboard(text) {
  if (!text) {
    toastr.error('No message text to copy.');
    return;
  }

  try {
    await navigator.clipboard.writeText(text);
    toastr.success('Message copied to clipboard!');
  } catch (err) {
    toastr.error('Failed to copy: ', err);
    console.error('Failed to copy: ', err);
  }
}

async function clearChat(contact_id) {
	const result = await Swal.fire({
		title: 'Are you sure?',
		text: "This will clear all messages for this contact!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Yes, clear it!',
		cancelButtonText: 'No, cancel!',
	});

	if (result.isConfirmed) {
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
				Swal.fire({
					icon: 'error',
					title: 'Oops...',
					text: response.data.error,
				});
			} else if (response.data.status) {
				Swal.fire({
					icon: 'success',
					title: 'Cleared!',
					text: response.data.message,
				});
				data.messages = []; // Clear the messages
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: response.data.message,
				});
			}
		} catch (error) {
			getMixinValue.config.loading = false;
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: 'An error occurred while clearing the chat.',
			});
			console.error(error);
		}
	} else {
		console.log('Chat clear action was canceled.');
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
	let url = getMixinValue.getUrl("message/" + props.chat_room_id);
	await axios.get(url, config).then((response) => {
		getMixinValue.config.loading = false;
		console.log(response);
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

function setReplyMessage(msgIndex, message) {
	replyStore.setReplyMessage(message);
	console.log(`Replying to message ID: ${message.id} with value: ${message.value}`);
}

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
		console.log(response);
		if (response.data.error) {
				Swal.fire({
					icon: 'error',
					title: 'Oops...',
					text: response.data.error,
				});
			} else if (response.data.status) {
				Swal.fire({
					icon: 'success',
					title: 'Assingned!',
					text: response.data.message,
				});
				data.messages = []; // Clear the messages
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: response.data.message,
				});
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

async function searchContacts() {
	if (timeoutId) {
		clearTimeout(timeoutId);
	}

	timeoutId = setTimeout(() => {
		getContacts();
	}, 1000);
}

// Define a ref to store message refs
const messageRefs = ref({});

// Function to scroll to a specific message
async function scrollToMessage(messageId) {
  await nextTick(); // Ensure DOM updates are applied
  const element = messageRefs.value[`message_${messageId}`];
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
  } else {
    console.error(`Element with ref message_${messageId} not found`);
  }
}

// Function to set refs dynamically
function setMessageRef(messageId, el) {
  if (el) {
    messageRefs.value[`message_${messageId}`] = el;
  } else {
    delete messageRefs.value[`message_${messageId}`];
  }
}

// Watch for changes in messageScroller to trigger scroll
watch(
  () => props.messageScroller,
  (newMessageId) => {
    if (newMessageId) {
      scrollToMessage(newMessageId);
    }
  }
);

onMounted(() => {
	// Example initial scroll; replace with actual logic if needed
	const initialMessageId = props.initialMessageId; // Set this as needed
	if (initialMessageId) {
		scrollToMessage(initialMessageId);
	}
	
	isWebChat.value = window.location.href.includes('/client/web-chat')
});

function shouldShowForwardOption(message) {
  const unsupportedTypes = ['interactive', 'button', 'condition', 'reaction', 'unsupported'];
  return !unsupportedTypes.includes(message.message_type);
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
