import { createRouter, createWebHistory } from "vue-router"

import HomePage from "./Pages/HomePage.vue"
import AdoultsPage from "./Pages/AdoultsPage.vue"
import KidsPage from "./Pages/KidsPage.vue"

const routes = [
    {
        path: '/',
        name: 'home',
        component: HomePage,
    },
    {
        path: '/kids',
        name: 'kids',
        component: KidsPage,
    },
    {
        path: '/adoults',
        name: 'adoults',
        component: AdoultsPage,
    },
]
const router = createRouter ({
        history: createWebHistory(),
        routes,
})

export default router

