<script setup>
import { ref, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user.js'

const router = useRouter()
const axios = inject('axios')
const toast = inject('toast')
const userStore = useUserStore()
const serverBaseUrl = inject("serverBaseUrl")
const errors = ref(null)

const passwords = ref({
  password: '',
  password_confirm: ''
})

const emit = defineEmits(['changedPassword'])



const editPassword = () => {
  if (passwords.value.password == passwords.value.password_confirm) {
    axios.patch(serverBaseUrl + '/api/users/' + userStore.user.id + '/change_password', {
      password: passwords.value.password
    })
      .then((response) => {
        toast.success('Password was updated successfully.')
        router.back()
      })
      .catch((error) => {
        if (error.response.status == 422) {
          errors.value = error.response.data.data

          toast.error('user password was not updated due to validation errors!')
        } else {
          toast.error('user password was not updated due to unknown server error!')
        }
      })
  } else {
    toast.error("The passwords aren't matching")
  }
}

const changePassword = () => {
  editPassword()
  emit('changedPassword')
}
</script>

<template>
  <div class="container-fluid">

    <form class="row g-3 needs-validation" novalidate @submit.prevent="changePassword">
      <div class="row">
        <div class="col-12">
          <div class="d-flex p-title-box">
            <h4 class="p-title me-auto">Change Password</h4>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="row mb-2">
                <div class="col-xl-6">
                  <div class="mb-3">
                    <label for="inputNewPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="inputNewPassword" required
                      v-model="passwords.password">
                    <field-error-message :errors="errors" fieldName="password"></field-error-message>
                  </div>
                </div>
                <div class="col-xl-6">
                  <div class="mb-3">
                    <label for="inputPasswordConfirm" class="form-label">Password Confirmmation</label>
                    <input type="password" class="form-control" id="inputPasswordConfirm" required
                      v-model="passwords.password_confirm">
                  </div>
                </div>
                <div class="mb-3 d-flex justify-content-end">
                  <div class="mb-3 px-1">
                    <button type="button" class="btn btn-warning px-4 btn-add" @click="changePassword">Change
                      Password</button>
                  </div>
                  <div class="mb-3 px-1">
                    <button type="button" class="btn btn-light px-5" @click="cancel">
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

