import './bootstrap';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';


import { createApp } from 'vue';
import App from './Components/App.vue';
import router from './router';


createApp(App).use(router).mount('#app');
