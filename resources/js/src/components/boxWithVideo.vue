<script setup>
import { ref, computed, watch } from "vue";
import { Handle, Position } from "@vue-flow/core";

// Importing Store Pinia
import { useStore } from "../stores/main.js";

// custom Top Menu import
import topMenu from "./topMenu.vue";
import globalValue from "../mixins/helper.js";
const getMixinValue = globalValue();
// Local variables and props declaration.
const transparent = ref(true);
let selectedColor = ref(false);
const props = defineProps(["id", "selected", "video", "current_id", "duration"]);
////////////////////////////////////////////.

// Usage of Store Pinia
const store = useStore();

// Computed Values from Store
let localStates = computed(() => {
	return store.getMessageById(props.id);
});

watch(
	() => props.selected,
	(isSelected) => (selectedColor.value = isSelected)
);

watch(
	() => [props.video, props.duration],
	() => {
		if (props.id === props.current_id) {
			localStates.value.video = props.video;
			localStates.value.video_duration = props.duration;
		}
	}
);
const emit = defineEmits(["data-sent"]);
function handleData() {
	emit("data-sent", { args: localStates });
}
</script>
<template>
		<!-- Handle for different utilities -->
		<Handle id="right" class="handle" type="source" :position="Position.Right" />
		<Handle id="left" class="handle" type="target" :position="Position.Left" />
		<!-- Handle for different utilities -->

		<div @mouseenter="transparent = false" @mouseleave="transparent = true"
			class="d-flex flex-column align-items-center">
			<!-- Delete Button and color controls -->
			<topMenu :eid="props.id" :transparent="transparent"></topMenu>
			<!-- Delete Button and color controls -->

			<div class="main-container" :style="{
				border: selectedColor ? '3px red solid' : `3px ${localStates.color} solid`,
			}">
				<div class="content">
					<div class="card" style="width: 18rem; text-align: center">
						<div class="card-body">
							<p class="card-title" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
								@click="handleData"><i class="las la-video"></i> {{ getMixinValue.lang.video }}</p>
							<div class="p-20 pt-0">
								<div class="d-none">
									Delay : {{ localStates.video_duration }}
								</div>
								<vue-plyr v-if="localStates.video">
									<video id="video_container">
										<source :src="localStates.video" type="video/mp4" />
									</video>
								</vue-plyr>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
</template>
