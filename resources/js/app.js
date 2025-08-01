import './bootstrap';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import router from './router';


import { createApp } from 'vue';
import App from './Components/App.vue';

createApp(App).use(router).mount('#app');
