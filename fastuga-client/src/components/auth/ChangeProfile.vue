<script setup>
  import { ref, inject} from 'vue'
  import { useRouter } from 'vue-router'
  import { useUserStore } from '../../stores/user.js'
  const router = useRouter()
  const axios = inject('axios')
  const toast = inject('toast')
  const userStore = useUserStore()
  const serverBaseUrl = inject("serverBaseUrl")
</script>


<template>
   
    <h3 class="mt-5 mb-3">Profile</h3>

    <hr />
        <!--
    <div class="d-flex flex-wrap justify-content-between">
      <div class="w-75 pe-4">
        <div class="mb-3">
          <label for="inputName" class="form-label">Name</label>
          <input
            type="text"
            class="form-control"
            id="inputName"
            placeholder="User Name"
            required
            v-model="editingUser.name"
          />
          <field-error-message :errors="errors" fieldName="name"></field-error-message>
        </div>
 
        <div class="mb-3 px-1">
          <label for="inputEmail" class="form-label">Email</label>
          <input
            type="email"
            class="form-control"
            id="inputEmail"
            placeholder="Email"
            required
            v-model="editingUser.email"
          />
          <field-error-message :errors="errors" fieldName="email"></field-error-message>
        </div>
        <div class="mb-3 px-1" v-if="$route.name == 'newUser'">
          <label for="inputPassword" class="form-label">Password</label>
          <input
            type="password"
            class="form-control"
            id="inputPassword"
            placeholder="Password"
            required
            v-model="editingUser.password"
          />
          <field-error-message :errors="errors" fieldName="email"></field-error-message>
        </div>
         <div class="mb-3">
            <label for="type">Role:</label>
            <select id="type" name="type"  v-model="editingUser.type">
              <option value="EM">Manager</option>
              <option value="EC">Chef</option>
              <option value="ED">Delivery</option>
            </select>
          <field-error-message :errors="errors" fieldName="type"></field-error-message>
        </div>

        <div v-if="$route.name == 'User'" class="mb-3">
            <label for="blocked">Blocked:</label>
            <select id="blocked" name="blocked"  v-model="editingUser.blocked">
              <option value="0">Unblocked</option>
              <option value="1">Blocked</option>
            </select>
          <field-error-message :errors="errors" fieldName="blocked"></field-error-message>
        </div>

      </div>
      <div class="w-25">
        <div class="mb-3">
          <label class="form-label">Photo</label>
          <div class="form-control text-center">
            <img :src="photoFullUrl" class="w-100" />
          
              <input type="file" id="actual-btn" hidden/>
              <label for="actual-btn" id="label">Choose File</label>
            
          </div>
          
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
  </form>
  -->
</template>