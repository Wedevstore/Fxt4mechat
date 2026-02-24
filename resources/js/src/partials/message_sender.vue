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
						<i class="las la-file-video"></i>
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
				<div v-else-if="replyMessage.message_type === 'contacts' && replyMessage.message_type === 'contact'">
					<div class="icon">
						<i class="las la-file-contract"></i>
					</div>
				</div>  
				<div v-if="['interactive_button', 'interactive', 'button','interactive_list'].includes(replyMessage.message_type)">
					<small>{{ replyMessage.buttons[0].text }}</small>
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
					<div class="savad-item-area show-item" tabindex="-1" v-show="cannedResponseOpened" @modal-close="closecannedResponseModal">
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
					<div class="savad-item-area robot__popup show-item" id="robot__replay" v-show="replyWithAIOpened" @modal-close="closeAIReplyModal"  tabindex="-1">
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
								<div class="custom__radio">
									<div v-for="type in replyTypes" :key="type.value">
										<input type="radio" :id="type.value" name="reply_type" :value="type.value"
											v-model="replyType" :checked="type.value === 'professional'" />
										<label :for="type.value">{{ type.label }}</label>
									</div>
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
					<div class="savad-item-area ai__popup show-item" id="ai__replay"  v-show="rewriteWithAIOpened" tabindex="-1">
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

				<div v-if="emiroSync">
					<div class="chat_popupBox">
						<button type="button" :title="getMixinValue.lang.stripe_payment_generator" class="bottom-icon robot__replay"
							@click="openStripPaymentModal"
								v-show="ecommerceButtonStatus.stripePayment && ecommerceButtonStatus.stripePayment.stripe_payment_link == 1"
							>
							<i class="lab la-cc-stripe"></i>
						</button>
						<div class="savad-item-area robot__popup show-item" id="robot__replay" v-show="stripePaymentOpened" @modal-close="closeStripePaymentModal"  tabindex="-1">
							<div class="saved-item-card">
								<div class="header-area">
									<h6 class="title">Stripe Payment Link</h6>
								</div>
								<div class="">

									<div style="padding: 10px 30px;">
										<input v-model="amount" placeholder="Enter Amount" type="number" min="0.5" step="0.01" />
									</div>
									<div style="padding: 10px 30px;">
										<!-- <input v-model="currency" placeholder="Currency (E.G. USD)" maxlength="3" class="currency" /> -->
									<select class="currency" style="background-color: white; color:black; border: 2px solid #dadada; height: 50px; padding: 10px 8px;" v-model="currency">
									  <option disabled value="">Please select currency</option>
									  <option v-for="cur in currencyList" :key="cur" :value="cur">
										{{ cur }}
									  </option>
									</select>

									<div class="btn-groups mt-3 text-end mr-3">
										<loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
	
										<button type="button" v-else class="btn sg-btn-primary mr-2" @click="generateStripeURL">
											{{ getMixinValue.lang.submit }}
										</button>
									</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div v-if="emiroSync">
					<div class="chat_popupBox">
						<button type="button" :title="getMixinValue.lang.show_all_products" class="bottom-icon robot__replay"
							@click="openShopifyProductModal"
							>
							<i class="las la-store"></i>
						</button>
					</div>
				</div>
			</div>
		</form>

		<!--Product Modal -->
		<!-- <Transition>
			<Modal class="sp-modal" :isOpen="shopifyProductOpen" @modal-close="closeShopifyProductModal" name="product-modal">
				<template #header class="modal-title">
					<div class="row w-100">
						<div class="col-lg-6">
							<p class="m-0 mt-3">{{ getMixinValue.lang.all_products }}</p>
						</div>
						<div class="col-lg-6 text-end">
							<button @click="closeShopifyProductModal" type="button" class="btn" style="font-size: 15px"><i
									class="las la-times"></i></button>
						</div>
					</div>
				</template>
				<template #content>
					<div class="modal-body">
						<div class="row" v-if="loading">
							<div>Loading...</div>
						</div>
						<div v-else>
							<div class="row" style="padding-bottom: 20px;" v-for="product in products" :key="product.id">
								<div class="col-2">
									<div class="productImage">
										<img v-if="product.image && product.image.src" style="width: 80px;" :src="product.image.src" alt="">
									</div>
								</div>
								<div class="col-10 shopifyModel">
									<div class="title">{{ product.title }}</div>
									<div class="title" v-if="product.variants && product.variants.length">Price: {{ product.variants[0].price }} {{ product.variants[0].currency || '' }}</div>
									<ul>
									  <li>
										<a
										  @click="productInfoTemplate(product)"
										  :class="{ 'loadingButton': loadingButton }"
										>
										  <span v-if="loadingButton">Product Info</span>
										  <span v-else>Product Info</span>
										</a>
									  </li>
									  <li>
										<a
										  @click="productWithPaymentTemplate(product)"
										  :class="{ 'loadingButton': loadingButton }"
										>
										  <span v-if="loadingButton">Product With Payment</span>
										  <span v-else>Product With Payment</span>
										</a>
									  </li>
									  <li>
										<a
										  @click="createOrderSendTemplate(product)"
										  :class="{ 'loadingButton': loadingButton }"
										>
										  <span v-if="loadingButton">Create Order & Send</span>
										  <span v-else>Create Order & Send</span>
										</a>
									  </li>
									</ul>

								</div>
							</div>
						</div>
					</div>
				</template>
				<template #footer>
					<div class="modal-footer mt-3" style="display: none;">
						<loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
						<button type="button" v-else class="btn btn-primary btn-lg" @click="generateAIReply">
							{{ getMixinValue.lang.send }}
						</button>
					</div>
				</template>
			</Modal>
		</Transition> -->

		<Transition>
		  <Modal class="sp-modal" :isOpen="shopifyProductOpen" @modal-close="closeShopifyProductModal" name="product-modal">
			<template #header class="modal-title">
			  <div class="row w-100">
				<div class="col-lg-6">
				  <p class="m-0 mt-3">{{ getMixinValue.lang.all_products }}</p>
				</div>
				<div class="col-lg-6 text-end">
				  <button @click="closeShopifyProductModal" type="button" class="btn" style="font-size: 15px">
					<i class="las la-times"></i>
				  </button>
				</div>
			  </div>
			</template>

			<template #content>
			  <div class="modal-body">

				<!-- Tabs -->
				<ul class="nav nav-tabs mb-3">
					<li class="nav-item" v-show="ecommerceButtonStatus.shopify.status === 1">
					<a
						class="nav-link"
						:class="{ active: activeTab === 'shopify' }"
						href="#"
						@click.prevent="activeTab = 'shopify'"
					>{{ getMixinValue.lang.shopify }}</a>
					</li>
					<li class="nav-item" v-show="ecommerceButtonStatus.bigcommerce.status === 1">
					<a
						class="nav-link"
						:class="{ active: activeTab === 'bigcommerce' }"
						href="#"
						@click.prevent="activeTab = 'bigcommerce'"
					>{{ getMixinValue.lang.bigcommerce }}</a>
					</li>
					<li class="nav-item" v-show="ecommerceButtonStatus.woocommerce.status === 1">
					<a
						class="nav-link"
						:class="{ active: activeTab === 'woocommerce' }"
						href="#"
						@click.prevent="activeTab = 'woocommerce'"
					>{{ getMixinValue.lang.woocommerce }}</a>
					</li>
				</ul>

				<!-- Content by Tab -->
				<div v-if="loading">
				  <div>{{ getMixinValue.lang.loading }}</div>
				</div>

				<!-- Shopify Products -->
				<div v-else-if="activeTab === 'shopify'">
					<div class="row" v-if="loading">
						<div>{{ getMixinValue.lang.loading }}</div>
					</div>
					<div v-else>
						<div class="row">
							<div class="col-12" style="padding-right: 15px;">
								<input
								  type="text"
								  v-model="searchQuery"
								  class="form-control mb-3"
								  :placeholder="getMixinValue.lang.search_products || 'Search products...'"
								/>
							</div>
						</div>
						<div class="row" style="padding-bottom: 20px;" v-for="product in filteredShopifyProducts" :key="product.id">
							<div class="col-2">
								<div class="productImage">
									<img v-if="product.image && product.image.src" style="width: 80px;" :src="product.image.src" alt="">
								</div>
							</div>
							<div class="col-10 shopifyModel">
								<div class="title">{{ product.title }}</div>
								<div class="title" v-if="product.variants && product.variants.length">{{ getMixinValue.lang.price }}: {{ product.variants[0].price }} {{ product.variants[0].currency || '' }}</div>
								<ul>
									<li v-show="ecommerceButtonStatus.shopify.shopify_product_info">
									  <a
										@click="() => { activeButton = { productId: product.id, type: 'info' }; productInfoTemplate(product) }"
										:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'info' }"
									  >
										{{ getMixinValue.lang.product_info }}
									  </a>
									</li>
									<li v-show="ecommerceButtonStatus.shopify.shopify_product_with">
									  <a
										@click="() => { activeButton = { productId: product.id, type: 'payment' }; productWithPaymentTemplate(product) }"
										:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'payment' }"
									  >
										{{ getMixinValue.lang.product_with_payment }}
									  </a>
									</li>
									<li v-show="ecommerceButtonStatus.shopify.shopify_create_order_send">
									  <a
										@click="() => { activeButton = { productId: product.id, type: 'order' }; createOrderSendTemplate(product) }"
										:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'order' }"
									  >
										{{ getMixinValue.lang.create_order_and_send }}
									  </a>
									</li>
								</ul>

							</div>
						</div>
					</div>
				</div>

				<!-- BigCommerce Products -->
				<div v-else-if="activeTab === 'bigcommerce'">
					<div class="row" v-if="loading">
						<div>{{ getMixinValue.lang.loading }}</div>
					</div>
					<div v-else>
						<div class="row">
							<div class="col-12" style="padding-right: 15px;">
								<input
								  type="text"
								  v-model="searchQuery"
								  class="form-control mb-3"
								  :placeholder="getMixinValue.lang.search_products || 'Search products...'"
								/>
							</div>
						</div>
						<div class="row" style="padding-bottom: 20px;" v-for="product in filteredBigCommerceProducts" :key="product.id">
							<div class="col-2">
								<div class="productImage">
								  	<img
										v-if="product.images && product.images.length"
										:src="product.images[0].url_thumbnail"
										alt=""
										style="width: 80px;"
								  	>
								</div>
							</div>
							<div class="col-10 shopifyModel">
								<div class="title">{{ product.name }}</div>
								<div class="title">{{ getMixinValue.lang.price }}: {{ product.price }}</div>
								<ul>
								<li v-show="ecommerceButtonStatus.bigcommerce.big_product_info">
								  <a
									@click="() => { activeButton = { productId: product.id, type: 'info' }; bigCommerceProductInfoTemplate(product) }"
									:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'info' }"
								  >
									{{ getMixinValue.lang.product_info }}
								  </a>
								</li>
								<li v-show="ecommerceButtonStatus.bigcommerce.big_product_with">
								  <a
									@click="() => { activeButton = { productId: product.id, type: 'payment' }; bigCommerceProductWithPaymentTemplate(product) }"
									:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'payment' }"
								  >
									{{ getMixinValue.lang.product_with_payment }}
								  </a>
								</li>
								<li v-show="ecommerceButtonStatus.bigcommerce.big_create_order_send">
								  <a
									@click="() => { activeButton = { productId: product.id, type: 'order' }; bigCommerceCreateOrderSendTemplate(product) }"
									:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'order' }"
								  >
									{{ getMixinValue.lang.create_order_and_send }}
								  </a>
								</li>
								</ul>

							</div>
						</div>
					</div>
				</div>

				<!-- WooCommerce Products -->
				<div v-else-if="activeTab === 'woocommerce'">
					<div class="row" v-if="loading">
						<div>{{ getMixinValue.lang.loading }}</div>
					</div>
					<div v-else>
						<div class="row">
							<div class="col-12" style="padding-right: 15px;">
								<input
								  type="text"
								  v-model="searchQuery"
								  class="form-control mb-3"
								  :placeholder="getMixinValue.lang.search_products || 'Search products...'"
								/>
							</div>
						</div>
						<div class="row" style="padding-bottom: 20px;" v-for="product in filteredWooCommerceProducts" :key="product.id">
							<div class="col-2">
								<div class="productImage">
									<img
										v-if="product.images && product.images.length"
										:src="product.images[0].src"
										alt=""
										style="width: 80px;"
									>
								</div>
							</div>
							<div class="col-10 shopifyModel">
								<div class="title">{{ product.name }}</div>
								<div class="title">{{ getMixinValue.lang.price }}: {{ product.price }}</div>
								<ul>
								<li v-show="ecommerceButtonStatus.woocommerce.woo_product_info">
								  <a
									@click="() => { activeButton = { productId: product.id, type: 'info' }; wooCommerceProductInfoTemplate(product) }"
									:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'info' }"
								  >
									{{ getMixinValue.lang.product_info }}
								  </a>
								</li>
								<li v-show="ecommerceButtonStatus.woocommerce.woo_product_with">
								  <a
									@click="() => { activeButton = { productId: product.id, type: 'payment' }; wooCommerceProductWithPaymentTemplate(product) }"
									:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'payment' }"
								  >
									{{ getMixinValue.lang.product_with_payment }}
								  </a>
								</li>
								<li v-show="ecommerceButtonStatus.woocommerce.woo_create_order_send">
								  <a
									@click="() => { activeButton = { productId: product.id, type: 'order' }; wooCommerceCreateOrderSendTemplate(product) }"
									:class="{ 'loadingButton': loadingButton, 'btn-green': activeButton.productId === product.id && activeButton.type === 'order' }"
								  >
									{{ getMixinValue.lang.create_order_and_send }}
								  </a>
								</li>
								</ul>

							</div>
						</div>
					</div>
				</div>

			  </div>
			</template>

			<template #footer>
			  <div class="modal-footer mt-3" style="display: none;">
				<loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
				<button type="button" v-else class="btn btn-primary btn-lg" @click="generateAIReply">
				  {{ getMixinValue.lang.send }}
				</button>
			  </div>
			</template>
		  </Modal>
		</Transition>



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
import { useRoute } from "vue-router";
const route = useRoute();
const replyStore = useReplyStore();
const replyMessage = computed(() => replyStore.replyMessage);
const replyMessageId = computed(() => replyStore.replyMessageId);
const getMixinValue = globalValue();
const replyWithAIOpened = ref(false);
const stripePaymentOpened = ref(false);
const cannedResponseOpened = ref(false);
const rewriteWithAIOpened = ref(false);
const emiroSync = ref(false)
const activeTab = ref('shopify')
const shopifyProductOpen = ref(false);
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
const amount = ref(null);
const currency = ref('');
const loading = ref(false);
const products = ref([]);
const bigCommerceProductList = ref([]);
const wooCommerceProductList = ref([]);
const currencyList = ref([]);
const loadingButton = ref(false);
// Replace with real data loading
const shopifyProducts = ref([]);
const bigcommerceProducts = ref([]);
const woocommerceProducts = ref([]);
const searchQuery = ref('');
const ecommerceButtonStatus = ref({});

