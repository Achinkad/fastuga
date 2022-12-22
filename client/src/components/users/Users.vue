<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import UserTable from "./UserTable.vue"
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { useUserStore } from '../../stores/user.js'

const userStore = useUserStore()
const router = useRouter()
const pagination_aux = ref({});
var total_users = 0
var value_role = ref("all")

const forceRerender = () => {
    loadUsers()
}

const loadUsers = (page = 1) => {
    userStore.load_users(page, value_role.value)
}

const pagination = computed(() => { return userStore.get_page() })
const users = computed(() => { return userStore.get_users() })

const total = computed(() => {
    pagination_aux.value = userStore.get_page()

    if (pagination_aux.value.meta != undefined) {
        total_users = pagination_aux.value.meta.total
    }

    return total_users
})

watch(value_role, () => {
    loadUsers()
})

const addUser = () => {
    router.push({ name: "newUser" });
};
const editUser = (user) => {
    router.push({ name: 'User', params: { id: user.id } })
}

onMounted(() => {
    loadUsers()
})
</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex p-title-box">
                    <h4 class="p-title me-auto">Users List</h4>
                    <div class="p-title-right">
                        <h6 class="p-title">Viewing {{ userStore.total_users }} of {{ total }}</h6>
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
                            <user-table :users="users" @edit="editUser" @forceRerender="forceRerender"></user-table>
                            <div class="d-flex justify-content-end mt-3">
                                <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadUsers" :limit="5">
                                </Bootstrap5Pagination>
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
