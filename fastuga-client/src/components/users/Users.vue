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


  var value_role=ref("-1");

  const totalUsers = computed(() => {
    return users.value.length
  })

  const loadUsers = (page = 1) => {
    axios.get(serverBaseUrl+'/users?page='+page,{
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
        <option value="-1" selected>Any</option>
        <option value="C">Customers</option>
        <option value="EC">Employees Chef</option>
        <option value="ED">Employees Delivery</option>
        <option value="EM">Employees Manager</option>
      </select>
      <!-- VAI SER PRECISO ADICIONAR O BOTÃO PARA ADICIONAR USERS, O MANAGER CONSEGUE CRIAR CONTAS PARA OS EMPREGADOS(MANAGERS(?),CHEFS E DELIVERY MAN)
      <div class="mx-0 mt-2">
        <button type="button" class="btn btn-warning px-4 btn-addtask" @click="addProduct">
        <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Product</button>
        </div>
        -->
  </div>
  <user-table
    :users="users"
    :showId="false"
    @edit="editUser"
  ></user-table>
  <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadUsers" :limit="5"></Bootstrap5Pagination>
  <hr>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}
.total-filtro {
  margin-top: 2.3rem;
}
</style>

