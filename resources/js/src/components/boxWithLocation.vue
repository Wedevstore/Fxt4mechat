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
const props = defineProps(["id", "selected", "latitude", "longitude", "current_id", "location_duration","address_name","address"]);
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
	() => [props.longitude, props.latitude, props.location_duration, props.address_name, props.address],
	() => {
		if (props.id === props.current_id) {
			localStates.value.address_name = props.address_name;
			localStates.value.address = props.address;
			localStates.value.latitude = props.latitude;
			localStates.value.longitude = props.longitude;
			localStates.value.location_duration = props.location_duration;
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
		<div @mouseenter="transparent = false" @mouseleave="transparent = true"
			class="d-flex flex-column align-items-center">
			<!-- Delete Button and color controls -->
			<topMenu :eid="props.id" :transparent="transparent"></topMenu>
			<!-- Delete Button and color controls -->
			<div data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" @click="handleData" class="main-container"
				:style="{
					border: selectedColor ? '3px red solid' : `3px ${localStates.color} solid`,
				}">
				<div class="content">
					<div class="card" style="width: 18rem; text-align: center">
						<div class="card-body">
						<div class="d-none">
                            {{ localStates.latitude }}
                        </div>
                        <div class="d-none">
                            {{ localStates.longitude }}
                        </div>
                        <div class="d-none">
                            {{ localStates.address_name }}
                        </div>
                        <div class="d-none">
                            {{ localStates.address }}
                        </div>
                        <div class="d-none">
							Delay:
                            {{ localStates.location_duration }}
                        </div>
							<p class="card-title"><i class="las la-map-marker-alt"></i> {{ getMixinValue.lang.location }}</p>
							<div class="p-20">
								<a href="javascript:void(0);"
									class="btn sg-btn-primary triger_btn"><i class="las la-map-marked-alt"></i> Maps</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
</template>
