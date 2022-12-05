<script setup>
import { ref,inject } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()
const axios = inject('axios')
const toast = inject('toast')
const serverBaseUrl = inject("serverBaseUrl")


const credentials = ref({
        username: '',
        password: '',
        confirmpassword: ''
})
/*
const newUser = ref({
    name: '',
    username: '',
    password: '',
    type: 'C',
    blocked: 0
})


const newCostumer = ref({
    user_id: '',
    phone: '',
    points: 0,
    nif: '',
    default_payment_type: '',
    default_payment_reference: ''
})
*/
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
//const user = ref(newUser)
const customer = ref(saveCustomer)
const errors = ref(null)



const emit = defineEmits(['register'])

/*
const dataAsString = () => {
    return JSON.stringify(user.value)
  }
*/
const dataAsString = () => {
    return JSON.stringify(customer.value)
  }
/*
  const saveUser = () => {
      errors.value = null
      axios.post(serverBaseUrl+'/api/users', user.value)
        .then((response) => {
          user.value = response.data.data
          originalValueStr = dataAsString()
          toast.success('User #' + user.value.id + ' was created successfully.')
          router.back()
        })
        .catch((error) => {
          if (error.response.status == 422) {
              toast.error('User #' + props.id + ' was not created due to validation errors!')
              errors.value = error.response.data.errors
            } else {
              toast.error('User #' + props.id + ' was not created due to unknown server error!')
            }
        })
  }
 */ 
 let originalValueStr = ''

  const saveCostumer = () => {
      errors.value = null
      if(credentials.value.password == credentials.value.confirmpassword){
        let formData = new FormData()
        formData.append('phone', saveCustomer.value.phone);
        formData.append('points', saveCustomer.value.points);
        if(saveCustomer.value.nif!=undefined){
            formData.append('nif', saveCustomer.value.nif);
        }
        if(saveCustomer.value.default_payment_type!=undefined){
            formData.append('default_payment_type', saveCustomer.value.default_payment_type);
        }
        if(saveCustomer.value.default_payment_reference!=undefined){
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
    }else{
        toast.error("The passwords aren't matching")

    }
  }

const register = () => {
    saveCostumer()
    emit('register')
}
</script>

<template>
    <form class="row g-3 needs-validation" novalidate @submit.prevent="login">
        <h3 class="mt-5 mb-3">Register</h3>
        <hr>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputName" class="form-label">Name</label>
                <input type="text" class="form-control" id="inputName" required v-model="saveCustomer.name">
            </div>
        </div>
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
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputConfirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="inputConfirmPassword" required v-model="credentials.confirmpassword">
        </div>
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputPhone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="inputPhone" required v-model="saveCustomer.phone">
            </div>
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputNif" class="form-label">NIF</label>
                <input type="text" class="form-control" id="inputNif" v-model="saveCustomer.nif">
            </div>
        </div>
        <div class="mb-3">
            <label for="payment_type">Payment Type</label>
            <select id="payment_type" name="payment_type" v-model="saveCustomer.default_payment_type">
                <option value="VISA">Visa</option>
                <option value="PAYPAL">PayPal</option>
                <option value="MBWAY">MBWay</option>
            </select>
        <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="inputPaymentReference" class="form-label">Default Payment Reference</label>
                <input type="text" class="form-control" id="inputPaymentReference" v-model="saveCustomer.default_payment_reference">
            </div>
        </div>
        <div class="mb-3 d-flex justify-content-center">
            <button type="button" class="btn btn-primary px-5" @click="register">Register</button>
        </div>
    </form>
</template>

