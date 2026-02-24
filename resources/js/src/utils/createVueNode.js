import getId from "./radomId";

let i = 0;
const createVueNode = (event, addNodes, project, store, custom_type = null) => {
  let id = getId();
  i++;

  let type = event.dataTransfer?.getData("application/vueflow");
  if (!type) {
    type = custom_type;
  }

  const position = project({ x: event.clientX - 450, y: event.clientY - 20 });

  let newNode = {
    id: type + id,
    type,
    position,
    label: `${type} node`,
  };

  //////////////////////////////////////////.
  switch (type) {
    case "starter-box":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "starter-box",
          label: "Label",
          matching_types: "exacts",
          keyword: "",
          title: "Start Bot flow",
          text: "Text",
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-title":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-title",
          label: "Label",
          title: "Message",
          text: "",
          title_duration: 0,
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "node-image":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "node-image",
          label: "Label",
          title: "Image",
          src: "",
          image_duration: 0,
          image: "",
          width: "340px",
          height: "240px",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-audio":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-audio",
          label: "Label",
          title: "Audio",
          audio_duration: 0,
          audio: "",
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-video":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-video",
          label: "Label",
          title: "Video",
          video_duration: 0,
          video: "",
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-file":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-file",
          label: "Label",
          title: "Document",
          file_duration: 0,
          file: "",
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-location":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-location",
          label: "Label",
          title: "Location",
          location_duration: 0,
          latitude: "",
          longitude: "",
          address_name: "",
          address: "",
          color: "#000000",
          position: i,
        });
      });
      break;
 
    case "box-with-button":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          title: "Button Message",
          type: "box-with-button",
          header_media_type: "",
          header_text: "", 
          header_media: "",
          footer_text: "",
          button_message: "",
          button_duration: 0,
          items: [
            {
              id: getId(),
              type: "button",
              text: "",
            },
            {
                id: getId(),
                type: "button",
                text: "",
              },
              {
                id: getId(),
                type: "button",
                text: "",
              },
          ],
          color: "#000000",
          position: i,
        });
      });
      break;
    case "starting-step":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "starting-step",
          label: "Label",
          keyword: "",
          matching_types: "",
          content: "Type",
          color: "#ffffff",
          items: [],
          position: i,
        });
      });
      break;
    case "box-with-flow":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-flow",
          title: "Flow",
          flow_id: "",
          flow_name: "",
          color: "#ffffff",
          position: i,
        });
      });
      break;

    case "container":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "container",
          label: "Label",
          width: "20rem",
          height: "10rem",
          color: "#3A8CC7",
        });
      });
      break;

    case "redirector":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "redirector",
          label: "Label",
          color: "#000000",
        });
      });
      break;

    case "box-with-template":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-template",
          label: "Label",
          title: "Template",
          template_id: "",
          subtitle: "Subtitle",
          template_variables: {},
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-condition":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-condition",
          label: "Label",
          title: "Condition",
          match_type: "",
          condition_fields: [
            {
              variable: "",
              operator: "",
              value: "",
            },
          ],
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-list":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-list",
          label: "Interactive List",
          title: "Interactive List",
          match_type: "",
          header_text: "Interactive header text",
          text_message: "",
          header_type: "",
          header_media: "",
          color: "#000000",
          button_text: "Button Text",
          section_title: "Section Title",
          items: [ //sections
            {
              id: getId(),
              type: "list",
              title: "Enter List title",
              text: "Enter List Text",
              subtitle: "Enter List subtitle",
            },
            {
                id: getId(),
                type: "list",
                title: "Enter List title",
                text: "Enter List Text",
                subtitle: "Enter List subtitle",
              },
              {
                id: getId(),
                type: "list",
                title: "Enter List title",
                text: "Enter List Text",
                subtitle: "Enter List subtitle",
              },
          ],
          position: i,
        });
      });
      break;
    case "box-with-interactive-button":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-interactive-button",
          label: "Label",
          title: "",
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-interactive-section":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-interactive-section",
          label: "Label",
          title: "",
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;
    case "box-with-interactive-row":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "box-with-interactive-row",
          label: "Label",
          title: "",
          description: "",
          subtitle: "Subtitle",
          color: "#000000",
          position: i,
        });
      });
      break;

    case "facebook-message":
      store.$patch((state) => {
        state.layers.messages.push({
          id: newNode.id,
          type: "facebook-message",
          label: "Label",
          color: "#ffffff",
          items: [
            {
              id: getId(),
              type: "messengerTextVue",
              text: "Enter Message Text",
              buttons: [],
            },
          ],
        });
      });
      break;

    default:
      break;
  }
  //////////////////////////////////////////.

  // Implementation of a basic container catching
  if (
    !custom_type &&
    event.target.parentNode.id.substring(-1, 9) === "container"
  ) {
    newNode.parentNode = event.target.parentNode.id;
  }
  ////////////////////////////////////////////.

  addNodes([newNode]);
  return newNode;
};

const copyVueNode = (addNodes, eid, getNode, store) => {
  let id = getId(); // Create a New UUid
  const nodeById = getNode.value(eid); // Get The node to copy by its Id (eid)

  const type = nodeById.type; // Get the node's type
  // When we copy, we need to create it above the old one (translate +50 x y)
  const position = {
    ...nodeById.position,
    x: nodeById.position.x + 50,
    y: nodeById.position.y - 50,
  };

  // Create a new message in the store
  store.$patch((state) => {
    const currentMessage = state.layers.messages.filter(
      (item) => item.id === eid
    ); // Get all the old message info

    state.layers.messages = [
      ...state.layers.messages,
      {
        ...JSON.parse(JSON.stringify(currentMessage))[0], // The element is copied by reference do we need to dereference it
        id: type + id,
      },
    ];
  });

  addNodes([
    {
      id: type + id,
      type,
      position,
      label: `${type} node`,
    },
  ]);
};

export { createVueNode, copyVueNode };
