<script setup>
  import { ref, inject, onMounted,computed} from 'vue'
  import { useRouter } from 'vue-router'
  import { useUserStore } from '../../stores/user.js'
  import avatarNoneUrl from '@/assets/avatar-none.png'



  const router = useRouter()
  const axios = inject('axios')
  const toast = inject('toast')
  const userStore = useUserStore()

  const serverBaseUrl = inject("serverBaseUrl")

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
                reader.onload = e =>{
                    previewImage = e.target.result;
                 
                }
}

  const customer = ref(newCustomer)


  const dataAsString = () => {
    return JSON.stringify(customer.value)
  }

   let originalValueStr='';
  const loadCustomer = () => {
  
    errors.value = null
    if(userStore.user && userStore.user.type == "C"){
        axios.get(serverBaseUrl+'/api/customers/users/' + userStore.user.id)
          .then((response) => {
            customer.value = response.data.data
            originalValueStr = dataAsString()
          })
          .catch((error) => {
            console.log(error)
          })
        }
    else{
      customer.value.user = userStore.user

    }
  }

  const save = () => {
  let formData = new FormData()
    formData.append('name', customer.value.user.name);
    formData.append('email', customer.value.user.email);
    formData.append('type', customer.value.user.type);
    if(customer.value.user.blocked == false){
        formData.append('blocked', 0);
        }else{
          formData.append('blocked', 1);
        }
    if (previewImage != null) {
        formData.append('photo_url', previewImage);
    }

    formData.append('_method', 'PUT');
    router.back();
    userStore.save(formData,userStore.user.id);

    }

  const photoFullUrl = computed(() => {
    return customer.value.user.photo_url
    ? serverBaseUrl + "/storage/fotos/" + customer.value.user.photo_url
    : avatarNoneUrl
})
const cancel = () => {
    
    router.back()
}

 
onMounted(() => {
  loadCustomer()
})


const updateCostumer = () => {
      errors.value = null
        let formData = new FormData()
        formData.append('phone', customer.value.phone);
        formData.append('points', customer.value.points);
        if(customer.value.nif!=undefined){
            formData.append('nif', customer.value.nif);
        }
        if(customer.value.default_payment_type!=undefined){
            formData.append('default_payment_type', customer.value.default_payment_type);
        }
        if(customer.value.default_payment_reference!=undefined){
            formData.append('default_payment_reference', customer.value.default_payment_reference);
        }
        if (previewImage != null) {
        formData.append('photo_url', previewImage);
    }
        formData.append('name', customer.value.user.name);
        formData.append('email', customer.value.user.email);
        formData.append('type', customer.value.user.type);
        if(customer.value.user.blocked == false){
        formData.append('blocked', 0);
        }else{
          formData.append('blocked', 1);
        }

        axios.put(serverBaseUrl+'/api/customers/' + customer.value.id, formData)
        .then((response) => {
          customer.value = response.data.data
          originalValueStr = dataAsString()
          console.log("aaa")
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
  }





  
</script>
fieldName

<template>
     <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="d-flex p-title-box">

    <h4 class="p-title me-auto">Profile</h4>
          </div>
        </div>
      </div>

       <form>
        <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="row mb-2">

    <div class="d-flex flex-wrap justify-content-between">
      <div class="col-xl-6">

        <div class="mb-3 px-1">
          <label for="inputName" class="form-label">Name</label>
          <input
            type="text"
            class="form-control"
            id="inputName"
            placeholder="User Name"
                        required
            v-model="customer.user.name"
          />
          <field-error-message :errors="errors" fieldName="name"></field-error-message>
        </div>
      </div>
     
      <div class="col-xl-6">

        <div class="mb-3 px-1">
          <label for="inputEmail" class="form-label">Email</label>
          <input
            type="email"
            class="form-control"
            id="inputEmail"
            placeholder="Email"
            required
            v-model="customer.user.email"
          />
          <field-error-message :errors="errors" fieldName="email"></field-error-message>
        </div>
      </div>
      
      <div class="col-xl-6">

        <div class="mb-3 px-1" v-if="userStore.user && userStore.user.type == 'C'">
          <label for="inputPhone" class="form-label">Phone</label>
          <input
            type="text"
            class="form-control"
            id="inputPhone"
            placeholder="Phone"
            required
            v-model="customer.phone"
          />
          <field-error-message :errors="errors" fieldName="phone"></field-error-message>
        </div>
      </div>

      <div class="col-xl-6">

        <div class="mb-3" v-if="userStore.user && userStore.user.type == 'C'">
          <label for="inputNif" class="form-label">NIF</label>
          <input
            type="text"
            class="form-control"
            id="inputNif"
            placeholder="NIF"
                        
            v-model="customer.nif"
          />
          <field-error-message :errors="errors" fieldName="name"></field-error-message>
        </div>
      </div>
      <div class="col-xl-6">

        <div class="mb-3" v-if="userStore.user && userStore.user.type == 'C'">
            <label class="form-label" for="payment_type">Payment Type</label>
            <select id="payment_type" name="payment_type" class="form-select" v-model="customer.default_payment_type">
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
                <input type="text" class="form-control" id="inputPaymentReference" v-model="customer.default_payment_reference"/>
                
            
        </div>
      </div>
        
      <div class="col-xl-6">

                <label class="form-label">Photo</label>
                <div class="mb-3">
                    <img :src="photoFullUrl" class="img-thumbnail" />
                    <input type="file" class="form-control" name='upload' @change="handleUpload"
                        required>
                    <field-error-message :errors="errors" fieldName="photo_url"></field-error-message>
                </div>
      </div>
    </div>


    
    <div class="mb-3 d-flex justify-content-end">
            <button  v-if="userStore.user.type == 'C'" type="button" class="btn btn-primary px-5" @click="updateCostumer">Save
                User</button>
            <button v-else type="button" class="btn btn-primary px-5" @click="save">Save
                User</button>
      <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
    </div>
  </div>
</div>
</div>
</div>
</div>
  </form>
  </div>



</template>