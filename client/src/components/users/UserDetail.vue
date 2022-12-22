<script setup>
import { ref, watch, computed, inject } from "vue";
import { useUserStore } from '../../stores/user.js'
import avatarNoneUrl from '@/assets/avatar-none.png'

const userStore = useUserStore()

const serverBaseUrl = inject("serverBaseUrl")
var previewImage = null

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        required: false
    },
    operationType: {
        type: String,
        default: "insert",
    },
})

const handleUpload = (e) => {
    const image = e.target.files[0];
    const reader = new FileReader();
    reader.readAsDataURL(image);
    reader.onload = e => {
        previewImage = e.target.result;
    }
}

const emit = defineEmits(["save", "cancel", "add"])
const editingUser = ref(props.user)
const errors = ref(null)

const photoFullUrl = computed(() => {
    return editingUser.value.photo_url ? serverBaseUrl + "/storage/fotos/" + editingUser.value.photo_url : avatarNoneUrl
})

const cancel = () => { emit("cancel", editingUser.value) }

const validations = (data) => {
    errors.value = {}
    data.forEach((item, i) => {
        switch (i) {
            case "name":
                if (item.length === 0) errors.value.name = ["Enter a valid name."]
                break;
            case "email":
                if (item.length === 0) errors.value.email = ["Enter a valid e-mail address."]
                break;
            case "type":
                if (item.length === 0 || (item.length > 0 && (item != "EM" && item != "ED" && item != "EC" && item != "C"))) errors.value.type = ["Select a valid role type."]
                break;
            case "password":
                if (item.length < 8) errors.value.password = ["Enter a password greater than 8 digits."]
                break;
        }
    })
    return Object.keys(errors.value).length === 0 ? true : false
}

const add = () => {
    let formData = new FormData()

    formData.append('name', editingUser.value.name)
    formData.append('email', editingUser.value.email)
    formData.append('type', editingUser.value.type)
    formData.append('password', editingUser.value.password)
    formData.append('blocked', 0)
    if (previewImage) formData.append('photo_url', previewImage)
    if (validations(formData)) emit("add", formData)
}

const save = () => {
    let formData = new FormData()

    formData.append('name', editingUser.value.name)
    formData.append('email', editingUser.value.email)
    formData.append('type', editingUser.value.type)

    if (editingUser.value.blocked == false) {
        formData.append('blocked', 0)
    } else {
        formData.append('blocked', 1)
    }

    if (previewImage) formData.append('photo_url', previewImage)
    if (validations(formData)) userStore.save(formData, props.user.id)
}

watch(() => props.user, (newUser) => {
    editingUser.value = newUser
},
    { immediate: true }
)
</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex p-title-box">
                    <h4 class="p-title me-auto" v-if="$route.name == 'User'">Editing User - {{ editingUser.name }} (#{{
                            editingUser.id
                    }})</h4>
                    <h4 class="p-title me-auto" v-if="$route.name == 'newUser'">Add a new User</h4>
                </div>
            </div>
        </div>
        <form class="pe-2 needs-validation" novalidate @submit.prevent="save">
            <div class="row mb-3">
                <div class="col-md-8 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-xl-6">
                                        <div class="mb-3 px-1">
                                            <label for="inputName" class="form-label">Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="inputName"
                                                placeholder="Enter a name" required v-model="editingUser.name" />
                                            <field-error-message :errors="errors"
                                                fieldName="name"></field-error-message>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="mb-3 px-1">
                                            <label for="inputEmail" class="form-label">E-mail address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="inputEmail"
                                                placeholder="Enter an e-mail" required v-model="editingUser.email" />
                                            <field-error-message :errors="errors"
                                                fieldName="email"></field-error-message>
                                        </div>
                                    </div>
                                    <div class="col-xl-6" v-if="$route.name == 'newUser'">
                                        <div class="mb-3 px-1">
                                            <label for="inputPassword" class="form-label">Password <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="inputPassword"
                                                placeholder="Enter a password" required
                                                v-model="editingUser.password" />
                                            <field-error-message :errors="errors"
                                                fieldName="password"></field-error-message>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="mb-3 px-1">
                                            <label for="type" class="form-label">Role <span
                                                    class="text-danger">*</span></label>
                                            <select v-if="userStore.user.type == 'EM' && editingUser.type != 'C'" id="type" name="type"
                                                class="form-select" v-model="editingUser.type">
                                                <option value="EM" selected>Manager</option>
                                                <option value="EC">Chef</option>
                                                <option value="ED">Delivery</option>
                                            </select>
                                            <select v-else id="type" name="type" class="form-select"
                                                v-model="editingUser.type">
                                                <option value="C">Customer</option>
                                            </select>
                                            <field-error-message :errors="errors"
                                                fieldName="type"></field-error-message>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="mb-3 px-1">
                                    <label class="form-label">Photo</label>
                                    <input type="file" class="form-control" name='upload' @change="handleUpload"
                                        required>
                                    <br>
                                    <img :src="photoFullUrl" class="img-thumbnail" v-if="$route.name == 'User'" />
                                    <field-error-message :errors="errors" fieldName="photo_url"></field-error-message>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="editingUser.type != 'C' && userStore.user.type == 'EM'">
                <div class="mb-3 d-flex justify-content-end">
                    <div class="mb-3 px-1">
                        <button type="button" class="btn btn-warning px-4 btn-add" @click="add"
                            v-if="$route.name == 'newUser'">
                            Add User
                        </button>
                        <button type="button" class="btn btn-warning px-4 btn-add" @click="save"
                            v-if="$route.name == 'User'">
                            Save User
                        </button>
                    </div>
                    <div class="mb-3 px-1">
                        <button type="button" class="btn btn-light px-4" @click="cancel">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.total_hours {
    width: 26rem;
}

#label {
    background-color: orange;
    color: white;
    padding: 0.5rem;
    font-family: sans-serif;
    border-radius: 0.3rem;
    cursor: pointer;
    margin-top: 1rem;

}
</style>
