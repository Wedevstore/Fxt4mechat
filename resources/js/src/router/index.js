import { createRouter, createWebHistory } from "vue-router";
import Home from "../pages/Chat.vue";
import Flow from "../pages/Flow.vue";
const routes = [
    // {
    //     path: "/:lang?/client/chat",
    //     name: "home",
    //     component: Home,
    // },
    // {
    //     path: "/:lang?/client/web-chat",
    //     name: "home",
    //     component: Home,
    // },
    {
        path: "/:lang(\\w+)?/client/chat",
        name: "chat",
        component: Home,
    },
    {
        path: "/:lang(\\w+)?/client/web-chat",
        name: "web-chat",
        component: Home,
    },
    {
        path: "/:lang?/client/flow-builders/:id?",
        name: "flow",
        component: Flow,
    },
];

let app_path = document.getElementById('app_path').value;


const router = new createRouter({
    // mode: 'history',
    history: createWebHistory(app_path),
    linkExactActiveClass: "active",
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { x: 0, y: 0 };
        }
    },
});

export default router;