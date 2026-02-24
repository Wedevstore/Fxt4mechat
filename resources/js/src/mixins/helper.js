import {reactive} from 'vue'

export default function globalValue() {
    const storeData = reactive({
        message: "",
        receiver_id: "",
        image: '',
        document: '',
        reply_message_id: '',
    });
    const params_data = reactive({
        q: "",
        name: "",
        page: 1,
        chat_room_page: 1,
        contact_page: 1,
    });
    const config = reactive({
        loading: false,
    });
    const lang = window.translations;
    const sidebar = false;

    const authUser = JSON.parse(document.getElementById('auth_user').value);

    function createFormData() {
        let formData = new FormData();

        for (var key in storeData) {
            formData.append(key, storeData[key]);
            if (key == 'image') {
                formData.append('image', storeData.image);
            }
        }
        // Ensure reply_message_id is included
        if (storeData.reply_message_id) {
            formData.append('reply_message_id', storeData.reply_message_id);
        }
        return formData;
    }

    async function imageUp(event) {
        storeData.images = event.target.files[0];
    }


    const url = document.getElementById('base_url').value;
    const asset_url = document.getElementById('asset_url').value;

    function getUrl(custom_url) {
        return url + '/client/' + custom_url;
    }
    function assetUrl(custom_url) {
        return asset_url +'/' + custom_url;
    }


    return {
        storeData,
        params_data,
        config,
        authUser,
        lang,
        sidebar,
        createFormData,
        getUrl,
        assetUrl
    }
}