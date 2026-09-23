import { createApp, reactive } from 'vue';
import TransactionsComponent from './components/TransactionsComponent.vue';
import GermanyTransactionsComponenet from './components/GermanyTransactionsComponenet.vue';
import TestWebSocket from './components/TestWebSocket.vue';
import * as bootstrap from 'bootstrap';
import axios from 'axios';
// import { i18nVue } from 'laravel-vue-i18n';


// import { initializeApp } from 'firebase/app';
// import { getDatabase } from 'firebase/database';



// import App from './App.vue';
// import router from './router';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'bootstrap';
import 'datatables.net-bs5';
import './bootstrap';
import jQuery from 'jquery';
import "./assets/css/loader.css";

// // Initialize Firebase using global config
// const firebaseApp = initializeApp(window.firebaseConfig);
// window.firebaseDB = getDatabase(firebaseApp);

window.bootstrap = bootstrap;
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


// const i18nStore = reactive({
//     translations: window.i18n || {}
// })


const app = createApp({});


// // Define global translation helper
// app.config.globalProperties.__ = function (key) {
//     const segments = key.split('.')
//     let value = window.i18n

//     for (const segment of segments) {
//         if (value && Object.prototype.hasOwnProperty.call(value, segment)) {
//             value = value[segment]
//         } else {
//             return key
//         }
//     }

//     return value
// }


// app.config.globalProperties.__ = function (key) {
//     const parts = key.split('.')
//     let value = i18nStore.translations
//     for (const part of parts) {
//         value = value?.[part]
//         if (!value) break
//     }
//     return value || key
// }

// // expose helper for language switching
// app.config.globalProperties.$setLanguage = async function (locale) {
//     const res = await fetch(`/api/lang/${locale}`)
//     if (res.ok) {
//         const data = await res.json()
//         i18nStore.translations = data
//     }
// }

// app.provide('i18nStore', i18nStore)


app.component('transactions-component', TransactionsComponent);
app.component('gemany-transactions-component', GermanyTransactionsComponenet);
app.component('test-web-socket', TestWebSocket);



// // Use eager: true for reliable loading
// app.use(i18nVue, {
//     resolve: lang => {
//         const langs = import.meta.glob('../../lang/*.json', { eager: true });
//         return langs[`../../lang/${lang}.json`].default;
//     }
// });


app.mount('#app');