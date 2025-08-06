import { createRouter, createWebHistory } from "vue-router"

import HomePage from "./Pages/HomePage.vue"
import AdoultsPage from "./Pages/AdultsPage.vue"
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
        path: '/adults',
        name: 'adults',
        component: AdoultsPage,
    },
]
const router = createRouter ({
        history: createWebHistory(),
        routes,
})

export default router

