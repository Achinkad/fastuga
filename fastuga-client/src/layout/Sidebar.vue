<script setup>
import { ref, onMounted, inject } from 'vue'
import { useUserStore } from '../stores/user.js'
const axios = inject('axios')
const serverBaseUrl = inject("serverBaseUrl");
const userStore = useUserStore()
var timer = null;
const orders = ref([])

const loadOrders = () => {
    if (userStore.user && userStore.user.type == 'C') {
        axios.get(serverBaseUrl + '/api/users/' + userStore.userId + '/orders')
            .then((response) => {
                orders.value = response.data.data
                console.log(orders.value)

            })
            .catch((error) => {
                console.log(error)
            })
    }
}

onMounted(() => {

    timer = setInterval(function () {
        loadOrders();
        clearInterval(timer)
    }, 1000)

})



</script>
<template>
    <nav id="sidebarMenu" class="d-md-block sidebar collapse">
        <div class="logo">
            <span style="vertical-align">FASTUGA</span>
        </div>
        <div class="position-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-item nav-item-title">Navigation</li>
                <li class="nav-item" v-if="userStore.user && userStore.user.type == 'EM'">
                    <router-link class="nav-link" :class="{ active: $route.name === 'Dashboard' }"
                        :to="{ name: 'Dashboard' }">
                        <i class="bi bi-house-door"></i>
                        Dashboard
                    </router-link>
                </li>
                <!--
            <li class="nav-item">
            <router-link class="nav-link" :class="{ active: $route.name === 'CurrentOrder' }"
            :to="{ name: 'CurrentOrder' }">
            <i class="bi bi-list-stars"></i>
            My Orders
        </router-link>
    </li>
-->
                <li class="nav-item d-flex justify-content-between align-items-center pe-3">
                    <router-link class="nav-link w-100 me-3" :class="{ active: $route.name === 'Orders' }"
                        :to="{ name: 'Orders' }">
                        <div v-if="userStore.user && userStore.user.type == 'EC'">
                            <i class="bi bi-basket3"></i>
                            Order-Items
                        </div>
                        <div v-else>
                            <i class="bi bi-basket3"></i>
                            Orders
                        </div>
                    </router-link>
                    <router-link class="link-secondary" :to="{ name: 'NewOrder' }" aria-label="Add a new order"
                        v-if="(userStore.user && userStore.user.type != 'EC' && userStore.user.type != 'ED') || !userStore.user">
                        <i class="bi bi-xs bi-plus-circle"></i>
                    </router-link>
                </li>



                <div v-if="userStore.user && userStore.user.type == 'EM'">
                    <li class="nav-item nav-item-title mt-3">Administration</li>
                    <li class="nav-item d-flex justify-content-between align-items-center pe-3">
                        <router-link class="nav-link w-100 me-3" :class="{ active: $route.name === 'Products' }"
                            :to="{ name: 'Products' }">
                            <i class="bi bi-egg-fried"></i>
                            Products
                        </router-link>
                        <router-link class="link-secondary" :to="{ name: 'newProduct' }" aria-label="Add a new order">
                            <i class="bi bi-xs bi-plus-circle"></i>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link class="nav-link" :class="{ active: $route.name === 'Users' }"
                            :to="{ name: 'Users' }">
                            <i class="bi bi-people"></i>
                            Users
                        </router-link>
                    </li>
                </div>
            </ul>

            <div v-if="userStore.user && userStore.user.type == 'C'">


                <ul class="nav flex-column">
                    <li class="nav-item nav-item-title">In preparation</li>

                    <li class="nav-item" v-for="order in orders" :key="order.id">
                        <div v-if="order.status == 'P'">
                            <router-link class="nav-link w-100 me-3"
                                :class="{ active: $route.name == 'Order' && $route.params.id == order.id }"
                                :to="{ name: 'Order', params: { id: order.id } }">
                                <i class="bi bi-bag"></i>
                                <span> Ticket nº {{ order.ticket_number }}</span>
                            </router-link>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
</template>

<style scoped>
#sidebarMenu {
    width: 260px;
    min-width: 260px;
    overflow-y: auto;
    background-color: #313a46;
    z-index: 2000;
}

.logo {
    height: 4.375rem;
    width: 260px;
    min-width: 260px;
    position: fixed;
    top: 0;
    color: #fff;
    text-decoration: none !important;
    font-size: 1.125rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
}

ul {
    padding: 2rem .75rem;
}

#orange {
    color: orange
}

.nav-item-title {
    padding: 12px 20px;
    letter-spacing: .05em;
    pointer-events: none;
    cursor: default;
    font-size: .6875rem;
    text-transform: uppercase;
    color: #7e8d9f;
    font-weight: 700;
}

.nav-link {
    display: block;
    padding: 10px 20px;
    font-size: .9375rem;
    position: relative;
    color: #7e8d9f;
}

.nav-link:hover {
    color: rgba(255, 255, 255, 0.9);
}

.nav-link .active {
    color: #fff;
}

.nav-link i {
    display: inline-block;
    line-height: 1.0625rem;
    margin: -3px 10px 0 0;
    font-size: 1.1rem;
    vertical-align: middle;
    width: 20px;
}
</style>
