<script setup>
import { ref, inject, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user.js'
import avatarNoneUrl from '@/assets/avatar-none.png'

const axios = inject('axios')
const toast = inject('toast')
const serverBaseUrl = inject("serverBaseUrl")

const router = useRouter()
const userStore = useUserStore()

const errors = ref(null)

const newCustomer = ref({
    id: "",
    phone: "",
    points: 0,
    nif: "",
    default_payment_type: "",
    default_payment_reference: "",
    user: {
        name: "",
        email: "",
        type: "C",
        blocked: 0,
        photo_url: null,
    }
})

var previewImage = null

const handleUpload = (e) => {
    const image = e.target.files[0];
    const reader = new FileReader();
    reader.readAsDataURL(image);
    reader.onload = e => {
        previewImage = e.target.result;
    }
}

const customer = ref(newCustomer)

const dataAsString = () => {
    return JSON.stringify(customer.value)
}

let originalValueStr = '';
const loadCustomer = () => {
    errors.value = null
    if (userStore.user && userStore.user.type == "C") {
        axios.get(serverBaseUrl + '/api/customers/users/' + userStore.user.id)
        .then((response) => {
            customer.value = response.data.data
            originalValueStr = dataAsString()
        })
    }
    else {
        customer.value.user = userStore.user
    }
}

const validations = (data) => {
    errors.value = {}
    data.forEach((item, i) => {
        switch (i) {
            case "name":
            if (item.length === 0) errors.value.name = ["Enter a valid name."]
            break;
            case "phone":
            if (item.length == 0 || item.length < 9 || item.length >= 20) errors.value.phone = ["Enter a valid phone number."]
            break;
            case "nif":
            if (item.length > 0 && (item.length != 9)) errors.value.nif = ["Enter a valid NIF."]
            break;
            case "default_payment_type":
            if (item.length > 0 && (item != "VISA" && item != "MBWAY" && item != "PAYPAL")) errors.value.default_payment_type = ["Select a valid payment type."]
            break;
            case "email":
            if (item.length === 0) errors.value.email = ["Enter a valid e-mail address."]
            break;
            case "password":
            if (item.length < 8) errors.value.password = ["Password should be at least 8 characters."]
            break;
        }
    })
    return Object.keys(errors.value).length === 0 ? true : false
}

const save = () => {
    let formData = new FormData()
    formData.append('name', customer.value.user.name);
    formData.append('email', customer.value.user.email);
    formData.append('type', customer.value.user.type);

    if (customer.value.user.blocked == false) {
        formData.append('blocked', 0);
    } else {
        formData.append('blocked', 1);
    }

    if (previewImage) formData.append('photo_url', previewImage)

    if (validations(formData)) {
        userStore.save(formData, userStore.user.id)
        router.push({ name: "Dashboard" })
    }
}

const photoFullUrl = computed(() => {
    return customer.value.user.photo_url ? serverBaseUrl + "/storage/fotos/" + customer.value.user.photo_url : avatarNoneUrl
})

const cancel = () => { router.back() }

const updateCostumer = () => {
    errors.value = null
    let formData = new FormData()

    formData.append('phone', customer.value.phone)
    formData.append('points', customer.value.points)
    formData.append('nif', customer.value.nif)
    formData.append('default_payment_type', customer.value.default_payment_type);
    formData.append('default_payment_reference', customer.value.default_payment_reference);
    formData.append('name', customer.value.user.name)
    formData.append('email', customer.value.user.email)
    formData.append('type', customer.value.user.type)

    if (previewImage != null) formData.append('photo_url', previewImage)

    if (customer.value.user.blocked == false) {
        formData.append('blocked', 0);
    } else {
        formData.append('blocked', 1);
    }

    if (validations(formData)) {
        axios.put(serverBaseUrl + '/api/customers/' + customer.value.id, formData)
        .then((response) => {
            customer.value = response.data.data
            originalValueStr = dataAsString()
            toast.success('Your profile was updated successfully!')
            router.push({ name: "Dashboard" })
        })
        .catch((error) => {
            if (error.response.status == 422) {
                toast.error('Customer was not updated due to validation errors!')
                errors.value = error.response.data.errors
            } else {
                toast.error('Customer was not updated due to unknown server error!')
            }
        })
    }
}

onMounted(() => {
    loadCustomer()
})
</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex p-title-box">
                    <h4 class="p-title me-auto">User Profile</h4>
                </div>
            </div>
        </div>

        <form class="pe-2 needs-validation" novalidate enctype="multipart/form-data">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-xl-6">
                                        <div class="mb-3 px-1">
                                            <label for="inputName" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="inputName" placeholder="User Name" required v-model="customer.user.name" />
                                            <field-error-message :errors="errors" fieldName="name"></field-error-message>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="mb-3 px-1">
                                            <label for="inputEmail" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="inputEmail" placeholder="Email" required
                                            v-model="customer.user.email" />
                                            <field-error-message :errors="errors" fieldName="email"></field-error-message>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="mb-3 px-1" v-if="userStore.user && userStore.user.type == 'C'">
                                            <label for="inputPhone" class="form-label">Phone</label>
                                            <input type="text" class="form-control" id="inputPhone" placeholder="Phone" required
                                            v-model="customer.phone" />
                                            <field-error-message :errors="errors" fieldName="phone"></field-error-message>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="mb-3" v-if="userStore.user && userStore.user.type == 'C'">
                                            <label for="inputNif" class="form-label">NIF</label>
                                            <input type="text" class="form-control" id="inputNif" placeholder="NIF" v-model="customer.nif" />
                                            <field-error-message :errors="errors" fieldName="name"></field-error-message>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="mb-3" v-if="userStore.user && userStore.user.type == 'C'">
                                            <label class="form-label" for="payment_type">Payment Type</label>
                                            <select id="payment_type" name="payment_type" class="form-select"
                                            v-model="customer.default_payment_type">
                                            <option value="VISA">Visa</option>
                                            <option value="PAYPAL">PayPal</option>
                                            <option value="MBWAY">MBWay</option>
                                        </select>
                                        <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="mb-3" v-if="userStore.user && userStore.user.type == 'C'">
                                        <label for="inputPaymentReference" class="form-label">Default Payment Reference</label>
                                        <input type="text" class="form-control" id="inputPaymentReference" v-model="customer.default_payment_reference" />
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label">Photo</label>
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name='upload' @change="handleUpload" required>
                                        <br>
                                        <img :src="photoFullUrl" class="img-thumbnail" />
                                        <field-error-message :errors="errors" fieldName="photo_url"></field-error-message>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 d-flex justify-content-end">
                    <div class="mb-3 px-1">
                        <button v-if="userStore.user.type == 'C'" type="button" class="btn btn-warning px-4 btn-add" @click="updateCostumer">
                            Save User
                        </button>
                        <button v-else type="button" class="btn btn-warning px-4 btn-add" @click="save">
                            Save User
                        </button>
                    </div>
                    <div class="mb-3 px-1">
                        <button type="button" class="btn btn-light px-4" @click="cancel">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</template>
