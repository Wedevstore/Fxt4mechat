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
// const props = defineProps(['id','selected', 'current_id', 'text', 'keyword','matching_types']);
const props = defineProps({
	id: String,
	current_id: String,
	selected: Boolean,
	text: String,
	keyword: String,  // New prop
	matching_types: String,  // New prop
});
////////////////////////////////////////////.
// Usage of Store Pinia
const store = useStore();
// Computed Values from Store.
let localStates = computed(() => {
	return store.getMessageById(props.id);
});

// Watching Selected Manual event
watch(
	() => props.selected,
	(isSelected) => (selectedColor.value = isSelected)
);
watch(
	() => [props.keyword, props.matching_types],
	() => {
		if (props.id === props.current_id) {
			localStates.value.keyword = props.keyword;
			localStates.value.matching_types = props.matching_types;

		}
	
	}
	
);
const emit = defineEmits(["data-sent"]);

function handleData() {
	emit("data-sent", { args: localStates });
}
</script>
<template>
	<Handle id="right" class="handle" type="source" :position="Position.Right" />
	<div @mouseenter="transparent = false" @mouseleave="transparent = true" class="d-flex flex-column align-items-center">
		<!-- Delete Button and color controls -->
		<!-- <topMenu :eid="props.id" :transparent="transparent"></topMenu> -->
		<!-- Delete Button and color controls -->
		<div
			@click="handleData"
			data-bs-toggle="offcanvas"
			data-bs-target="#offcanvasRight" 
			class="main-container"
			:style="{ border: selectedColor ? '3px red solid' : `3px ${localStates.color} solid`,}">
			<div class="content">
				<div class="card" style="width: 18rem;">
					<div class="card-body">
						<p class="card-title"><i class="las la-walking"></i> {{ getMixinValue.lang.start_bot_flow }}</p>
						<div v-if="localStates.keyword" class="bg-body-tertiary">
						  <span>{{ getMixinValue.lang.keyword }} : {{ localStates.keyword }}</span>
						</div>
						<div v-if="localStates.matching_types" class="bg-body-tertiary">
						  <span> {{ getMixinValue.lang.matching_type }} : {{ localStates.matching_types ? localStates.matching_types : "exacts" }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<style scoped>
.add-items {
  border: 1px rgb(120, 120, 120) dashed;
  border-radius: 5px;
  padding: 0.8rem;
  color: rgb(120, 120, 120);
  margin: 0.4rem;
}
.add-items:hover {
  background-color: #eee;
  color: #0084ff;
}

.add-items:active {
  cursor: grabbing;
}

.add-type {
  text-align: left;
  border-radius: 1rem;
  padding: 0.4rem;
}

.items {
  border: 1px #e1faec solid;
  border-radius: 10px;
  padding: 0.8rem;
  color: rgb(120, 120, 120);
  margin: 0.4rem;
  background-color: #e1faec;
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

</style>
