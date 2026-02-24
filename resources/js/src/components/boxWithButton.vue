<script setup>
import { ref, computed, watch } from "vue";
import { Handle, Position } from "@vue-flow/core";
import getId from "../utils/radomId";
import { useStore } from "../stores/main.js";
import topMenu from "./topMenu.vue";
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();
const transparent = ref(true);
const selectedColor = ref(false);
const props = defineProps(['id','selected','current_id','header_text','footer_text','button_message','button_duration','data','buttons','items']);

console.log(props);


// Usage of Store Pinia
const store = useStore();
// Computed Values from Store.
const localStates = computed(() => store.getMessageById(props.id));
const Items = computed(() => localStates.value.items);
const localItems = computed(() => store.getItemById(props.id));
const localButtons = Items;
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

console.log(props.id);
console.log(props.current_id);

const textarea = ref(null); // Access the textarea by its ref.
// Watching Selected Manual event
watch(
    () => props.selected,
    (isSelected) => (selectedColor.value = isSelected)
);
watch(
    () => [props.button_message, props.header_text, props.footer_text, props.button_duration],
    () => {
        if (props.id === props.current_id) {
            localStates.value.button_message = props.button_message;
            localStates.value.button_duration = props.button_duration;
            localStates.value.buttons = props.buttons;
            localStates.value.items = props.items;
            localStates.value.footer_text = props.footer_text;
            localStates.value.header_text = props.header_text;
        }
    }
);

const emit = defineEmits(["data-sent"]);
function handleData() {
    emit("data-sent", { args: localStates });
}
</script>
<template>
    <Handle id="left" class="handle" type="target" :position="Position.Left" />
    <div @mouseenter="transparent = false" @mouseleave="transparent = true"
        class="d-flex flex-column align-items-center">
        <topMenu :eid="props.id" :transparent="transparent"></topMenu>
        <div data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" @click="handleData" class="main-container"
            :style="{
                border: selectedColor ? '3px red solid' : `3px red solid`,
            }">
            <div class="content">
                <div class="card" style="width: 18rem; text-align: center;">
                    <div class="card-body">

                        <p class="card-title"><i class="las la-reply"></i> {{ getMixinValue.lang.message_with_buttons }}</p>

                        <div class="d-none">
                            Delay: {{ localStates.button_duration }}
                        </div>
                        <div v-if="localStates.header_text" class="bg-body-tertiary">
                            {{ localStates.header_text }}
                        </div>
                        <div class="bg-body-tertiary">
                            {{ localStates.button_message ? localStates.button_message :"Enter Button Message" }}
                        </div>
                        <div class="p-20">
                            <div class="d-flex flex-column justify-content-center align-items-end ">
                                <div v-for="button in localButtons" :key="button.id" class="button btn sg-btn-primary w-50 btn-sm mb-1" style="position: relative">
                                    <div :id="button.id + 'button'" contenteditable="true" @input="(event) => {
                                            updateValues(event, button.id);
                                        }">
                                        {{ button.text ?  button.text : "Enter Button Text" }}
                                    </div>
                                    <Handle :id="button.id + 'right'" class="handle" type="source"
                                        :position="Position.Right" style="top: 1.4rem; left: 100% !important" />
                                </div>
                            </div>
                        </div>
                        <div class="flow__footer">
                            {{ localStates.footer_text }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
