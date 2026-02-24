<template>
  <div class="sp-right-sidebar">
    <div class="right-sidebar-header sticky-top sticky-offset overflow-auto">
      <h6 class="title">{{ getMixinValue.lang.details }}</h6>
    </div>
    <div class="right-sidebar-content">
      <div class="author-info-top text-center">
        <div class="author-img">
          <img class="d-inline-block" :src="data.contact.image" :alt="data.contact.name">
        </div>
        <h6 class="title">{{ data.contact.name }}</h6>
        <p class="desc">{{ data.contact.phone }}</p>
      </div>
      <div class="author-info-tab-area">
        <nav class="chat-tab-two">
          <div class="nav nav-tabs border-0" id="nav-author-info-tab" role="tablist">
            <button class="nav-link active" id="nav-authorinfo-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-authorinfo" type="button" role="tab" aria-controls="nav-authorinfo"
                    aria-selected="true">{{ getMixinValue.lang.details }}
            </button>
            <button class="nav-link" id="nav-sharelist-tab" data-bs-toggle="tab" data-bs-target="#nav-sharelist"
                    @click="getSharedMedias"
                    type="button" role="tab" aria-controls="nav-sharelist" aria-selected="false">{{ getMixinValue.lang.shared_files }}
            </button>
          </div>
        </nav>
        <div class="tab-content" id="nav-chat-tabContent">
          <div class="tab-pane fade active show" id="nav-authorinfo" role="tabpanel"
               aria-labelledby="nav-authorinfo-tab">
            <div class="sp-details-area">
              <ul class="details-info-list">
                <li v-if="data.contact.conversation_id">
                  <i class="las la-comments"></i>
                  <p>{{ getMixinValue.lang.conversation_id }} <strong>{{ data.contact.conversation_id }}</strong></p>
                </li>
                <li>
                  <i v-if="data.contact.source == 'telegram'" class="lab la-telegram"></i>
                  <i v-else class="lab la-whatsapp"></i>
                  <p>{{ getMixinValue.lang.source }} <strong class="text-capitalize">{{ data.contact.source }}</strong></p>
                </li>
                <li>
                  <i class="las la-briefcase"></i>
                  <p>{{ getMixinValue.lang.id }} <strong>{{ data.contact.id }}</strong></p>
                </li>
                <li v-show="isWebChat == true">
                  <i class="las la-mobile"></i>
                    <p>{{ getMixinValue.lang.device }}
                      <strong>{{ data.contact.device_name ? data.contact.device_name : 'N/A' }}</strong>
                    </p>
                </li>
                <li>
                  <i class="las la-user-circle"></i>
                  <p>{{ getMixinValue.lang.user_type }} <strong>User</strong></p>
                </li>
                <li>
                  <i class="las la-calendar-week"></i>
                  <p>{{ getMixinValue.lang.creation_time }} <strong>{{ data.contact.created_at }}</strong></p>
                </li>
                <li>
                  <i class="las la-hourglass-half"></i>
                  <p>{{ getMixinValue.lang.last_activity }}<strong>{{ data.contact.last_conversation_at }}</strong></p>
                </li>
                <li v-if="data.contact.source == 'whatsapp'">
                  <i class="las la-phone-alt"></i>
                  <p>{{ getMixinValue.lang.phone }} <strong>{{ data.contact.phone }}</strong></p>
                </li>
              </ul>
            </div>
            <div class="sp-details-info-area note-area">
              <div class="info-top">
                <h6 class="title">{{ getMixinValue.lang.note }}</h6>
                <div class="action-area">
                  <a href="javascript:void(0)" @click="openModal"><i class="las la-plus"></i></a>
                </div>
              </div>
              <div class="info-content">
                <div class="note-card" v-for="(note, index) in data.notes" :key="index">
                  <transition name="fade">
                    <div v-if="note.show">
                      <div class="position-relative">
                        <h6 class="title">{{ note.title }}</h6>
                        <button @click="deleteNote(note.id, index)" type="button" class="btn" style="font-size: 15px;
    position: absolute;
    top: -12px;
    right: 0;"><i class="las la-times"></i></button>
                      </div>
                      <p>{{ note.details }}</p>
                    </div>
                  </transition>
                </div>
              </div>
            </div>

            <div class="sp-details-info-area tag-area">
              <div class="info-top">
                <h6 class="title">{{ getMixinValue.lang.tags }}</h6>
                <div class="action-area">
                  <a href="javascript:void(0)" @click="openTagModal"><i class="las la-plus"></i></a>
                  <!-- <a href="javascript:void(0)" v-if="data.active_tags.length > 0" @click="openSettingModal"><i class="las la-cog"></i></a> -->
                  <a href="javascript:void(0)" @click="openSettingModal"><i class="las la-cog"></i></a>
                </div>
              </div>
              <div class="info-content">
                <div class="single-tag" v-for="(tag, index) in chatRoomStore.contact_tags" :key="index">{{ tag.title }}</div>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="nav-sharelist" role="tabpanel" aria-labelledby="nav-sharelist-tab">
            <div class="sp-details-info-area media-area border-0" v-if="data.medias.length > 0">
              <div class="info-top">
                <h6 class="title"><i class="las la-image mr--5"></i>{{ getMixinValue.lang.shared_media }}</h6>
              </div>
              <div class="info-content">
                <div v-for="(media, index) in data.medias" :key="index" class="position-relative">
                  <a target="_blank" v-if="media.type == 'image'" :href="media.path" class="image-link single-media">
                    <img :src="media.path" alt="Image">
                  </a>
                  <div class="action-area" style="position:absolute;top: 0;right: 0">
                    <div class="dropdown">
                      <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1"
                              data-bs-toggle="dropdown"
                              aria-expanded="false">
                        <i class="las la-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li @click="hideFile(index, 'media')"><a class="dropdown-item" href="javascript:void(0)">{{
                            getMixinValue.lang.hide
                          }}</a></li>
                        <li @click="deleteFiles(media.id, index, 'media')"><a class="dropdown-item"
                                                                              href="javascript:void(0)">{{
                            getMixinValue.lang.delete
                          }}</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 text-center" v-if="data.media_next_page_url">
                  <loadingBtn v-if="data.media_loading"></loadingBtn>
                  <a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary" @click="loadSharedMedias">
                    <span>{{ getMixinValue.lang.load_more }}</span>
                  </a>
                </div>
              </div>
            </div>

            <div class="sp-details-info-area files-area" v-if="data.files.length > 0">
              <div class="info-top">
                <h6 class="title"><i class="las la-file-invoice mr--5"></i> {{ getMixinValue.lang.shared_files }}</h6>
              </div>
              <div class="info-content">
                <div class="single-media-card" v-for="(file, index) in data.files" :key="index">
                  <div class="left-part">
                    <div class="icon">
                      <i class="las la-file-invoice"></i>
                    </div>
                    <div class="content">
                      <h6 class="title"><a target="_blank" :href="file.path">{{ file.name }}</a></h6>
                      <p>{{ file.sent_at }}</p>
                    </div>
                  </div>
                  <div class="right-part">
                    <div class="dropdown">
                      <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1"
                              data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="las la-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li @click="hideFile(index, 'files')"><a class="dropdown-item" href="javascript:void(0)">{{
                            getMixinValue.lang.hide
                          }}</a></li>
                        <li @click="deleteFiles(file.id, index, 'files')"><a class="dropdown-item"
                                                                             href="javascript:void(0)">{{
                            getMixinValue.lang.delete
                          }}</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 text-center" v-if="data.file_next_page_url">
                  <loadingBtn v-if="data.file_loading"></loadingBtn>
                  <a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary" @click="loadSharedFiles">
                    <span>{{ getMixinValue.lang.load_more }}</span>
                  </a>
                </div>
              </div>
            </div>

            <div class="sp-details-info-area link-area" v-if="data.links.length > 0">
              <div class="info-top">
                <h6 class="title"><i class="las la-link mr--5"></i>{{ getMixinValue.lang.shared_links }}</h6>
              </div>
              <div class="info-content">
                <div class="single-media-card" v-for="(link, index) in data.links" :key="index">
                  <div class="left-part">
                    <div class="icon">
                      <i class="las la-link"></i>
                    </div>
                    <div class="content">
                      <a :href="link" target="_blank" class="link">{{ link }}</a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 text-center" v-if="data.link_next_page_url">
                  <loadingBtn v-if="data.link_loading"></loadingBtn>
                  <a v-else href="javascript:void(0)" class="btn btn-sm sg-btn-primary" @click="loadSharedLinks">
                    <span>{{ getMixinValue.lang.load_more }}</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--    note modal-->
    <Transition>
      <Modal class="sp-modal" :isOpen="isModalOpened" @modal-close="closeModal"
             name="note-modal">
        <template #header class="modal-title">
          <div class="row w-100">
            <div class="col-lg-6">
              <p class="m-0 mt-3">{{ getMixinValue.lang.add_note }}</p>
            </div>
            <div class="col-lg-6 text-end">
              <button @click="closeModal" type="button" class="btn" style="font-size: 15px"><i
                  class="las la-times"></i></button>
            </div>
          </div>
        </template>
        <template #content>
          <div class="modal-body">
            <div class="mb-4">
              <div class="title-mid mb-4">{{ getMixinValue.lang.title }}</div>
              <input type="text" class="sp_modal_text" v-model="data.note.title">
            </div>
            <div>
              <div class="title-mid mb-4">{{ getMixinValue.lang.note }}</div>
              <textarea :placeholder="getMixinValue.lang.add_note" rows="5"
                        v-model="data.note.details"></textarea>
            </div>
          </div>
        </template>
        <template #footer>
          <div class="modal-footer mt-3">
            <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
            <button type="button" v-else class="btn btn-primary btn-lg" @click="addNote">
              {{ getMixinValue.lang.save }}
            </button>
          </div>
        </template>
      </Modal>
    </Transition>
    <!--Tag modal-->
    <Transition>
      <Modal class="sp-modal" :isOpen="isTagModalOpened" @modal-close="closeTagModal"
             name="tag-modal">
        <template #header class="modal-title">
          <div class="row w-100">
            <div class="col-lg-6">
              <p class="m-0 mt-3">{{ getMixinValue.lang.add_tag }}</p>
            </div>
            <div class="col-lg-6 text-end">
              <button @click="closeTagModal" type="button" class="btn" style="font-size: 15px"><i
                  class="las la-times"></i></button>
            </div>
          </div>
        </template>
        <template #content>
          <div class="modal-body">
            <div class="mb-4">
              <div class="title-mid mb-4">{{ getMixinValue.lang.title }}</div>
              <input type="text" class="sp_modal_text" v-model="data.tag.title">
            </div>
          </div>
        </template>
        <template #footer>
          <div class="modal-footer mt-3">
            <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
            <button type="button" v-else class="btn btn-primary btn-lg" @click="addTag">
              {{ getMixinValue.lang.save }}
            </button>
          </div>
        </template>
      </Modal>
    </Transition>
    <!-- Setting modal-->
    <Transition>
      <Modal class="sp-modal" :isOpen="isSettingModalOpened" @modal-close="closeSettingModal"
             name="tag-modal">
        <template #header class="modal-title">
          <div class="row w-100">
            <div class="col-lg-6">
              <p class="m-0 mt-3">{{ getMixinValue.lang.tags }}</p>
            </div>
            <div class="col-lg-6 text-end">
              <button @click="closeSettingModal" type="button" class="btn" style="font-size: 15px"><i
                  class="las la-times"></i></button>
            </div>
          </div>
        </template>
        <template #content>
          <div class="modal-tags-area">
            <div class="select-btn" v-for="(tag, index) in chatRoomStore.client_tags" :key="index">
              <input 
                type="checkbox" 
                :id="'item_' + index" 
                v-model="data.tag_ids" 
                :value="tag.id"
              >
              <label 
                class="single-tag button_select" 
                :for="'item_' + index"
              >
                {{ tag.title }}
              </label>
            </div>
          </div>
        </template>


        <template #footer>
          <div class="modal-footer mt-3">
            <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn>
            <button type="button" v-else class="btn btn-primary btn-lg" @click="storeContactTag">
              {{ getMixinValue.lang.save }}
            </button>
          </div>
        </template>
      </Modal>
    </Transition>
  </div>