const activeButton = ref({ productId: null, type: null });

onMounted(() => {
	getMixinValue.storeData.receiver_id = props.chat_room_id;
	checkEmiroConfig()
	ecommerceButtonStatusConfig()
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

const filteredShopifyProducts = computed(() => {
  if (!products.value) return []
  return products.value
	.filter(product => !!product && !!product.title) // filter null/undefined
	.filter(product =>
	  product.title.toLowerCase().includes(searchQuery.value.toLowerCase())
	)
})

const filteredBigCommerceProducts = computed(() => {
  if (!bigCommerceProductList.value) return []
  return bigCommerceProductList.value
	.filter(product => !!product && !!product.name)
	.filter(product =>
	  product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
	)
})

const filteredWooCommerceProducts = computed(() => {
  if (!wooCommerceProductList.value) return []
  return wooCommerceProductList.value
	.filter(product => !!product && !!product.name)
	.filter(product =>
	  product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
	)
})

const sendProductTemplate = async (product, templateType = 'default') => {
	try {
		loadingButton.value = true;
		const url = getMixinValue.getUrl("message/" + props.chat_room_id);
		const userResponse = await axios.get(url);
		const user = userResponse.data.user;

		const product_template_url = getMixinValue.getUrl("shopify/product/send-template");
		const response = await axios.post(product_template_url, {
			product_id: product.id ?? null,
			title: product.title ?? null,
			price: product.variants[0]?.price ?? null,
			currency: product.variants[0]?.currency ?? null,
			image_url: product.image?.src ?? null,
			user_id: user.id ?? null,
			phone: user.phone ?? null,
			name: user.name ?? null,
			template_type: templateType ?? null,
		});

		shopifyProductOpen.value = !shopifyProductOpen.value;
		toastr.success(response.data.message);
		loadingButton.value = false;
		activeButton.value = { productId: null, type: null };
		emit("sendMessages", { message_sender: 1 });

	} catch (error) {
		// console.error(error);
		toastr.error('Something went wrong');
	}
};

const sendBigCommerceProductTemplate = async (product, templateType = 'default') => {
	try {
		loadingButton.value = true;
		const url = getMixinValue.getUrl("message/" + props.chat_room_id);
		const userResponse = await axios.get(url);
		const user = userResponse.data.user;

		const product_template_url = getMixinValue.getUrl("big-commerce/product/send-template");
		const response = await axios.post(product_template_url, {
			product_id: product.id ?? null,
			title: product.name ?? null,
			price: product.price ?? null,
			image_url: product.images[0].url_thumbnail ?? null,
			user_id: user.id ?? null,
			phone: user.phone ?? null,
			name: user.name ?? null,
			template_type: templateType ?? null,
		});

		shopifyProductOpen.value = !shopifyProductOpen.value;
		toastr.success(response.data.message);
		loadingButton.value = false;
		activeButton.value = { productId: null, type: null };
		emit("sendMessages", { message_sender: 1 });

	} catch (error) {
		console.error(error);
		toastr.error('Something went wrong');
	}
};

const sendWooCommerceProductTemplate = async (product, templateType = 'default') => {
	try {
		loadingButton.value = true;
		const url = getMixinValue.getUrl("message/" + props.chat_room_id);
		const userResponse = await axios.get(url);
		const user = userResponse.data.user;

		const product_template_url = getMixinValue.getUrl("woo-commerce/product/send-template");
		const response = await axios.post(product_template_url, {
			product_id: product.id ?? null,
			title: product.name ?? null,
			price: product.price ?? null,
			image_url: product.images[0].src ?? null,
			user_id: user.id ?? null,
			phone: user.phone ?? null,
			name: user.name ?? null,
			template_type: templateType ?? null,
		});

		shopifyProductOpen.value = !shopifyProductOpen.value;
		toastr.success(response.data.message);
		loadingButton.value = false;
		activeButton.value = { productId: null, type: null };
		emit("sendMessages", { message_sender: 1 });

	} catch (error) {
		console.error(error);
		toastr.error('Something went wrong');
	}
};


const productInfoTemplate = (product) => sendProductTemplate(product, 'product_info');
const productWithPaymentTemplate = (product) => sendProductTemplate(product, 'product_with_payment');
const createOrderSendTemplate = (product) => sendProductTemplate(product, 'create_order_and_send');

const bigCommerceProductInfoTemplate = (product) => sendBigCommerceProductTemplate(product, 'product_info');
const bigCommerceProductWithPaymentTemplate = (product) => sendBigCommerceProductTemplate(product, 'product_with_payment');
const bigCommerceCreateOrderSendTemplate = (product) => sendBigCommerceProductTemplate(product, 'create_order_and_send');

const wooCommerceProductInfoTemplate = (product) => sendWooCommerceProductTemplate(product, 'product_info');
const wooCommerceProductWithPaymentTemplate = (product) => sendWooCommerceProductTemplate(product, 'product_with_payment');
const wooCommerceCreateOrderSendTemplate = (product) => sendWooCommerceProductTemplate(product, 'create_order_and_send');

const activeTabProducts = computed(() => {
  return activeTab.value === 'shopify' ? shopifyProducts.value : bigcommerceProducts.value
})

function closeShopifyProductModal() {
  shopifyProductOpen.value = false;
}

function onSelectEmoji(emoji) {
	getMixinValue.storeData.message += emoji.i;
}

const clearReplyMessage = () => {
	replyStore.replyMessage = null;  // Clear the replyMessage in the store
};
const fetchMessages = async (chatRoomId, limit) => {
	try {
		const url = getMixinValue.getUrl(`contact-messages/${chatRoomId}?limit=${limit}`);
		const response = await axios.get(url);
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
		cannedResponseOpened.value = false;
		replyWithAIOpened.value = !replyWithAIOpened.value;
	}
};

const openShopifyProductModal = async () => {
	shopifyProductOpen.value = !shopifyProductOpen.value;
	
	if (shopifyProductOpen.value && products.value.length === 0) {
		loading.value = true
		try {
			let url = getMixinValue.getUrl('shopify/products');
		  	const response = await axios.get(url)
		  	products.value = response.data.data.products || []
		} catch (error) {
		  	console.error('Failed to fetch products', error)
		} finally {
		  	loading.value = false
		}
	}

	showBigCommerceProduct();
	showWooCommerceProduct();

};

const showBigCommerceProduct = async () => {
	loading.value = true
	try {
		let url = getMixinValue.getUrl('bigcommerce/products');
		const response = await axios.get(url)
		bigCommerceProductList.value = response.data.data.products || []
	} catch (error) {
		console.error('Failed to fetch products', error)
	} finally {
		loading.value = false
	}
	
};

const showWooCommerceProduct = async () => {
	loading.value = true
	try {
		let url = getMixinValue.getUrl('woocommerce/products');
		const response = await axios.get(url)
		wooCommerceProductList.value = response.data.data.products || []
	} catch (error) {
		console.error('Failed to fetch products', error)
	} finally {
		loading.value = false
	}
	
};

const openStripPaymentModal = async () => {
	stripePaymentOpened.value = !stripePaymentOpened.value;
	try {
		let url = getMixinValue.getUrl('generate-stripe/currency');
		const response = await axios.get(url)
		currencyList.value = response.data.data.currencies || []
	} catch (error) {
		console.error('Failed to fetch currency', error)
	} finally {
		loading.value = false
	}

};

const openAIRewriteModal = async () => {
	const textareaValue = document.querySelector('textarea').value;
	const context = textareaValue ? [textareaValue] : '';
	// console.log(context);
	if (context) {
		data.context = context;
		replyWithAIOpened.value = false;
		cannedResponseOpened.value = false;
		rewriteWithAIOpened.value = !rewriteWithAIOpened.value;
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

const closeStripePaymentModal = () => {
	stripePaymentOpened.value = false;
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

const checkEmiroConfig = async () => {
	try {
		const response = await axios.get('/check-addons-config')
		emiroSync.value = response.data.emiroSync || response.data.saleBotECommerce
	} catch (error) {
		console.error('Error checking config.json:', error)
		emiroSync.value = false
	}
}
const ecommerceButtonStatusConfig = async () => {
	try {
		const response = await axios.get('/client/ecommerce/buttons')
		ecommerceButtonStatus.value = response.data.buttons
	} catch (error) {
		ecommerceButtonStatus.value = false
	}
}

// const checkEmiroConfig = async () => {
//   try {
// 	const response = await axios.get('/check-emiro-config')
// 	emiroSync.value = response.data.exists
//   } catch (error) {
// 	console.error('Error checking config.json:', error)
// 	emiroSync.value = false
//   }
// }



const generateStripeURL = async () => {
	if (!amount.value || isNaN(amount.value) || Number(amount.value) < 0.5) {
		toastr.error('Please enter a valid amount (minimum 0.5).');
		return;
	}

	if (!currency.value) {
		toastr.error('Please select a valid 3-letter currency code.');
		return;
	}

	loading.value = true;

	let url = getMixinValue.getUrl('generate-stripe-link');

	try {
		const response = await axios.post(url, {
		amount: parseFloat(amount.value).toFixed(2),
		currency: currency.value.toUpperCase(),
		}, {
		headers: {
			'Content-Type': 'application/json',
			'Accept': 'application/json',
		}
		});

		const result = response.data;

		if (response.status === 200 && result.url) {
		getMixinValue.storeData.message = decodeHtmlEntities(result.url);
		closeStripePaymentModal();
		currency.value = "";
		amount.value = "";
		//   window.open(result.url, '_blank');
		} else {
		toastr.error(result.message || 'Failed to generate payment link.');
		}
	} catch (err) {
		// console.error(err);
		toastr.error('An error occurred. Please try again.');
	} finally {
		loading.value = false;
	}
};



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
	rewriteWithAIOpened.value = false;
	replyWithAIOpened.value = false;
	cannedResponseOpened.value = !cannedResponseOpened.value;
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
			toastr.error('Something went wrong');

			// return alert("Something went wrong");
		});
}

async function setMessage(response) {
	getMixinValue.storeData.message = response.reply_text;
}

async function sendMessage() {
	if (!getMixinValue.storeData.message.trim()) {
		toastr.error("Please enter message");
		// return alert("Please enter message");
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
	//let url = getMixinValue.getUrl("send-message");

	// Decide API endpoint by route
	let url = "";
	switch (route.name) {
		case "chat":
			url = getMixinValue.getUrl("send-message");
			break;
		case "web-chat":
			url = getMixinValue.getUrl("send-web-message");
			break;
		default:
			url = getMixinValue.getUrl("send-message");
	}
	
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
				getMixinValue.storeData.reply_message_id = null;
				return true;
			} else {
			}
		})
		.catch((error) => {
			data.message_loading = false;

			// Check if the error has a response with a message
			if (error.response && error.response.data && error.response.data.error) {
				toastr.error(error.response.data.error);
			} else if (error.message) {
				// Fallback to error.message
				toastr.error("Something went wrong");
				// toastr.error(error.message);
			} else {
				toastr.error("Something went wrong");
			}

			return;
		});

		// .catch((error) => {
		// 	data.message_loading = false;
		// 	toastr.error("Something went wrongg");
		// 	return ;
		// });
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
		toastr.error('Something went wrong',error);
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
		toastr.error("Please record audio first");
		return ;
	}
	const last_file = new File([file], "test.mp3", { type: "audio/mpeg" });
	getMixinValue.storeData.image = last_file;
	await message();
	file = null;
}
</script>

