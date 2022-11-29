<script setup>
  import { ref, computed, onMounted, inject } from 'vue'
  import {useRouter} from 'vue-router'
  import UserTable from "./UserTable.vue"
  import { Bootstrap5Pagination } from 'laravel-vue-pagination';
  
  const router = useRouter()

  const axios = inject('axios')

  const users = ref([])
  const pagination = ref({})

  const serverBaseUrl ="http://fastuga.test";

  const totalUsers = computed(() => {
    return users.value.length
  })

  const loadUsers = (page = 1) => {
    axios.get(serverBaseUrl+'/api/users?page='+page)
        .then((response) => {
          users.value = response.data.data
          pagination.value = response.data
        })
        .catch((error) => {
          console.log(error)
        })
    }

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
  <user-table
    :users="users"
    :showId="false"
    @edit="editUser"
  ></user-table>
  <hr>
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