</template>

<script setup>
import {onMounted, reactive, watch, ref,computed } from 'vue';
import globalValue from "../mixins/helper.js";
import Modal from "../partials/modal.vue";
const getMixinValue = globalValue();
import loadingBtn from '../partials/loading_btn.vue';
import { useChatRoomStore } from "../stores/chatRoomStore"; // Adjust the path as needed
const chatRoomStore = useChatRoomStore();
const isModalOpened = ref(false);
const isTagModalOpened = ref(false);
const isSettingModalOpened = ref(false);
const isWebChat = ref(false);
const checkedTagIds = computed(() => {
  return chatRoomStore.contact_tags.map(tag => tag.id);
});

const openTagModal = () => {
  isTagModalOpened.value = true;
};
const openModal = () => {
	isModalOpened.value = true;
};
const closeModal = () => {
	isModalOpened.value = false;
};
const closeTagModal = () => {
  isTagModalOpened.value = false;

};
const openSettingModal = () => {
  isSettingModalOpened.value = true;
  // chatRoomStore.fetchContactTags();
  // data.tag_ids = data.active_tags.map(tag => tag.id);

};
const closeSettingModal = () => {
  isSettingModalOpened.value = false;
};
const props = defineProps(['chat_room_id'])

const data = reactive({
  contact: '',
  notes: [],
  tags: [],
  active_tags: [],
  note: {
    title: '',
    details: ''
  },
  tag: {
    title: '',
  },
  tag_ids: [],
  medias: [],
  files: [],
  links: [],
  media_next_page_url: false,
  media_loading: false,
  file_next_page_url: false,
  file_loading: false,
  link_next_page_url: false,
  link_loading: false,
  note_show: true
});

