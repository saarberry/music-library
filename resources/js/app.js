import '@/bootstrap.js';

import { createApp } from 'vue'
import App from '@/components/App.vue';

createApp({})
    .component('app', App)
    .mount('#app');