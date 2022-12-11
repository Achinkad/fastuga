import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'
import App from './App.vue'
import router from './router'
import Toaster from "@meforma/vue-toaster";
import VueApexCharts from 'vue3-apexcharts'
import FieldErrorMessage from './components/global/FieldErrorMessage.vue'
import ConfirmationDialog from './components/global/ConfirmationDialog.vue'
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap-icons/font/bootstrap-icons.css"
import "bootstrap"


const app = createApp(App)
const serverBaseUrl = import.meta.env.VITE_API_URL;

app.provide('axios', axios.create({
    baseURL: serverBaseUrl + '/api',
    headers: {
        'Content-type': 'application/json',
    },
}))
app.provide('serverBaseUrl', serverBaseUrl)
app.use(Toaster, {
    // Global/Default options
    position: 'top',
    timeout: 3000,
    pauseOnHover: true
})
app.use(VueApexCharts)
app.provide('toast', app.config.globalProperties.$toast);
app.use(createPinia())
app.use(router)
app.component('FieldErrorMessage', FieldErrorMessage)
app.component('ConfirmationDialog', ConfirmationDialog)
app.mount('#app')