onMounted(() => {
  getUserDetails();
  initializeTagIds();

  isWebChat.value = window.location.href.includes('/client/web-chat')
});

watch(
  () => props.chat_room_id, () => {
  getUserDetails();
  data.medias = [];
  data.files = [];
  data.links = [];
  getSharedMedias();
});
watch(
  () => chatRoomStore.contact_tags,
  () => {
    initializeTagIds();
  },
  { immediate: true } // This ensures it runs immediately on mount
);

function initializeTagIds() {
  const contactTagIds = chatRoomStore.contact_tags.map(tag => tag.tag_id);
  data.tag_ids = chatRoomStore.client_tags
    .filter(tag => contactTagIds.includes(tag.id))
    .map(tag => tag.id);
}

async function addNote() {
  let url = getMixinValue.getUrl('notes');
  getMixinValue.config.loading = true;
  data.note.contact_id = props.chat_room_id;
  await axios.post(url, data.note).then(response => {
    getMixinValue.config.loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      data.notes = response.data.notes;
      closeModal();
      data.note = {
        title: '',
        details: ''
      }
    }
  });
}

async function addTag() {
  const url = getMixinValue.getUrl('tags');
  getMixinValue.config.loading = true;
  data.tag.contact_id = props.chat_room_id;

  try {
    // Await the axios post response
    const response = await axios.post(url, data.tag);

    getMixinValue.config.loading = false; // Set loading to false after the request completes

    if (response.data.error) {
      // Handle error in response
      alert(response.data.error);
      return;
    }

    // Update data.tags and chatRoomStore with the new tags
    data.tags = response.data.tags;
    chatRoomStore.contact_tags = response.data.tags;

    // Extract tag IDs and update data.tag_ids
    const contactTagIds = response.data.tags.map(tag => tag.tag_id);
    data.tag_ids = chatRoomStore.contact_tags
      .filter(tag => contactTagIds.includes(tag.id))
      .map(tag => tag.id);

    // Fetch updated contact tags
    await chatRoomStore.fetchClientTags();

    // Close the tag modal
    closeTagModal();

    // Reset the tag object for the form
    data.tag = {
      title: '',
    };

  } catch (error) {
    // Log error to console and alert user
    console.error('An error occurred while adding the tag:', error);
    alert('An error occurred while adding the tag. Please try again.');
  } finally {
    // Ensure loading state is reset to false in case of error
    getMixinValue.config.loading = false;
  }
}

