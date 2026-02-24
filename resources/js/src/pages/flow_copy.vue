<script setup>
import { onMounted, reactive, ref } from "vue";
import { VueFlow, useVueFlow } from "@vue-flow/core";
import { Background } from "@vue-flow/background";
import { Controls } from "@vue-flow/controls";
import { MiniMap } from "@vue-flow/minimap";
import { initialElements } from "../assets/initial-elements";
import VueMultiselect from "vue-multiselect";
import boxWithTitleVue from "../components/boxWithTitle.vue";
import boxWithStarter from "../components/boxWithStarter.vue";
import imageContainerVue from "../components/imageContainer.vue";
import boxWitAudioVue from "../components/boxWithAudio.vue";
import boxWitVideoVue from "../components/boxWithVideo.vue";
import boxWithFileVue from "../components/boxWithFile.vue";
import boxTemplate from "../components/boxTemplate.vue";
import boxLocation from "../components/boxWithLocation.vue";
import boxCondition from "../components/boxWithCondition.vue";
import boxWithInteractive from "../components/boxWithInteractive.vue";
import boxWithButton from "../components/boxWithButton.vue";
import globalMenuVue from "../components/globalMenu.vue";
import boxWithInteractiveButton from "../components/boxWithInteractiveButton.vue";
import boxWithInteractiveRow from "../components/boxWithInteractiveRow.vue";
import boxWithInteractiveSection from "../components/boxWithInteractiveSection.vue";
import facebookMessage from "../components/facebookMessage.vue";
import quickReply from "../components/quickReply.vue";

// Custom Connection line and Custom Edge
import redirectorEdgeVue from "../components/redirectorEdge.vue";
import CustomConnectionLine from "../components/CustomConnectionLine.vue";
import customEdgeVue from "../components/customEdge.vue";
import { createVueNode } from "../utils/createVueNode";
////////////////////////////////////////////.
// Usage of Store Pinia
import { useStore } from "../stores/main.js";
import globalValue from "../mixins/helper.js";
import getId from "./../utils/radomId.js";
const getMixinValue = globalValue();
const store = useStore();
const { addEdges, addNodes, onConnect, project, setInteractive, onConnectStart } = useVueFlow();

onConnectStart((event) => {
    console.log(event);
    let handle_id = event.handleId;
    if (handle_id == "list_messages") {
        let create_event = event.event;
        let btn_node = createVueNode(create_event, addNodes, project, store, "box-with-interactive-button");
        let section_node = createVueNode({ clientX: btn_node.position.x + 800, clientY: btn_node.position.y }, addNodes, project, store, "box-with-interactive-section");
        let row_node_1 = createVueNode({ clientX: section_node.position.x + 800, clientY: section_node.position.y }, addNodes, project, store, "box-with-interactive-row");
        let row_node_2 = createVueNode({ clientX: section_node.position.x + 800, clientY: section_node.position.y + 100 }, addNodes, project, store, "box-with-interactive-row");
        let row_node_3 = createVueNode({ clientX: section_node.position.x + 800, clientY: section_node.position.y + 200 }, addNodes, project, store, "box-with-interactive-row");
        addEdges([{
            animated: false,
            source: event.nodeId,
            sourceHandle: event.handleType,
            target: btn_node.id,
            targetHandle: "left",
            type: "custom",
        }]);
        addEdges([{
            animated: false,
            source: btn_node.id,
            sourceHandle: "source",
            target: section_node.id,
            targetHandle: "left",
            type: "custom",
        }]);
    }
});
const onDragOver = (event) => {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = "move";
    }
};
const onDrop = (event) => {
    createVueNode(event, addNodes, project, store);
};
onConnect((params) => {
    (params.type = "custom"), (params.animated = false);
    addEdges([params]);
});
const onClick = (event) => {
    if (event.node.type == "facebook-message") {
        if (messageToEdit.value == event.node.id) {
            messageToEdit.value = "";
        } else {
            messageToEdit.value = event.node.id;
        }
    }
    store.messageToEdit = messageToEdit.value;
};
let onKeyUp = (event) => {
    switch (event.key) {
        case "AltGraph":
            setInteractive(true);
            break;

        // Close the editor if Escape key is pressed
        case "Escape":
            messageToEdit.value = "";
            break;

        default:
            break;
    }
};

