import './bootstrap';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';


import { createApp } from 'vue';
import App from './Components/App.vue';
import router from './router';

import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';


createApp(App).use(router).mount('#app');
