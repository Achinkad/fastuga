import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { io } from "socket.io-client"

import App from './App.vue'
import Toaster from "@meforma/vue-toaster";
import VueApexCharts from 'vue3-apexcharts'
import FieldErrorMessage from './components/global/FieldErrorMessage.vue'

import ConfirmationDialog from './components/global/ConfirmationDialog.vue'
import router from './router'
import axios from 'axios'

import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap-icons/font/bootstrap-icons.css"
import "bootstrap"

const app = createApp(App)
const serverBaseUrl = import.meta.env.VITE_API_URL;

/* --- AXIOS --- */
app.provide('axios', axios.create({
    baseURL: serverBaseUrl + '/api',
    headers: {
        'Content-type': 'application/json',
    },
}))
app.provide('serverBaseUrl', serverBaseUrl)

/* --- TOASTER --- */
app.use(Toaster, {
    position: 'top',
    timeout: 3000,
    pauseOnHover: true
})
app.provide('toast', app.config.globalProperties.$toast)

/* --- SOCKET.IO --- */
app.provide('socket', io("http://localhost:8080"))

/* --- APEX CHARTS --- */
app.use(VueApexCharts)

/* --- PINIA --- */
app.use(createPinia())

app.use(router)

/* --- CUSTOM COMPONENTS --- */
app.component('FieldErrorMessage', FieldErrorMessage)
app.component('ConfirmationDialog', ConfirmationDialog)

app.mount('#app')