let onKeyDown = (event) => {
    switch (event.key) {
        case "AltGraph":
            setInteractive(false);
            break;

        default:
            break;
    }
};

onMounted(() => {
    templates();
    window.addEventListener("keydown", onKeyDown);
    window.addEventListener("keyup", onKeyUp);
});
////////////////////////////////////////////.

// Local variables and props declaration.
let messageToEdit = ref("");
const elements = ref(initialElements);

const onChange = (event) => {
    event.forEach((element) => {
        if (element.type == "remove") {
            store.layers.messages = store.layers.messages.filter((item) => {
                return item.id != element.id;
            });
        }
    });
};

async function templates() {
    let url = getMixinValue.getUrl("whatsapp-templates?flow_builder=1");
    await axios.get(url).then((response) => {
        if (response.data.success) {
            data.templates = response.data.templates;
        }
    });
}

////////////////////////////////////////////.
const data = reactive({
    opened: false,
    keyword: "",
    matching_types: "",
    text: "",
    description: "",
    text_duration: "",
    current_id: "",
    type: "",
    image: "",
    file_duration: "",
    image_duration: "",
    audio: "",
    audio_duration: "",
    video_duration: "",
    video: "",
    location_duration: "",
    latitude: "",
    longitude: "",
    template_id: "",
    template_variables: {},
    variables: [],
    templates: [],
    contact_lists: [],
    segments: [],
    box_title: "",
    match_type: "",
    header_text: "",
    header_media_type: "",
    header_media: "",
    text_message: "",
    footer_text: "",
    button_text: "",
    button1_text: "",
    button2_text: "",
    button3_text: "",
    button_message: "",
    original_file_name: "",
    file_extension: "",
    buttons: [],
    condition_fields: [
        {
            variable: "",
            operator: "",
            value: "",
        }
    ],
    action: [
        {
            button: '',
            sections:
            {
                title: '',
                rows: [
                    {
                        id: '',
                        title: '',
                        description: ''
                    }
                ]
            }

        }
    ],
    condition_variable_options: [
        {
            label: "First Name",
            value: "first_name",
        },
        {
            label: "Last Name",
            value: "last_name",
        },
        {
            label: "Label",
            value: "label",
        },
        {
            label: "Email",
            value: "email",
        },
        {
            label: "Phone Number",
            value: "phone_number",
        },
    ],
    condition_operator_options: [
        {
            label: "=",
            value: "equal",
        },
        {
            label: "<",
            value: "less_than",
        },
        {
            label: ">",
            value: "greater_than",
        },
        {
            label: "≤",
            value: "less_than_or_equal",
        },
        {
            label: "≥",
            value: "greater_than_or_equal",
        },
        {
            label: "≠",
            value: "not_equal",
        },
        {
            label: "Contains",
            value: "contains",
        },
        {
            label: "Starts With",
            value: "starts_with",
        },
        {
            label: "Ends With",
            value: "ends_with",
        },
        {
            label: "Has Value",
            value: "has_value",
        },
    ],
});


function addSection() {
    if (!data.action.sections) {
        // $set(data.action, 'sections', []);

        data.action, 'sections', [];

    }
    if (data.action.sections.length < 10) {
        data.action.sections.push({
            title: '',
            rows: [{ id: '', title: '', description: '' }]
        });
    }
}

function removeSection(index) {
    data.action.sections.splice(index, 1);
}

function addSectionRow(sectionIndex) {
    if (data.action.sections[sectionIndex].rows.length < 10) {
        data.action.sections[sectionIndex].rows.push({
            id: '',
            title: '',
            description: ''
        });
    }
}

function removeSectionRow(sectionIndex, rowIndex) {
    data.action.sections[sectionIndex].rows.splice(rowIndex, 1);
}

