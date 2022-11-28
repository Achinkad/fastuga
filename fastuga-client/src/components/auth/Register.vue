<script setup>
import { ref } from 'vue'

const credentials = ref({
    username: '',
    password: '',
    confirmpassword: ''
})

const emit = defineEmits(['register'])

const register = () => {
    // FALTA FAZER O register
    emit('register')
}
</script>

<template>
    <form class="row g-3 needs-validation" novalidate @submit.prevent="login">
        <h3 class="mt-5 mb-3">Register</h3>
        <hr>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputUsername" class="form-label">Username</label>
                <input type="text" class="form-control" id="inputUsername" required v-model="credentials.username">
            </div>
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="inputPassword" required v-model="credentials.password">
            </div>
            <div class="mb-3">
                <label for="inputPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="inputConfirmPassword" required v-model="credentials.password">
            </div>
        </div>
        <div class="mb-3 d-flex justify-content-center">
            <button type="button" class="btn btn-primary px-5" @click="login">Register</button>
        </div>
    </form>
</template>

