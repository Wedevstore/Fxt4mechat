<script setup>
import { ref, computed, watch } from "vue";
import { Handle, Position } from "@vue-flow/core";

// Importing Store Pinia
import { useStore } from "../stores/main.js";

// Custom Top Menu import
import topMenu from "./topMenu.vue";
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();
// Local variables and props declaration
const transparent = ref(true);
let selectedColor = ref(false);
const props = defineProps(["id", "selected", "image", "current_id", "duration"]);
////////////////////////////////////////////.

// Usage of Store Pinia
const store = useStore();

// Computed Values from Store
let localStates = computed(() => {
	return store.getMessageById(props.id);
});

// Watching Selected Manual event
watch(
	() => props.selected,
	(isSelected) => (selectedColor.value = isSelected)
);
watch(
	() => [props.image, props.duration],
	() => {
		if (props.id === props.current_id) {
			localStates.value.image = props.image;
			localStates.value.image_duration = props.duration;
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
	<Handle id="right" class="handle" type="source" :position="Position.Right" />
	<Handle id="left" class="handle" type="target" :position="Position.Left" />
	<div @mouseenter="transparent = false" @mouseleave="transparent = true" class="d-flex flex-column align-items-center">
		<topMenu :eid="props.id" :transparent="transparent"></topMenu>
		<div class="main-container" :style="{
				border: selectedColor ? '3px red solid' : `3px ${localStates.color} solid`,
			}"
		>
			<div class="content" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" @click="handleData">
				<div class="card" style="width: 18rem; text-align: center">
					<div class="card-body">
						<p class="card-title">{{ getMixinValue.lang.image }}</p>

						<div class="d-none">
							Delay : {{ localStates.image_duration }}
						</div>
					
						<div class="thumb p-20">
							<img v-if="localStates.image" :src="localStates.image" alt="image" />
							<img v-else src="https://cloud.githubusercontent.com/assets/398893/15136779/4e765036-1639-11e6-9201-67e728e86f39.jpg" alt="image" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