function handleData(args) {
    let localStates = args.args.value;
    data.type = localStates.type;
    data.current_id = localStates.id;
    data.box_title = localStates.title;

    if (data.type == "starter-box") {
        data.keyword = localStates.keyword;
        data.matching_types = localStates.matching_types;
    }

    else if (data.type == "box-with-title") {
        data.text = localStates.text;
        data.text_duration = localStates.text_duration;
    }

    else if (data.type == "node-image") {
        data.image = localStates.image;
        data.image_duration = localStates.image_duration;
    }
    else if (data.type == "box-with-audio") {
        data.audio = localStates.audio;
        data.audio_duration = localStates.audio_duration;
        const audio_element = document.getElementById("audio");
        if (audio_element) {
            audio_element.src = data.audio;
        }
    } else if (data.type == "box-with-video") {
        data.video = localStates.video;
        data.video_duration = localStates.video_duration;
        const video_element = document.getElementById("video");
        if (video_element) {
            video_element.src = data.video;
        }
    } else if (data.type == "box-with-file") {
        data.file = localStates.file;
        data.file_duration = localStates.file_duration;
    } else if (data.type == "box-with-location") {
        data.latitude = localStates.latitude;
        data.longitude = localStates.longitude;
        data.location_duration = localStates.location_duration;
    } else if (data.type == "box-with-template") {
        data.template_id = localStates.template_id;
    } else if (data.type == "box-with-condition") {
        data.match_type = localStates.match_type;
        data.condition_fields = localStates.condition_fields;
    } else if (data.type == "box-with-list") {
        data.header_type = localStates.header_type;
        data.header_text = localStates.header_text;
        data.header_media_type = localStates.header_media_type;
        data.header_media = localStates.header_media;
        data.text_message = localStates.text_message;
        data.footer_text = localStates.footer_text;
        // data.action = localStates.action;
    }
    else if (data.type == "box-with-button") {

        data.button_message = localStates.button_message;
        data.buttons 		= localStates.buttons ?? [];
        data.button1_text 	= localStates.button1_text;
        data.button2_text 	= localStates.button2_text;
        data.button3_text 	= localStates.button3_text;
        data.duration 		= localStates.duration ;
    }
}

async function handleFile(event, type) {
    let file = event.target.files[0];
    await uploadFile(file, type);
}

