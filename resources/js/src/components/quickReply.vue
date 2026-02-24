<script setup>
import { ref, computed, watch } from "vue";
import { Handle, Position } from "@vue-flow/core";

// Importing Store Pinia
import { useStore } from "../stores/main.js";

import messageRendererVue from "./messageRenderer.vue";
import TrashIcon from "../assets/svg/TrashIcon.svg";

// Custom Top Menu import
import topMenu from "./topMenu.vue";
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();
// Importing SVG icons
import Messenger from "../assets/svg/Messenger.svg";

// Local variables and props declaration
const transparent = ref(true);
let selectedColor = ref(false);
// Default id is passed from the component
const props = defineProps({
  id: String,
  selected: Boolean,
});
////////////////////////////////////////////.

// Usage of Store Pinia
const store = useStore();

// Computed Values from Store
let localStates = computed(() => {
  return store.getMessageById(props.id);
});

let messageToEdit = computed(() => {
  return store.messageToEdit;
});
////////////////////////////////////////////.
// Elements related methods
const deleteElement = (id) => {
  localStates.value.items = Items.value.filter((element) => element.id != id);
};
// Watching Selected Manual event
watch(
  () => props.selected,
  (isSelected) => (selectedColor.value = isSelected)
);
////////////////////////////////////////////.
</script>

<template>
  <Handle :id="id + 'left'" class="handle handle-left" type="target" :position="Position.Left" style="right: 0" />
  <div @mouseenter="transparent = false" @mouseleave="transparent = true" class="d-flex flex-column align-items-center">
    <div class="main-container" :class="{ 'on-edit': messageToEdit == id }">
      <div class="card">
        <div class="card-header">
            <div>{{ getMixinValue.lang.send_message }}</div>
        </div>
        <div class="card-body">
        <div class="button-container" :class="{ transparent: transparent }"
        style="position: absolute; top: 50%; right:" @click="deleteElement(id)">
        <TrashIcon />
      </div>
      </div>
      </div>
      
    </div>
  </div>
</template>

<style scoped>
/* 
[contenteditable]:focus {
  outline: none;
}

[contenteditable] {
  cursor: text;
}

.content {
  background-color: #fff;
  padding: 0.5rem 1rem 0.5rem 1rem;
  width: 100%;
  border-bottom-left-radius: 1rem;
  border-bottom-right-radius: 1rem;
  cursor: pointer;
}

.handle-left {
  background-color: white;
  width: 1rem;
  height: 1rem;
  border: 2px solid;
  position: absolute;
  top: 5rem;
  left: -5px !important;
  z-index: 1002;
}

.handle-right {
  background-color: white;
  width: 0.95rem;
  height: 1rem;
  border: 2px solid;
  position: absolute;
  top: 95%;
  right: -5px !important;
  z-index: 1002;
}

.handle-right:hover,
.handle-left:hover {
  width: 1.3rem;
  height: 1.3rem;
  transition: width, height 0.5s;
}

.label {
  display: flex;
  justify-content: center;
  align-items: center;
  position: absolute;
  left: 50%;
  transform: translate(-50%, -100%);
  padding: 5px 1em 0px 1em;
  clear: left;
  width: 100%;
  border: 2px solid;
  border-bottom: transparent;
  border-top-left-radius: 1rem;
  border-top-right-radius: 1rem;
  background-color: white;
  padding: 0;
  cursor: move;
}

.label input {
  width: calc(100% - 1rem);
  margin: 0.2rem;
  padding: 0;
  outline: transparent;
  border: transparent;
  text-align: center;
  cursor: move;
}

.main-container {
  max-width: calc(23rem + 6px);
  border: 2px solid black;
  border-top: 0px;
  border-bottom-right-radius: 1rem;
  border-bottom-left-radius: 1rem;
  margin-top: -1px;
  box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
}

.main-container:hover {
  border: 2px #0084ff solid;
  border-top: 0px;
  border-bottom-right-radius: 1rem;
  border-bottom-left-radius: 1rem;
}

.on-edit {
  border: 3px red solid;
}

.starting-step {
  border-bottom: 1px solid #eee;
}

.starting-step {
  background-color: white;
  width: 23rem;
  height: 3rem;
  font-size: medium;
  text-align: left;
  padding-left: 0.5rem;
  padding-top: 0.3rem;
} */
</style>
