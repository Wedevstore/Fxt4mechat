<script setup>
import { ref, onMounted, reactive } from "vue";
import getId from "../utils/radomId.js";

import SidebarVue from "./sidebarMenu/Sidebar.vue";
import { useVueFlow } from "@vue-flow/core";
import { saveAs } from "file-saver";

import getDateAsString from "../utils/getDateasString";

// Usage of Store Pinia
import { useStore } from "../stores/main.js";
import globalValue from "../mixins/helper.js";
import { useRoute } from "vue-router";
import Modal from "@/src/partials/modal.vue";
import loadingBtn from "../partials/loading_btn.vue";
import VueMultiselect from "vue-multiselect";

const route = useRoute();

const store = useStore();
const contactListSelection = ref([]);
const segmentSelection = ref([]);

function updateContactListIds(selected) {
  console.log('Contact List Selection:', selected); // Debug
  data.contact_list_ids = selected.map(item => item.id);
  console.log('Updated Contact List IDs:', data.contact_list_ids); // Debug
}
function updateSegmentIds(selected) {
  data.segment_ids = selected.map(item => item.id);
}

const { toObject, setNodes, setEdges, setTransform } = useVueFlow();
onMounted(() => {
    if (route.params.id == "create") {
        createStartNode();
    } else {
        getFlowData(route.params.id);
    }
    segments();
    contact_lists();
});
// LocalStorage Saving State
const storeKey = "vueFlowState";
const getMixinValue = globalValue();

async function contact_lists() {
    let url = getMixinValue.getUrl("contacts-lists");
    await axios.get(url).then((response) => {
        data.contact_lists = response.data;
        setPreselectedValues();
    });
} 
async function segments() {
    let url = getMixinValue.getUrl("segments-list");
    await axios.get(url).then((response) => {
        data.segments = response.data;
        setPreselectedValues();

    });
} 

function saveFlow() {
    store.$patch((state) => {
        state.layers.elements = toObject();
    });
    let edges = store.layers.elements.edges;
    let nodes = store.layers.elements.nodes;

    console.log(edges);

    if (nodes.length - edges.length > 1) {
        alert("You must connect all nodes to export.");
        return false;
    }

    let form = {
        name: data.name,
        contact_list_ids: data.contact_list_ids,
        segment_ids: data.segment_ids,
        flow_data: store.layers,
    };
    let url = "";
    if (route.params.id == "create") {
        url = getMixinValue.getUrl("flow-builders");
    } else {
        url = getMixinValue.getUrl("flow-builders/" + route.params.id);
    }
    getMixinValue.config.loading = true;
    axios.post(url, form).then((response) => {
        if (response.data.success) {
      // getMixinValue.config.loading = false;
            localStorage.clear();
            sessionStorage.clear();
            window.location.href = response.data.redirect_url;
        } else {
            getMixinValue.config.loading = false;
            alert(response.data.message);
        }
    });
}

const onRestore = (event, fromImport) => {
    let confirmation = true;

    if (!fromImport) {
        confirmation = confirm("Restoring State.\nAll data not exported will be lost,\nDo you want to continue ?");
    }

    if (confirmation) {
        store.$patch((state) => {
            state.layers = JSON.parse(localStorage.getItem("vueFlowState"));
        });

        const flow = store.layers.elements;

        if (flow) {
            const [x = 0, y = 0] = flow.position;
            setNodes(flow.nodes);
            setEdges(flow.edges);
            setTransform({ x, y, zoom: flow.zoom || 0 });
        }
    }
};

const onExport = () => {
    let edges = store.layers.elements.edges;
    let nodes = store.layers.elements.nodes;

    if (nodes.length - edges.length > 1) {
        alert("You must connect all nodes to export.");
        return false;
    }

    let name = prompt("Export, you can specify a file name (optional) :");

    let blob = new Blob([JSON.stringify(store.layers)], {
        type: "text/plain;charset=utf-8",
    });

    saveAs(blob, name || getDateAsString() + ".txt");
};

