<script setup>
import { ref, inject, onBeforeUnmount, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import { useConfigStore } from '../../stores/config.js'

const router = useRouter()
const configStore = useConfigStore()

const axios = inject('axios')
const toast = inject('toast')
const serverBaseUrl = inject("serverBaseUrl")

const credentials = ref({
        username: '',
        password: '',
        confirmpassword: ''
})

const saveCustomer = ref({
    phone: '',
    points: 0,
    nif: '',
    default_payment_type: '',
    default_payment_reference: '',
    custom: null,
    name: '',
    email: '',
    password: '',
    type: 'C',
    blocked: 0,
    photo_url: null
})

const customer = ref(saveCustomer)
const errors = ref(null)

const emit = defineEmits(['register'])

const dataAsString = () => {
    return JSON.stringify(customer.value)
}

let originalValueStr = ''

const saveCostumer = () => {
    errors.value = null
    if(credentials.value.password == credentials.value.confirmpassword){
        let formData = new FormData()

        formData.append('phone', saveCustomer.value.phone);
        formData.append('points', saveCustomer.value.points);

        if(saveCustomer.value.nif!=undefined) {
            formData.append('nif', saveCustomer.value.nif);
        }

        if(saveCustomer.value.default_payment_type!=undefined) {
            formData.append('default_payment_type', saveCustomer.value.default_payment_type);
        }

        if(saveCustomer.value.default_payment_reference!=undefined) {
            formData.append('default_payment_reference', saveCustomer.value.default_payment_reference);
        }

        formData.append('name', saveCustomer.value.name);
        formData.append('email', credentials.value.username);
        formData.append('password', credentials.value.password);
        formData.append('type', saveCustomer.value.type);
        formData.append('blocked', saveCustomer.value.blocked);

        axios.post(serverBaseUrl+'/api/customers', formData)
        .then((response) => {
            saveCustomer.value = response.data.data
            originalValueStr = dataAsString()
            toast.success('Register was done successfully.')
            router.back()
        })
        .catch((error) => {
            if (error.response.status == 422) {
                toast.error('User was not created due to validation errors!')
                errors.value = error.response.data.errors
            } else {
                toast.error('User was not created due to unknown server error!')
            }
        })
    } else {
        toast.error("The passwords aren't matching.")
    }
}

const register = () => {
    saveCostumer()
    emit('register')
}

onBeforeMount(() => {
    configStore.showNavbar = false
    configStore.showSidebar = false
    configStore.showMain = false
})

onBeforeUnmount(() => {
    configStore.showNavbar = true
    configStore.showSidebar = true
    configStore.showMain = true
})
</script>

<template>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-7 col-lg-7">
                    <div class="card">
                        <div class="card-header text-center">
                            <span>FASTUGA</span>
                        </div>
                        <div class="card-body">
                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">Free Sign Up</h4>
                                <p class="text-muted">Don't have an account? Create your account, it takes less than a minute.</p>
                            </div>
                            <form class="row g-3 needs-validation" novalidate @submit.prevent="login">
                                <div class="mb-3 col-md-6">
                                    <label for="inputName" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="inputName" placeholder="Enter your name" required v-model="saveCustomer.name">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="inputUsername" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="inputUsername" placeholder="Enter your email" required v-model="credentials.username">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="inputPassword" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="inputPassword" placeholder="Enter your password" required v-model="credentials.password">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="inputConfirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="inputConfirmPassword" placeholder="Confirm your password" required v-model="credentials.confirmpassword">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="inputPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="inputPhone" placeholder="Enter your phone" required v-model="saveCustomer.phone">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="inputNif" class="form-label">NIF</label>
                                    <input type="text" class="form-control" id="inputNif" placeholder="Enter your NIF" v-model="saveCustomer.nif">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="payment_type" class="form-label">Payment Type</label>
                                    <select id="payment_type" name="payment_type" class="form-select" v-model="saveCustomer.default_payment_type">
                                        <option selected value="VISA">Visa</option>
                                        <option value="PAYPAL">PayPal</option>
                                        <option value="MBWAY">MBWay</option>
                                    </select>
                                    <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="inputPaymentReference" class="form-label">Default Payment Reference</label>
                                    <input type="text" class="form-control" id="inputPaymentReference" placeholder="Enter your payment reference" v-model="saveCustomer.default_payment_reference">
                                </div>
                                <div class="mb-3 d-flex justify-content-center">
                                    <button type="button" class="btn btn-primary px-3" @click="register">Sign Up</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row" id="signup">
                        <div class="col-12 text-center">
                            <p class="text-muted">Already have an account?
                                <router-link class="text-muted ms-1" :to="{ name: 'Login' }">
                                    <b>Log In</b>
                                </router-link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer footer-alt">
        Distributed Applications Development - 2022 Project
    </footer>
</template>

<style scoped>
    .account-pages {
        align-items: center;
        display: flex;
        min-height: 100vh;
    }

    .card-header {
        background-color: rgb(114, 124, 245);
        color: #fff !important;
        font-weight: 800;
        font-size: 1.1rem;
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

    .card, .card-body {
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
        background-color: #727cf5 !important;
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
        left: 0;
        border: none;
        text-align: center;
        font-size: 14px;
    }

    .footer {
        bottom: 0;
        padding: 19px 24px 20px;
        position: absolute;
        right: 0;
        transition: all .2s ease-in-out;
    }

    .text-danger {
        color: rgb(250, 92, 124) !important;
        font-size: 14px;
        font-weight: 700;
    }
</style>
