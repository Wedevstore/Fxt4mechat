<script setup>
import SidebarTemplate from "./SidebarTemplate.vue";
import CardText from "../../assets/svg/CardText.svg";
import BiGoogle from "../../assets/svg/BiGoogle.svg";
import CardImage from "../../assets/svg/CardImage.svg";
import CardAudio from "../../assets/svg/CardAudio.svg";
import CardVideo from "../../assets/svg/CardVideo.svg";
import CardFile from "../../assets/svg/CardFile.svg";
import CardLocation from "../../assets/svg/CardLocation.svg";
import CardCondition from "../../assets/svg/CardCondition.svg";
import CardInteractive from "../../assets/svg/CardInteractive.svg";
import CardQuickReply from "../../assets/svg/QuickReply.svg";
import globalValue from "../../mixins/helper.js";
const getMixinValue = globalValue();

const templates = [
	// {
	// 	type: "starter-box",
	// 	matching_types: "exact",
	// 	keyword: "Hello,Hi,Start",
	// 	title: "Text",
	// 	subtitle: "",
	// 	logoColor: "#129f7f",
	// 	logoComponent: CardText,
	// },
	{
		type: "box-with-title",
		title: "Text Message",
		subtitle: "",
		logoColor: "#129f7f",
		logoComponent: CardText,
	},
	{
		type: "box-with-button",
		title: "Button Message",
		subtitle: "",
		logoColor: "#0eaba7",
		logoComponent: CardQuickReply,
	},
	{
		type: "node-image",
		title: "Image Message",
		subtitle: "",
		logoColor: "#5392f9",
		logoComponent: CardImage,
	},
	{
		type: "box-with-audio",
		title: "Audio Message",
		subtitle: "",
		logoColor: "#fdc334",
		logoComponent: CardAudio,
	},
	{
		type: "box-with-video",
		title: "Video Message",
		subtitle: "",
		logoColor: "#ff6b6b",
		logoComponent: CardVideo,
	},
	{
		type: "box-with-file",
		title: "Document",
		subtitle: "",
		logoColor: "#1565c0",
		logoComponent: CardFile,
	},
	{
		type: "box-with-location",
		title: "Location",
		subtitle: "",
		logoColor: "#0eab61",
		logoComponent: CardLocation,
	},
	// {
	// 	type: "box-with-list",
	// 	title: "Interactive List",
	// 	subtitle: "",
	// 	logoColor: "#0eaba7",
	// 	logoComponent: CardInteractive,
	// },
	// {
	// 	type: 'box-with-template',
	// 	title: 'Template',
	// 	subtitle: '',
	// 	logoColor: 'red',
	// 	logoComponent: BiGoogle,
	// },
	// {
	// 	type: "box-with-condition",
	// 	title: "Condition",
	// 	subtitle: "",
	// 	logoColor: "#159be1",
	// 	logoComponent: CardCondition,
	// },
	// {
	// 	type: "cta-button",
	// 	title: "CTA Button",
	// 	subtitle: "",
	// 	logoColor: "#159be1",
	// 	logoComponent: CardQuickReply,
	// },
	// {
	// 	type: "box-with-flow",
	// 	title: "Flow",
	// 	subtitle: "",
	// 	logoColor: "#159be1",
	// 	logoComponent: CardQuickReply,
	// },
];
</script>

<template>
	<aside>
		<h1 class="flowBuilder__title">{{ getMixinValue.lang.available_components }}</h1>
		<div class="container">
			<div class="flowBuilder__wrapper">
				<SidebarTemplate v-for="template in templates" v-bind="template" />
			</div>
		</div>
	</aside>
</template>
