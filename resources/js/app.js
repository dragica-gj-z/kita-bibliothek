import './bootstrap';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import { createApp } from 'vue';
import App from './Components/App.vue';
import router from './router';

import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// CSRF aus dem Meta-Tag holen und setzen
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token
} else {
  console.warn('CSRF token not found: bitte <meta name="csrf-token"> im Layout einfügen.')
}

createApp(App).use(router).mount('#app');