async function storeContactTag() {
  const url = getMixinValue.getUrl('tags/store/contact-tag');
  
  // Set loading state to true
  getMixinValue.config.loading = true;
  
  const formData = {
    ids: data.tag_ids,             // IDs of selected tags
    contact_id: props.chat_room_id // Current chat room ID
  };
  
  try {
    // Send request to update contact tags
    const response = await axios.post(url, formData);
    
    if (response.data.error) {
      // Show error message if there's an error in response
    
      toastr.error(response.data.error || response.errors.message);
      return;
    }
    // Update the contact tags store with the response data
    chatRoomStore.contact_tags = response.data.tags;
    const contactTagIds = response.data.tags.map(tag => tag.tag_id);
    // Update data.tag_ids to match only those ids present in contactTagIds
    data.tag_ids = chatRoomStore.contact_tags
      .filter(tag => contactTagIds.includes(tag.id))
      .map(tag => tag.id);

    // Close the settings modal upon successful update
    closeSettingModal();
  } catch (error) {
    toastr.error('An error occurred while updating the contact tags. Please try again.');
  } finally {
    // Ensure loading state is reset to false
    getMixinValue.config.loading = false;
  }
}

async function deleteNote(id, index) {
  // Show confirmation dialog
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to recover this note!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  });

  // Check if the user confirmed the deletion
  if (result.isConfirmed) {
    // Hide the note immediately for a smoother UX
    data.notes[index].show = false;
    
    // Add a delay for the UX before actually removing the note
    setTimeout(() => {
      data.notes.splice(index, 1); // Remove note from the local data array
      let url = getMixinValue.getUrl('notes/' + id); // Construct URL
      getMixinValue.config.loading = true;
      
      axios.delete(url, data.note).then(response => {
        getMixinValue.config.loading = false;
        if (response.data.error) {
          toastr.error(response.data.error);
          return;
        }
        Swal.fire(
          'Deleted!',
          'Your note has been deleted.',
          'success'
        );
      }).catch(error => {
        getMixinValue.config.loading = false;
        Swal.fire(
          'Error!',
          'There was an issue deleting your note.',
          'error'
        );
      });
    }, 500); // Optional delay for UX
  }
}

