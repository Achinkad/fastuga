<script setup>
import { ref, watch, computed, inject } from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'
import { useUserStore } from '../../stores/user.js'

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

const emit = defineEmits(["save", "cancel", "add"]);

const editingUser = ref(props.user)

watch(
  () => props.user,
  (newUser) => {
    editingUser.value = newUser
  },
  { immediate: true }
)

const photoFullUrl = computed(() => {
  return editingUser.value.photo_url
    ? serverBaseUrl + "/storage/fotos/" + editingUser.value.photo_url
    : avatarNoneUrl
})

const add = () => {
  let formData = new FormData()
  formData.append('name', editingUser.value.name);
  formData.append('email', editingUser.value.email);
  formData.append('type', editingUser.value.type);
  formData.append('password', editingUser.value.password);
  formData.append('blocked', 0);


  if (previewImage != null) {
    formData.append('photo_url', previewImage);
  }


  emit("add", formData);
}

const save = () => {
  let formData = new FormData()
  formData.append('name', editingUser.value.name);
  formData.append('email', editingUser.value.email);
  formData.append('type', editingUser.value.type);

  if (editingUser.value.blocked == false) {
    formData.append('blocked', 0);
  } else {
    formData.append('blocked', 1);
  }



  if (previewImage != null) {
    formData.append('photo_url', previewImage);
  }
  formData.append('_method', 'PUT');

  userStore.save(formData, props.user.id);

}

const cancel = () => {
  emit("cancel", editingUser.value);
}
</script>

<template>
  <div class="container-fluid">

    <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
      <div class="row">
        <div class="col-12">
          <div class="d-flex p-title-box">

            <h4 class="p-title me-auto" v-if="$route.name == 'User'">User #{{ editingUser.id }}</h4>
            <h4 class="p-title me-auto" v-if="$route.name == 'newUser'">Adding New User</h4>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="row mb-2">



                <div class="d-flex flex-wrap justify-content-between">
                  <div class="col-xl-6">

                    <div class="mb-3 px-1">
                      <label for="inputName" class="form-label">Name</label>
                      <input type="text" class="form-control" id="inputName" placeholder="User Name" required
                        v-model="editingUser.name" />
                      <field-error-message :errors="errors" fieldName="name"></field-error-message>
                    </div>
                  </div>
                  <div class="col-xl-6">

                    <div class="mb-3 px-1">
                      <label for="inputEmail" class="form-label">Email</label>
                      <input type="email" class="form-control" id="inputEmail" placeholder="Email" required
                        v-model="editingUser.email" />
                      <field-error-message :errors="errors" fieldName="email"></field-error-message>
                    </div>
                  </div>
                  <div class="col-xl-6" v-if="$route.name == 'newUser'">

                    <div class="mb-3 px-1">
                      <label for="inputPassword" class="form-label">Password</label>
                      <input type="password" class="form-control" id="inputPassword" placeholder="Password" required
                        v-model="editingUser.password" />
                      <field-error-message :errors="errors" fieldName="password"></field-error-message>
                    </div>
                  </div>
                  <div class="col-xl-6">

                    <div class="mb-3 px-1">
                      <label for="type" class="form-label">Role</label>
                      <select id="type" name="type" class="form-select" v-model="editingUser.type">
                        <option value="EM">Manager</option>
                        <option value="EC">Chef</option>
                        <option value="ED">Delivery</option>
                      </select>
                      <field-error-message :errors="errors" fieldName="type"></field-error-message>
                    </div>
                  </div>
                  <div class="col-xl-6" v-if="$route.name == 'User'">

                    <div class="mb-3 px-1">
                      <label for="blocked" class="form-label">Blocked</label>
                      <select id="blocked" name="blocked" class="form-select" v-model="editingUser.blocked">
                        <option value="false">Unblocked</option>
                        <option value="true">Blocked</option>
                      </select>
                      <field-error-message :errors="errors" fieldName="blocked"></field-error-message>
                    </div>
                  </div>

                  <div class="col-xl-6">


                    <div class="mb-3 px-1">
                      <label class="form-label">Photo</label>
                      <br>
                      <img :src="photoFullUrl" class="img-thumbnail" v-if="$route.name == 'User'" />

                      <input type="file" class="form-control" name='upload' @change="handleUpload" required>
                      <field-error-message :errors="errors" fieldName="photo_url"></field-error-message>
                    </div>
                  </div>

                </div>
                <div class="mb-3 d-flex justify-content-end">
                  <button type="button" class="btn btn-primary px-5" @click="add" v-if="$route.name == 'newUser'">Add
                    User</button>
                  <button type="button" class="btn btn-primary px-5" @click="save" v-if="$route.name == 'User'">Save
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
