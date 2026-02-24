<script setup>
import { ref, computed, watch } from "vue";
import { Handle, Position } from "@vue-flow/core";

// Importing Store Pinia
import { useStore } from "../stores/main.js";

// custom Top Menu import
import topMenu from "./topMenu.vue";
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();
// Local variables and props declaration
const transparent = ref(true);
let selectedColor = ref(false);
const props = defineProps(['id', 'selected', 'current_id', 'match_type', 'header_type', 'text_message', 'footer_text', 'button_text', 'header_text', 'header_media', 'duration']);
////////////////////////////////////////////.
// Usage of Store Pinia
const store = useStore();
const localStates = computed(() => store.getMessageById(props.id));
const Items = computed(() => localStates.value.items);
const localItems = computed(() => store.getItemById(props.id));
const localButtons = Items;
// Watching Selected Manual event
watch(
	() => props.selected,
	(isSelected) => (selectedColor.value = isSelected)
);

watch(
	() => [props.match_type, props.header_type, props.text_message, props.footer_text, props.button_text, props.header_text, props.header_media, props.duration],
	() => {
		if (props.id === props.current_id) {
			localStates.value.match_type = props.match_type;
			localStates.value.header_type = props.header_type;
			localStates.value.text_message = props.text_message;
			localStates.value.header_text = props.header_text;
			localStates.value.footer_text = props.footer_text;
			localStates.value.button_text = props.button_text;
			localStates.value.header_media = props.header_media;
			localStates.value.buttons = props.buttons;
            localStates.value.items = props.items;
			localStates.value.duration = props.duration;
		}
	}
);
const emit = defineEmits(["data-sent"]);
function handleData() {
	emit("data-sent", { args: localStates });
}
////////////////////////////////////////////.
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
                        <p class="card-title"><i class="las la-reply"></i> {{ getMixinValue.lang.interactive_list }}</p>
                        <div  class="bg-body-tertiary">
                            {{ localStates.text_message }}
                        </div>
                        <div class="bg-body-tertiary">
                            {{ localStates.button_text }}
                        </div>
                        <div class="bg-body-tertiary">
                            {{ localStates.section_title }}
                        </div>
                        <div class="p-20">
                            <div class="d-flex flex-column justify-content-center align-items-end ">
                                <div v-for="button in localButtons" :key="button.id" class="button btn sg-btn-primary w-50 btn-sm mb-1" style="position: relative">
                                    <div :id="button.id + 'button'" contenteditable="true">
                                        {{ button.text }} <br>

										{{ button.subtitle }}
										
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