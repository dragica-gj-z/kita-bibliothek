import { createRouter, createWebHistory } from "vue-router"

import HomePage from "./Pages/HomePage.vue"
import AdoultsPage from "./Pages/AdultsPage.vue"
import KidsPage from "./Pages/KidsPage.vue"
import Register from "./Components/Register.vue"
import Login from "./Components/Login.vue"

const routes = [
    {
        path: '/',
        component: HomePage,          
        children: [
            { path: '', name: 'login', component: Login },
            { path: 'register', name: 'register', component: Register },
        ],
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

