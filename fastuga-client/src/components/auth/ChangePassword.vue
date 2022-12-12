<script setup>
  import { ref, inject} from 'vue'
  import { useRouter } from 'vue-router'
  import { useUserStore } from '../../stores/user.js'
  const router = useRouter()
  const axios = inject('axios')
  const toast = inject('toast')
  const userStore = useUserStore()
  const serverBaseUrl = inject("serverBaseUrl")



  const passwords = ref({
        //current_password: '',
        password: '',
        password_confirm: ''
    })

  const emit = defineEmits(['changedPassword'])


  //let originalValueStr = ''


  const editPassword = () => {
    if (passwords.value.password==passwords.value.password_confirm){
    axios.patch(serverBaseUrl + '/api/users/'+ userStore.user.id +'/change_password', {
        password: passwords.value.password
      })
      .then((response) => {
        //order.value = response.data.data
        //originalValueStr = dataAsString()
        toast.success('Password was updated successfully.')
        router.back()
      })
      .catch((error) => {
        if (error.response.status == 422) {
          toast.error('user password was not updated due to validation errors!')
          errors.value = error.response.data.errors
        } else {
          toast.error('user password was not updated due to unknown server error!')
        }
      })
    }else{
      toast.error("The passwords aren't matching")
    }
  }

  const changePassword = () => {
      // FALTA FAZER O LOGIN
      editPassword()
      emit('changedPassword')
  }
</script>

<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    @submit.prevent="changePassword"
  >
    <h3 class="mt-5 mb-3">Change Password</h3>
    <hr>
    <div class="mb-3">
      <div class="mb-3">
        <label
          for="inputNewPassword"
          class="form-label"
        >New Password</label>
        <input
          type="password"
          class="form-control"
          id="inputNewPassword"
          required
          v-model="passwords.password"
        >
      </div>
    </div>
    <div class="mb-3">
      <div class="mb-3">
        <label
          for="inputPasswordConfirm"
          class="form-label"
        >Password Confirmmation</label>
        <input
          type="password"
          class="form-control"
          id="inputPasswordConfirm"
          required
          v-model="passwords.password_confirm"
        >
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-center">
      <button
        type="button"
        class="btn btn-primary px-5"
        @click="changePassword"
      >Change Password</button>
    </div>
  </form>
</template>