////////////////////////////////////////////.
function createStartNode() {
    setTimeout(() => {
        let message = {
            messageToEdit: "",
            elements: {
                nodes: [
                    {
                        id: "starter-boxxn3tn0ycqxdp7izcap2qdj",
                        type: "starter-box",
                        matching_types: "",
                        keyword: "",
                        position: { x: 135, y: 45 },
                        data: {},
                        label: "starter-box node",
                    },
                ],
                edges: [],
                position: [0, 1],
                zoom: 1,
                viewport: { x: 0, y: 0, zoom: 0 },
            },
            messages: [
                {
                    id: "starter-boxxn3tn0ycqxdp7izcap2qdj",
                    type: "starter-box",
                    keyword: "",
                    matching_types: "",
                    label: "Label",
                    title: "Start Bot flow",
                    text: "Text",
                    subtitle: "Subtitle",
                    color: "#000000",
                },
            ],
        };
        localStorage.setItem(storeKey, JSON.stringify(message));
        onRestore(null, true);
    }, 0);
}

function getFlowData(id) {
    let url = getMixinValue.getUrl("flow-builders/" + id + "/edit");
    axios.get(url).then((response) => {
        if (response.data.success) {
            data.name = response.data.flow.name;
            data.contact_list_ids = response.data.flow.contact_list_ids;
            data.segment_ids = response.data.flow.segment_ids;
            setPreselectedValues();

            localStorage.setItem(storeKey, JSON.stringify(response.data.flow.data));
            store.$patch((state) => {
                state.layers = JSON.parse(localStorage.getItem(storeKey));
            });

            const flow = store.layers.elements;

            if (flow) {
                const [x = 0, y = 0] = flow.position;
                setNodes(flow.nodes);
                setEdges(flow.edges);
                setTransform({ x, y, zoom: flow.zoom || 0 });
            }
        } else {
            createStartNode();
        }
    });
}

function setPreselectedValues() {
  contactListSelection.value = data.contact_lists.filter(list => data.contact_list_ids.includes(list.id));
  segmentSelection.value = data.segments.filter(segment => data.segment_ids.includes(segment.id));
}
const onImport = () => {
    if (!importedFile.value) {
        alert("You must select a file to import.");
    } else {
        if (confirm("All data not exported will be lost,\nDo you want to continue ?")) {
            localStorage.setItem(storeKey, importedFile.value);
            onRestore(null, true);
        }
    }
};
////////////////////////////////////////////.

// Function trigered on imported file change.
const fileSelected = (e) => {
    importedFileName.value = e.target.value.replace(/.*[\/\\]/, "");
    reader.readAsText(inputFileOne.value.files[0]);
};
////////////////////////////////////////////.

// Local variables and props declaration.
let inputFileOne = ref(null);
let importedFile = ref(null);
let importedFileName = ref("");
////////////////////////////////////////////.

// Creating a new file reader and registering
// an onload function that store the file content
const reader = new FileReader();
reader.onload = (e) => {
    importedFile.value = e.target.result;
    alert(`File ${importedFileName.value} was uploaded, you can now import its content.`);
};
const isModalOpened = ref(false);
const openModal = () => {
    store.$patch((state) => {
        state.layers.elements = toObject();
    });
    let edges = store.layers.elements.edges;
    let nodes = store.layers.elements.nodes;

    if (nodes.length - edges.length > 1) {
        alert(getMixinValue.lang.you_must_need_to_connect_all_nodes_to_proceed);
        return false;
    }
    isModalOpened.value = true;
};
const closeModal = () => {
    isModalOpened.value = false;
};
const data = reactive({
    name: "",
    segment_ids: [],
    contact_list_ids: [],
    contact_lists: [],
    segments: [],
    selectedContactLists: []
});
</script>

