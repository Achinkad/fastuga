<script setup>
  import { ref, computed, onMounted, inject,watch } from 'vue'
  import {useRouter} from 'vue-router'
  import UserTable from "./UserTable.vue"
  import { Bootstrap5Pagination } from 'laravel-vue-pagination';
  
  const router = useRouter()

  const axios = inject('axios')

  const users = ref([])
  const pagination = ref({})

const serverBaseUrl = inject("serverBaseUrl")

const forceRerender = () => {
    loadUsers()
    console.log("reload")
}
  var value_role=ref("all");



  const loadUsers = (page = 1) => {
    axios.get(serverBaseUrl+'/api/users?page='+page,{
    params:{
      type: value_role.value
      }
      
  })
        .then((response) => {
          users.value = response.data.data
          pagination.value = response.data
        })
        .catch((error) => {
          console.log(error)
        })
    }

  watch(value_role,() =>{
  console.log(value_role.value)
  loadUsers()
}) 
  const addUser = () => {
    router.push({ name: "newUser" });
  };
  const editUser = (user) => {
    router.push({ name: 'User', params: { id: user.id } })
  }

  onMounted (() => {
    loadUsers()
  })
</script>

<template>
  <h3 class="mt-5 mb-3">Users</h3>
  <hr>
  <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectRole" class="form-label">Filter by role:</label>
      <select class="form-select" id="selectRole" v-model="value_role">
        <option value="all" selected>Any</option>
        <option value="C">Customers</option>
        <option value="EC">Employees Chef</option>
        <option value="ED">Employees Delivery</option>
        <option value="EM">Employees Manager</option>
      </select>
      <div class="mx-0 mt-2">
        <button type="button" class="btn btn-warning px-4 btn-addtask" @click="addUser"><i
            class="bi bi-xs bi-plus-circle"></i>&nbsp; Add User</button>
      </div>
  </div>
  <user-table
    :users="users"
    :showId="false"
    @edit="editUser"
    @forceRerender="forceRerender"
  ></user-table>
  <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadUsers" :limit="5"></Bootstrap5Pagination>
  <hr>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}
button[type="button"] {
    background-color: rgb(255,165,0) !important;
    color: #fff;
    border-color: rgb(255,165,0);
    border-radius: 0.15rem;
    box-shadow: 0px 2px 6px 0px rgb(255,165,0);
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

.btn-add {
    position: relative;
    top: .775rem;
}
.total-filtro {
  margin-top: 2.3rem;
}
</style>

