<script setup>
import { ref, computed, watch } from "vue";
import { Handle, Position } from "@vue-flow/core";
import getId from "../utils/radomId";

// Importing Store Pinia
import { useStore } from "../stores/main.js";
import messageRendererVue from "./messageRenderer.vue";
import TrashIcon from "../assets/svg/TrashIcon.svg";

// Custom Top Menu import
import topMenu from "./topMenu.vue";
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();

// Local variables and props declaration
const transparent = ref(true);
const selectedColor = ref(false);
const props = defineProps([
    'id',
    'selected',
    'current_id',
    'button_message',
    'button1_text',
    'button2_text',
    'button3_text',
    'duration',
    'data',
    'buttons',
]);

// Usage of Store Pinia
const store = useStore();
// console.log(props.id);

// Computed Values from Store.
const localStates = computed(() => store.getMessageById(props.id));
// console.log(localStates.value);
const Items = computed(() => localStates.value.items);
const localItems = computed(() => store.getItemById(props.id));
const localButtons = computed(() => localItems.value?.buttons || []);

// Ensure there are always 3 buttons
watch(localItems, (newItems) => {
    if (newItems && (!newItems.buttons || newItems.buttons.length === 0)) {
        newItems.buttons = [
            { id: getId(), text: "Button 1" },
            { id: getId(), text: "Button 2" },
            { id: getId(), text: "Button 3" },
        ];
    }
}, { immediate: true });

// Value update related methods are defined here
const updateValues = (event, button_id) => {
    const button = localButtons.value.find(element => element.id == button_id);
    if (button) {
        button.text = event.target.innerText || "Enter Text";
    } else {
        console.error('Button not found:', button_id);
    }
};

// Elements related methods
const deleteElement = (id) => {
    localStates.value.items = Items.value.filter(element => element.id != id);
};

// Buttons related methods
const deleteButton = (id) => {
    localItems.value.buttons = localButtons.value.filter(element => element.id != id);
};

const insertButton = () => {
    if (!localButtons.value) {
        localButtons.value = [];
    }
    localButtons.value.push({
        id: getId(),
        text: `New Button ${localButtons.value.length + 1}`,
    });
};

const textarea = ref(null); // Access the textarea by its ref.

// Watching Selected Manual event
watch(
    () => props.selected,
    (isSelected) => (selectedColor.value = isSelected)
);

watch(
    () => props.button_message,
    () => {
        if (props.id === props.current_id) {
            localStates.value.button_message = props.button_message;
            localStates.value.button1_text = props.button1_text;
            localStates.value.button2_text = props.button2_text;
            localStates.value.button3_text = props.button3_text;
            localStates.value.duration = props.duration;
            localStates.value.buttons = props.buttons;
        }
    }
);

const emit = defineEmits(["data-sent"]);
function handleData() {
    emit("data-sent", { args: localStates });
}

const calculateHandleTop = (index) => {
    return 60 + index * 15 + '%';
};

function calculateHandlesCount(buttons) {
    return buttons.length;
}
</script>

<template>
    <div>
    <Handle id="left" class="handle" type="target" :position="Position.Left" />
    <div @mouseenter="transparent = false" @mouseleave="transparent = true"
        class="d-flex flex-column align-items-center">
        <!-- Delete Button and color controls -->
        <topMenu :eid="props.id" :transparent="transparent"></topMenu>
        <!-- Delete Button and color controls -->
        <div data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" @click="handleData" class="main-container"
            :style="{
                border: selectedColor ? '3px red solid' : `3px red solid`,
            }">
            <div class="content">
                <div class="card" style="width: 18rem; text-align: center;">
                    <div class="card-body">
                        <p class="card-title">{{ getMixinValue.lang.button_message }}</p>
                        <div>
                            <textarea class="bubble" ref="textarea" :class="{ 'with-buttons': localButtons.length > 0 }">
                            </textarea>
                            <!-- Button Poped to request delete element -->
                            <div class="button-container" :class="{ transparent: transparent }"
                                style="position: absolute; top: 50%; right: -2.2rem" @click="deleteElement(props.id)">
                                <TrashIcon />
                            </div>
                            <!-- Button Poped to request delete element -->

                            <div class="d-flex flex-column justify-content-center align-items-center"
                                style="width: 100%">
                                <!-- Button template : Insert and render -->
                                <!-- Button render from localButtons -->
                                <div v-for="button in localButtons" :key="button.id" class="button" style="position: relative">
                                    <div :id="button.id + 'button'" contenteditable="true" @input="(event) => {
                                            updateValues(event, button.id);
                                        }">
                                        {{ button.text }}
                                    </div>
                                    <Handle :id="button.id + 'right'" class="handle" type="source"
                                        :position="Position.Right" style="top: 1.4rem; left: 100% !important" />
                                    <div class="button-container" @click="deleteButton(button.id)"
                                        style="position: absolute; right: 0">
                                        <TrashIcon />
                                    </div>
                                </div>
                                <!-- Button render from localButtons -->
                                <div class="button" @click="insertButton" v-if="localButtons.length < 3">
                                    Insert Button
                                </div>
                                <!-- Button template : Insert and render -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</template>