// async function deleteNote(id, index) {
//   data.notes[index].show = false;
//   setTimeout(() => {
//     data.notes.splice(index, 1);
//     let url = getMixinValue.getUrl('notes/' + id);
//     getMixinValue.config.loading = true;
//     axios.delete(url, data.note).then(response => {
//       getMixinValue.config.loading = false;
//       if (response.data.error) {
//         toastr.error(response.data.error);
//         return ;
//       }
//     });
//   }, 500);
// }

async function getUserDetails() {
  let url = getMixinValue.getUrl('contacts-details/' + props.chat_room_id);
  await axios.get(url).then(response => {
    getMixinValue.config.loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      data.contact = response.data.contact;
      data.notes = response.data.notes;
      data.tags = response.data.tags;
      data.active_tags = response.data.tags.filter(tag => tag.status === true);
    }
  });
}

async function getSharedMedias() {
  if (data.medias.length > 0 || getMixinValue.config.loading || data.files.length > 0 || data.links.length > 0) {
    return false;
  }
  let url = getMixinValue.getUrl('shared-files/' + props.chat_room_id);
  let config = {
    params: {
      type: 'media',
    }
  };
  data.media_loading = true;
  await axios.get(url, config).then(response => {
    data.media_loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      data.medias = response.data.files;
      data.media_next_page_url = response.data.next_page_url;
      getSharedFiles();
    }
  });
}

async function getSharedFiles() {
  let url = getMixinValue.getUrl('shared-files/' + props.chat_room_id);
  let config = {
    params: {
      type: 'files',
    }
  };
  data.file_loading = true;
  await axios.get(url, config).then(response => {
    data.file_loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      data.files = response.data.files;
      data.file_next_page_url = response.data.next_page_url;
      getSharedLinks();
    }
  });
}

async function getSharedLinks() {
  let url = getMixinValue.getUrl('shared-files/' + props.chat_room_id);
  let config = {
    params: {
      type: 'links',
    }
  };
  data.link_loading = true;
  await axios.get(url, config).then(response => {
    data.link_loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      data.links = response.data.files;
      data.link_next_page_url = response.data.next_page_url;
    }
  });
}

async function loadSharedMedias() {
  let url = data.media_next_page_url;
  data.media_loading = true;
  let config = {
    params: {
      type: 'media',
    }
  };
  await axios.get(url, config).then(response => {
    data.media_loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      data.medias = data.medias.concat(response.data.files);
      data.media_next_page_url = response.data.next_page_url;
    }
  });
}

async function loadSharedFiles() {
  let url = data.file_next_page_url;
  data.file_loading = true;
  let config = {
    params: {
      type: 'files',
    }
  };
  await axios.get(url, config).then(response => {
    data.file_loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      data.files = data.files.concat(response.data.files);
      data.file_next_page_url = response.data.next_page_url;
    }
  });
}

async function loadSharedLinks() {
  let url = data.link_next_page_url;
  data.link_loading = true;
  let config = {
    params: {
      type: 'links',
    }
  };
  await axios.get(url, config).then(response => {
    data.link_loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      data.links = data.links.concat(response.data.files);
      data.link_next_page_url = response.data.next_page_url;
    }
  });
}

async function deleteFiles(id, index, type) {
  let url = getMixinValue.getUrl('delete-file/' + id);
  hideFile(index, type);
  await axios.delete(url).then(response => {
    data.link_loading = false;
    if (response.data.error) {
      toastr.error(response.data.error);
      return ;
    } else {
      getSharedMedias();
    }
  });
}

function hideFile(index, type) {
  if (type == 'media') {
    data.medias.splice(index, 1);
  } else if (type == 'files') {
    data.files.splice(index, 1);
  }
}
</script>
<style scoped>
.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
  opacity: 0;
}

.note {
  margin-bottom: 20px;
}
</style>