async function uploadFile(file, type) {
    let config = {
        headers: {
            "Content-Type": "multipart/form-data; charset=utf-8; boundary=" + Math.random().toString().substr(2),
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
    };
    let form_data = new FormData();
    form_data.append("file", file);
    form_data.append("id", data.current_id);
    form_data.append("type", data.type);
    let url = getMixinValue.getUrl("upload-files");
    await axios
        .post(url, form_data, config)
        .then((response) => {
            if (type == "audio") {
                data.audio = response.data.file_object.file;
                const audio_element = document.getElementById("audio");
                const audio_container = document.getElementById("audio_container");
                audio_element.src = response.data.file_object.file;
                audio_container.src = response.data.file_object.file;
            } else if (type == "video") {
                data.video = response.data.file_object.file;
                const video_element = document.getElementById("video");
                const video_container = document.getElementById("video_container");
                video_element.src = response.data.file_object.file;
                video_container.src = response.data.file_object.file;
            } else {
                data[type] = response.data.file_object.file;
                data.file_extension = response.data.file_object.ext;
            }
        })
        .catch((error) => { });
}
function addRow() {
    data.condition_fields.push({
        variable: "",
        operator: "",
        value: "",
    });
}
function removeRow() {
    data.condition_fields.pop();
}

function addButton() {
  if (!Array.isArray(data.buttons)) {
    data.buttons = []; // Initialize as an empty array if not already an array
  }
  if (data.buttons.length < 2) {
    data.buttons.push({ text: '' }); // Add a new button object with empty text
  }
}

// Method to remove a button from data.buttons array
function removeButton(index) {
    data.buttons.splice(index, 1); // Remove button at specified index
}
</script>

<template>
    <div>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasRightLabel">
                    Configure <span>{{ data.box_title }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">

                {{ data.type }}

                <div v-if="data.type == 'starter-box'">
                    <div class="trigger-trigger-keyword-group trigger-onDemand-field form-group text-left mt-2">
                        <label for="trigger-trigger-keyword"> Write down the keywords for which the bot will bet
                            triggered </label>
                        <input type="text" class="form-control" id="trigger-trigger-keyword"
                            aria-describedby="trigger-trigger-keyword-help"
                            :placeholder="getMixinValue.lang.hello_hi_start" v-model="data.keyword" />
                    </div>
                    <div class="card_content">
                        <div class=" form-group">
                            <label for="matching_type" class="d-block">{{ getMixinValue.lang.matching_type }}<span
                                    class="text-danger">*</span></label>
                            <div class="flex_input p-0" style="border: none;">
                                <div class="radio_button">
                                    <input type="radio" name="matching_types" id="exacts" value="exacts"
                                        v-model="data.matching_types" checked />
                                    <label class="" for="exacts">
                                        {{ getMixinValue.lang.exact_keyword_match }}
                                    </label>
                                </div>
                                <div class="radio_button">
                                    <input type="radio" name="matching_types" id="contains" value="contains"
                                        v-model="data.matching_types" />
                                    <label class="" for="contains">
                                        {{ getMixinValue.lang.contain }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="data.type == 'box-with-title'">
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.duration }} ({{ getMixinValue.lang.in_sec }})</label>
                        <input type="text" class="form-control" :placeholder="getMixinValue.lang.e_g_10"
                            v-model="data.text_duration" />
                    </div>
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.text }}</label>
                        <textarea name="" class="form-control" id="" :placeholder="getMixinValue.lang.text"
                            v-model="data.text"></textarea>
                    </div>
                </div>
                <div v-else-if="data.type == 'node-image'">
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.duration }} ({{ getMixinValue.lang.in_sec }})</label>
                        <input type="text" class="form-control" :placeholder="getMixinValue.lang.e_g_10"
                            v-model="data.image_duration" />
                    </div>
                    <div class="form-group">
                        <label for="">Image</label>
                        <div class="file_upload_text">
                            <input type="file" accept="image/*" class="form-control"
                                @change="handleFile($event, 'image')" />
                        </div>
                        <div>
                            <img :src="data.image" class="image" />
                        </div>
                    </div>
                </div>
                <div v-else-if="data.type == 'box-with-audio'">
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.duration }} ({{ getMixinValue.lang.in_sec }})</label>
                        <input type="text" class="form-control" placeholder="{{ getMixinValue.lang.e_g_10 }}"
                            v-model="data.audio_duration" />
                    </div>
                    <div class="form-group">
                        <label for="">Audio</label>
                        <div class="file_upload_text">
                            <input type="file" accept="audio/*" class="form-control"
                                @change="handleFile($event, 'audio')" />
                        </div>
                        <div style="margin-top: 20px" v-if="data.audio">
                            <p style="margin: 0">Preview</p>
                            <vue-plyr>
                                <audio id="audio">
                                    <source :src="data.audio" type="audio/mp3" />
                                </audio>
                            </vue-plyr>
                        </div>
                    </div>
                </div>
                <div v-else-if="data.type == 'box-with-video'">
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.duration }} ({{ getMixinValue.lang.in_sec }})</label>
                        <input type="text" class="form-control" :placeholder="getMixinValue.lang.e_g_10"
                            v-model="data.video_duration" />
                    </div>
                    <div class="form-group">
                        <label for="">Video</label>
                        <div class="file_upload_text">
                            <input type="file" accept="video/*" class="form-control"
                                @change="handleFile($event, 'video')" />
                        </div>
                        <div style="margin-top: 20px" v-if="data.video">
                            <p style="margin: 0">Preview</p>
                            <vue-plyr>
                                <video id="video">
                                    <source :src="data.video" type="video/mp4" />
                                </video>
                            </vue-plyr>
                        </div>
                    </div>
                </div>
                <div v-else-if="data.type == 'box-with-file'">
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.duration }} ({{ getMixinValue.lang.in_sec }})</label>
                        <input type="text" class="form-control" :placeholder="getMixinValue.lang.e_g_10"
                            v-model="data.file_duration" />
                    </div>
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.file }}</label>
                        <div class="file_upload_text">
                            <input type="file" class="form-control" @change="handleFile($event, 'file')" />
                        </div>
                        <div style="margin-top: 20px" v-if="data.file">
                            <p style="margin: 0">{{ getMixinValue.lang.preview }}</p>
                            <iframe id="iframe" :src="data.file"></iframe>
                        </div>
                    </div>
                </div>
                <div v-if="data.type == 'box-with-list'">
                    <div class="">
                        <div class="mb-3">
                            <label>{{ getMixinValue.lang.duration }} ({{ getMixinValue.lang.in_sec }})</label>
                            <input type="text" class="form-control" :placeholder="getMixinValue.lang.e_g_10"
                                v-model="data.interactive_duration">
                        </div>
                        <div class="mb-3">
                            <label>{{ getMixinValue.lang.header_text }}</label>
                            <input type="text" class="form-control" :placeholder="getMixinValue.lang.header_text"
                                v-model="data.header_text">
                        </div>
                        <div class="mb-3">
                            <label>{{ getMixinValue.lang.message_body }}</label>
                            <textarea class="form-control" :placeholder="getMixinValue.lang.message_body"
                                v-model="data.text_message"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>{{ getMixinValue.lang.message_footer }} ({{ getMixinValue.lang.optional }})</label>
                            <input type="text" class="form-control" v-model="data.footer_text"
                                :placeholder="getMixinValue.lang.message_footer + ' (' + getMixinValue.lang.optional + ')'">
                        </div>
                        <div class="mb-3">
                            <label>{{ getMixinValue.lang.button_text }}</label>
                            <input type="text" class="form-control" v-model="data.button_text"
                                :placeholder="getMixinValue.lang.button_text">
                        </div>
                        <div class="border-bottom">{{ getMixinValue.lang.action }}</div>

                        <div v-for="(section, sIndex) in data.action.sections" :key="sIndex">
                            <div class="mb-3">
                                <label>{{ getMixinValue.lang.section_title }}</label>
                                <input type="text" class="form-control" v-model="section.title"
                                    :placeholder="getMixinValue.lang.section_title">
                            </div>
                            <div v-for="(row, rIndex) in section.rows" :key="rIndex">
                                <div class="mb-3">
                                    <label>{{ getMixinValue.lang.row_title }}</label>
                                    <input type="text" class="form-control" v-model="row.title"
                                        :placeholder="getMixinValue.lang.row_title">
                                </div>
                                <div class="mb-3">
                                    <label>{{ getMixinValue.lang.row_description }}</label>
                                    <input type="text" class="form-control" v-model="row.description"
                                        :placeholder="getMixinValue.lang.row_description">
                                </div>
                                <button class="btn btn-sm" @click="removeSectionRow(sIndex, rIndex)">Remove Row</button>
                            </div>
                            <button class="btn btn-sm" @click="addSectionRow(sIndex)">Add Row</button>
                            <button class="btn btn-sm" @click="removeSection(sIndex)">Remove Section</button>
                        </div>
                        <button class="btn btn-sm" @click="addSection">Add Section</button>
                    </div>
                </div>

                <div v-else-if="data.type == 'box-with-button'">
                    <div class="">
                        <div class="mb-3">
                            <label for="">{{ getMixinValue.lang.button_message }}</label>
                            <textarea name="message" class="form-control" id="message"
                                :placeholder="getMixinValue.lang.button_message"
                                v-model="data.button_message"></textarea>
                        </div>

                        <div class="mb-3">
                            <label>{{ getMixinValue.lang.button1_text }}</label>
                            <input type="text" class="form-control" :placeholder="getMixinValue.lang.button1_text"
                                v-model="data.button1_text">
                        </div>

                        <div class="mb-3">
                            <label>{{ getMixinValue.lang.button2_text }}</label>
                            <input type="text" class="form-control" :placeholder="getMixinValue.lang.button2_text"
                                v-model="data.button2_text">
                        </div>

                        <div class="mb-3">
                            <label>{{ getMixinValue.lang.button3_text }}</label>
                            <input type="text" class="form-control" :placeholder="getMixinValue.lang.button3_text"
                                v-model="data.button3_text">
                        </div>

                        <!--<div v-for="(button, index) in data.buttons" :key="index" class="mb-3">
                            <label>{{ getMixinValue.lang['button' + (index + 1) + '_text'] }}</label>
                            <input type="text" class="form-control" :placeholder="getMixinValue.lang.button_text"
                                v-model="button.text">
                            <button class="btn btn-sm btn-danger mt-2 text-white" @click="removeButton(index)">Remove
                                Button</button>
                        </div>-->
                        <button class="btn btn-sm btn-primary mt-2" @click="addButton">Add Button</button>
                    </div>
                </div>

                <div v-else-if="data.type == 'box-with-location'">
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.duration }} ({{ getMixinValue.lang.in_sec }})</label>
                        <input type="text" class="form-control" :placeholder="getMixinValue.lang.e_g_10"
                            v-model="data.location_duration" />
                    </div>
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.latitude }}</label>
                        <input type="text" v-model="data.latitude" class="form-control"
                            :placeholder="getMixinValue.lang.latitude" />
                    </div>
                    <div class="form-group">
                        <label for="">{{ getMixinValue.lang.longitude }}</label>
                        <input type="text" v-model="data.longitude" class="form-control"
                            :placeholder="getMixinValue.lang.longitude" />
                    </div>
                </div>

                <div v-else-if="data.type == 'box-with-condition'">
                    <label>Match Type</label>
                    <VueMultiselect v-model="data.match_type" label="label" track-by="value" :options="[
                        { value: 'all_match', label: 'All Match' },
                        { value: 'any_match', label: 'Any Match' },
                    ]">
                    </VueMultiselect>

                    <div>
                        <p class="mt-3 mb-1">Fields</p>
                        <div class="flex_input" v-for="(field, index) in data.condition_fields" :key="index">
                            <div class="form-group">
                                <label for="">Variable</label>
                                <VueMultiselect v-model="field.variable" label="label" track-by="value"
                                    :options="data.condition_variable_options"> </VueMultiselect>
                            </div>
                            <div class="form-group">
                                <label for="">Operator</label>
                                <VueMultiselect v-model="field.operator" label="label" track-by="value"
                                    :options="data.condition_operator_options"> </VueMultiselect>
                            </div>
                            <div class="form-group">
                                <label for="">Value</label>
                                <input type="text" class="form-control" v-model="field.value" />
                            </div>
                            <div class="form-group">
                                <label for="">Action</label>
                                <div class="action_btn">
                                    <a v-if="index > 0" href="javascript:void(0)" @click="removeRow"><i
                                            class="las la-minus"></i></a>
                                    <a v-else href="javascript:void(0)" @click="addRow"><i class="las la-plus"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Variable</th>
                                <th>Operator</th>
                                <th>Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(field, index) in data.condition_fields" :key="index">
                                <td>
                                    <VueMultiselect v-model="field.variable" label="label" track-by="value"
                                        :options="data.condition_variable_options"> </VueMultiselect>
                                </td>
                                <td>
                                    <VueMultiselect v-model="field.operator" label="label" track-by="value"
                                        :options="data.condition_operator_options"> </VueMultiselect>
                                </td>
                                <td>
                                    <input type="text" class="form-control" v-model="field.value" />
                                </td>
                                <td>
                                    <a v-if="index > 0" href="javascript:void(0)" @click="removeRow"><i
                                            class="las la-minus"></i></a>
                                    <a v-else href="javascript:void(0)" @click="addRow"><i class="las la-plus"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!--        <div class="form-group" v-else-if="data.type == 'box-with-template'">
          <label>Template</label>
          <select class="form-control" v-model="data.template_id">
            <option value="">Select Template</option>
            <option v-for="(template, index) in data.templates" :key="index" :value="template.id">{{ template.category }} => {{ template.name }}</option>
         
          </select>
          <div v-if="data.variables.length > 0">
            <div class="form-group" v-for="(variable, index) in data.variables" :key="index">
              <label>{{ variable }}</label>
              <input type="text" v-model="data.template_variables[variable]" class="form-control">
            </div>
          </div>
        </div>-->
            </div>
            <div class="offcanvas-footer">
                <div class="offcanvas-button">
                    <button data-bs-dismiss="offcanvas" class="btn sg-btn-primary w-100">Save</button>
                </div>
            </div>
        </div>
        <div id="allTheNav">
            <div class="flowBuilder__inner d-flex" style="height: 100vh">
                <globalMenuVue></globalMenuVue>
                <div class="m-1 border" id="vue_flow" oncontextmenu="return false;" style="position: relative">
                    <VueFlow v-model="elements" class="customnodeflow" :snap-to-grid="true" :select-nodes-on-drag="true"
                        :only-render-visible-elements="true" :default-viewport="{ zoom: 0.5 }" :max-zoom="50"
                        :min-zoom="0.05" @dragover="onDragOver" @drop="onDrop" @nodeDoubleClick="onClick"
                        @nodesChange="onChange">
                        <Background pattern-color="grey" gap="16" size="1.2" />

                        <template #connection-line="{ sourceX, sourceY, targetX, targetY }">
                            <CustomConnectionLine :source-x="sourceX" :source-y="sourceY" :target-x="targetX"
                                :target-y="targetY" />
                        </template>
                        <template #node-facebook-message="props">
                            <div>Slot ID: {{ props.id }}, Slot Selected: {{ props.selected }}</div>

                            <facebookMessage :id="props.id" :selected="props.selected" />
                        </template>
                        <template #edge-custom="props">
                            <customEdgeVue v-bind="props" />
                        </template>
                        <template #node-redirector="props">
                            <redirectorEdgeVue v-bind="props" />
                        </template>
                        <template #node-starter-box="props">
                            <boxWithStarter @data-sent="handleData" :id="props.id" :text="data.text"
                                :selected="props.selected" :keyword="data.keyword" :matching_types="data.matching_types"
                                :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-title="props">
                            <boxWithTitleVue @data-sent="handleData" :id="props.id" :selected="props.selected"
                                :duration="data.text_duration" :text="data.text" :current_id="data.current_id" />
                        </template>
                        <template #node-node-image="props">
                            <imageContainerVue @data-sent="handleData" :id="props.id" :selected="props.selected"
                                :duration="data.image_duration" :image="data.image" :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-audio="props">
                            <boxWitAudioVue @data-sent="handleData" :id="props.id" :selected="props.selected"
                                :duration="data.audio_duration" :audio="data.audio" :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-video="props">
                            <boxWitVideoVue @data-sent="handleData" :id="props.id" :selected="props.selected"
                                :duration="data.video_duration" :video="data.video" :current_id="data.current_id" />
                        </template>

                        <template #node-quick-reply="props">
                            <quickReply :id="props.id" :selected="props.selected" />
                        </template>

                        <template #node-box-with-file="props">
                            <boxWithFileVue @data-sent="handleData" :id="props.id" :selected="props.selected"
                                :duration="data.file_duration" :file="data.file" :extension="data.file_extension"
                                :current_id="data.current_id" />
                        </template>

                        <template #node-box-with-location="props">
                            <boxLocation @data-sent="handleData" :id="props.id" :selected="props.selected"
                                :duration="data.location_duration" :latitude="data.latitude" :longitude="data.longitude"
                                :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-template="props">
                            <boxTemplate @data-sent="handleData" :id="props.id" :selected="props.selected"
                                :template_id="data.template_id" :template_variables="data.template_variables"
                                :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-condition="props">
                            <boxCondition @data-sent="handleData" :id="props.id" :selected="props.selected"
                                :current_id="data.current_id" :match_type="data.match_type"
                                :condition_fields="data.condition_fields" />
                        </template>
                        <template #node-box-with-list="props">
                            <boxWithInteractive @data-sent="handleData" :id="props.id" :duration="data.duration"
                                :selected="props.selected" :header_text="data.header_text"
                                :header_type="data.header_type" :header_media="data.header_media"
                                :text_message="data.text_message" :footer_text="data.footer_text"
                                :button_text="data.button_text" :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-interactive-button="props">
                            <box-with-interactive-button @data-sent="handleData" :id="props.id" :text="data.text"
                                :selected="props.selected" :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-interactive-section="props">
                            <boxWithInteractiveSection @data-sent="handleData" :id="props.id" :text="data.text"
                                :selected="props.selected" :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-interactive-row="props">
                            <box-with-interactive-row @data-sent="handleData" :id="props.id" :text="data.text"
                                :description="data.description" :selected="props.selected"
                                :current_id="data.current_id" />
                        </template>
                        <template #node-box-with-button="props">
                            <boxWithButton :id="props.id" :duration="data.duration"
                                :selected="props.selected" :button1_text="data.button1_text" :buttons="data.buttons"
                                :button2_text="data.button2_text" :button3_text="data.button3_text"
                                :button_message="data.button_message" :current_id="data.current_id" />
                        </template>

                        <Controls />
                        <MiniMap v-show="messageToEdit === ''" />
                    </VueFlow>
                </div>
            </div>
        </div>
    </div>
</template>