<template>
    <div>
    <div class="flowBuilder__left container rounded" style="position: relative; width: ">
        <SidebarVue></SidebarVue>
        <div class="borders">
            <!--      <button class="btn border rounded" @click="openModal">Save</button>-->
            <!--      <button class="btn border rounded" @click="onRestore">Restore</button>-->
            <!--      <button class="btn border rounded" @click="onExport">Export</button>
      <button class="btn border rounded" @click="onImport">Import</button>-->
        </div>
        <div class="options border file-input">
            <button class="btn sg-btn-primary w-100" @click="openModal">Save</button>
            <!--      <label for="file"
      >{{ importedFileName || "Select file" }}
        <input
            type="file"
            class="file"
            ref="inputFileOne"
            name="inputfile"
            id="inputfile"
            @change="fileSelected"
        />
      </label>-->
        </div>
    </div>
    <Transition>
        <Modal class="sp-modal" :isOpen="isModalOpened" @modal-close="closeModal" name="note-modal">
            <template #header class="modal-title">
                <div class="row w-100">
                    <div class="col-lg-6">
                        <p class="m-0 mt-3">{{ route.params.id == "create" ? getMixinValue.lang.add_flow : getMixinValue.lang.edit_flow }}</p>
                    </div>
                    <div class="col-lg-6 text-end">
                        <button @click="closeModal" type="button" class="btn" style="font-size: 15px"><i class="las la-times"></i></button>
                    </div>
                </div>
            </template> 
            <template #content>
                <div class="modal-body">
                    <div class="mb-4">
                        <div class="title-mid mb-4">{{ getMixinValue.lang.title }}</div>
                        <input type="text" class="sp_modal_text" v-model="data.name" />
                    </div>
                    <div class="mb-4">
                    <label for="labels" class="form-label">{{ getMixinValue.lang.contact_lists }}</label>
                    <VueMultiselect 
                        v-model="contactListSelection" 
                        label="text" 
                        track-by="id" 
                        :multiple="true"
                        :options="data.contact_lists"
                        @input="updateContactListIds">
                      </VueMultiselect>
                    <!-- <select class="form-select form-select-lg mb-3" v-model="data.contact_list_ids" multiple>
                        <option value="" disabled>Select contact lists</option>
                        <option v-for="(list, index) in data.contact_lists" :key="index" :value="list.id">{{ list.text }}</option>
                    </select> -->
                </div>
                <div class="mb-4">
                    <label for="sagement" class="form-label">{{ getMixinValue.lang.segments }}</label>
                    <VueMultiselect 
                v-model="segmentSelection" 
                label="text" 
                track-by="id" 
                :multiple="true"
                :options="data.segments"
                @input="updateSegmentIds">
              </VueMultiselect>
                    <!-- <select class="form-select form-select-lg mb-3" v-model="data.segment_ids" multiple>
                        <option value="" disabled>Select segment</option>
                        <option v-for="(segment, index) in data.segments" :key="index" :value="segment.id">{{ segment.text }}</option>
                    </select> -->
                </div>

                </div>
            </template>
            <template #footer>
                <div class="modal-footer mt-3">
                    <!-- <loadingBtn v-if="getMixinValue.config.loading"></loadingBtn> -->
                    <!-- <button type="button" v-else class="btn btn-primary btn-lg" @click="saveFlow">
                        {{ getMixinValue.lang.save }}
                    </button> -->
                    <button type="button" class="btn btn-primary btn-lg" @click="saveFlow">
                        {{ getMixinValue.lang.save }}
                    </button>
                </div>
            </template>
        </Modal>
    </Transition>
</div>
</template>

<style scoped>
.container {
    overflow-y: scroll;
    overflow-x: hidden;
}

.file-input label:hover {
    transform: scale(1.02);
}

.file-input label {
    display: block;
    position: relative;
    width: auto;
    height: 3rem;
    border-radius: 1rem;
    padding: 1rem;
    background: linear-gradient(40deg, #297cbc, #16d462);
    box-shadow: 0 4px 7px rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: bold;
    transition: transform 0.2s ease-out;
    overflow: hidden;
}

.file {
    opacity: 0%;
    width: 100%;
    height: 100%;
    position: absolute;
    cursor: pointer;
}

.btn {
    font-size: small;
    margin: 2px;
}

.btn:hover {
    background-color: #eee;
}

.options {
    display: flex;
    flex-direction: column;
    align-items: center;
    border-radius: 1rem;
    margin: 0.2rem;
    padding: 0.5rem;
}
</style>
