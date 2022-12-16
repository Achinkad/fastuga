<script setup>
import { ref, computed, onMounted, inject,watch } from 'vue'
import { useRouter } from 'vue-router'
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

var value_role=ref("all")

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
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex">
                    <div class="p-title-box">
                        <div class="p-title-right">
                            <h4 class="p-title">Users</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-xl-8">
                                <div class="d-flex">
                                    <div class="d-flex align-items-center">
                                        <label for="selectRole" class="me-2">Role</label>
                                        <select class="form-select" id="selectRole" v-model="value_role">
                                            <option value="all" selected>Any</option>
                                            <option value="C">Customers</option>
                                            <option value="EC">Employees Chef</option>
                                            <option value="ED">Employees Delivery</option>
                                            <option value="EM">Employees Manager</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 d-flex justify-content-end align-items-end">
                                <button type="button" class="btn btn-warning px-4 btn-add" @click="addUser"><i
                                    class="bi bi-xs bi-plus-circle me-2"></i> Add User
                                </button>
                            </div>
                            <user-table
                                :users="users"
                                :showId="false"
                                @edit="editUser"
                                @forceRerender="forceRerender"
                            ></user-table>
                            <div class="d-flex justify-content-end mt-3">
                                <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadUsers" :limit="5"></Bootstrap5Pagination>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.filter-div {
    min-width: 12rem;
}

.total-filtro {
    margin-top: 2.3rem;
}
</style>
