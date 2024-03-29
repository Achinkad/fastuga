<script setup>
import { ref, inject, onBeforeUnmount, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user.js'
import { useConfigStore } from '../../stores/config.js'

const router = useRouter()
const userStore = useUserStore()
const configStore = useConfigStore()
const toast = inject('toast')

const credentials = ref({
    username: '',
    password: ''
})

const emit = defineEmits(['login'])

const login = async () => {
    if (await userStore.login(credentials.value)) {
        toast.success('User ' + userStore.user.name + ' has entered the application.')
        emit('login')
        router.push({ name: "Dashboard" })
    } else {
        credentials.value.password = ''
        toast.error('The credentials were invalid. Try again.')
    }
}

onBeforeMount(() => {
    configStore.showNavbar = false
    configStore.showSidebar = false
    configStore.showMain = false
    configStore.showFooter = false
})

onBeforeUnmount(() => {
    configStore.showNavbar = true
    configStore.showSidebar = true
    configStore.showMain = true
    configStore.showFooter = true
})
</script>

<template>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <div class="card-header text-center">
                            <span>Fastuga.</span>
                        </div>
                        <div class="card-body">
                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">Sign In</h4>
                                <p class="text-muted">Enter your email address and password to access the application.
                                </p>
                            </div>
                            <form class="row g-3 needs-validation" novalidate @submit.prevent="login">
                                <div class="mb-3">
                                    <label for="inputUsername" class="form-label">E-Mail</label>
                                    <input type="text" class="form-control" id="inputUsername"
                                        placeholder="Enter your email" required v-model="credentials.username">
                                </div>
                                <div class="mb-3">
                                    <label for="inputPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="inputPassword"
                                        placeholder="Enter your password" required v-model="credentials.password">
                                </div>
                                <div class="mb-3 text-center">
                                    <button type="button" class="btn px-3" @click="login">Log In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row" id="signup">
                        <div class="col-12 text-center">
                            <p class="text-muted" style="margin-bottom:.5rem!important;">Don't have an account?
                                <router-link class="text-muted" :to="{ name: 'Register' }">
                                    <b>Sign Up.</b>
                                </router-link>
                            </p>
                            <p class="text-muted">You can also shop
                                <router-link class="text-muted" :to="{ name: 'Dashboard' }">
                                    <b>anonymous.</b>
                                </router-link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer footer-alt">
        2022 © Project - Distributed Applications Development
    </footer>
</template>

<style scoped>
.account-pages {
    align-items: center;
    display: flex;
    min-height: 100vh;
}

.card-header {
    background: rgb(238, 155, 39);
    background: linear-gradient(40deg, rgba(238, 155, 39, 1) 50%, rgba(232, 180, 109, 1) 100%);
    color: #fff !important;
    font-weight: 800;
    font-size: 1.5rem;
    padding-top: 2.25rem !important;
    padding-bottom: 2.25rem !important;
    border: 0;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

.card-body {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    padding: 2.25rem !important;
}

.card,
.card-body {
    border: 0;
}

h4 {
    font-size: 1.125rem;
}

.text-muted {
    color: #6c757d !important;
    margin-bottom: 1.5rem !important;
    font-size: 14px;
    font-weight: 400;
}

label {
    color: #6c757d !important;
    font-size: 14.4px;
    font-weight: 600;
}

input::placeholder {
    font-size: 14px;
    opacity: .6;
}

button[type="button"] {
    background: rgb(238, 155, 39);
    background: linear-gradient(40deg, rgba(238, 155, 39, 1) 50%, rgba(232, 180, 109, 1) 100%);
    color: #fff;
    border-color: #727cf5;
    border-radius: 0.15rem;
    box-shadow: 0px 2px 6px 0px rgba(114, 124, 245, 0.5);
    border: 1px #727cf5;
    font-size: 15px;
    padding: .5rem 0;
}

button[type="button"]:hover {
    color: #fff;
}

button[type="button"]:focus {
    color: #fff;
    box-shadow: 0 0 0 .15rem rgba(135, 144, 247, 0.5);
}

#signup {
    margin-top: 1.5rem !important;
}

#signup a {
    text-decoration: none;
}

.footer-alt {
    left: 0 !important;
    width: 100% !important;
    border: none;
    text-align: center;
    font-size: 14px;
}
</style>
