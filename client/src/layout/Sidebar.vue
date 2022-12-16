<script setup>
import { ref, onMounted, inject ,watch} from 'vue'
import { useUserStore } from '../stores/user.js'

const axios = inject('axios')
const serverBaseUrl = inject("serverBaseUrl")

const userStore = useUserStore()
const orders = ref([])

var timer = null;

const loadOrders = () => {
    if (userStore.user && userStore.user.type == 'C') {
        axios.get(serverBaseUrl + '/api/users/' + userStore.userId + '/orders')
            .then((response) => {
                orders.value = response.data.data
            })
            .catch((error) => {
                console.log(error)
            })
    }
}
onMounted(() => {
  loadOrders();

  watch(
    () => userStore.userId,
    (userId) => {
      if (userId) {
        loadOrders();
      }
    },
  );
});



</script>
<template>
    <nav id="sidebarMenu" class="d-md-block sidebar collapse">
        <div class="logo">
            <router-link class="nav-link" :to="{ name: 'Dashboard' }">
                <span style="vertical-align">Fastuga.</span>
            </router-link>
        </div>
        <div class="position-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-item nav-item-title">Navigation</li>
                <li class="nav-item">
                    <router-link class="nav-link" :class="{ active: $route.name === 'Dashboard' }"
                        :to="{ name: 'Dashboard' }">
                        <i class="bi bi-house"></i>
                        Dashboard
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" :class="{ active: $route.name === 'Orders' }"
                        :to="{ name: 'Orders' }">
                        <div v-if="userStore.user && userStore.user.type == 'EC'">
                            <i class="bi bi-bag" style="font-size: 17px!important;"></i>
                            Order-Items
                        </div>
                        <div v-else>
                            <i class="bi bi-bag" style="font-size: 17px!important;"></i>
                            Orders
                        </div>
                    </router-link>
                </li>

                <div v-if="userStore.user && userStore.user.type == 'EM'">
                    <li class="nav-item nav-item-title mt-3">Administration</li>
                    <li class="nav-item">
                        <router-link class="nav-link" :class="{ active: $route.name === 'Products' }"
                            :to="{ name: 'Products' }">
                            <i class="bi bi-egg-fried"></i>
                            Products
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

                <div v-if="userStore.user && userStore.user.type == 'C'">
                    <li class="nav-item nav-item-title mt-3">In preparation</li>
                    <li class="nav-item" v-for="order in orders" :key="order.id">
                        <div v-if="order.status == 'P'">
                            <router-link class="nav-link"
                                :class="{ active: $route.name == 'Order' && $route.params.id == order.id }"
                                :to="{ name: 'Order', params: { id: order.id } }">
                                <i class="bi bi-ticket"></i>
                                <span> Ticket nº {{ order.ticket_number }}</span>
                            </router-link>
                        </div>
                    </li>
                </div>
            </ul>
        </div>
    </nav>
</template>

<style scoped>
#sidebarMenu {
    width: 260px;
    min-width: 260px;
    overflow-y: auto;
    background-color: #1f2937;
    z-index: 2000;
    box-shadow: 0 0 35px 0 rgba(154, 161, 171, .15) !important;
}

.logo {
    height: 4.375rem;
    width: 260px;
    min-width: 260px;
    position: fixed;
    top: 0;
    color: #fff !important;
    text-decoration: none !important;
}

.logo .nav-link {
    height: 4.375rem;
    width: 260px;
    font-size: 1.313rem;
    font-weight: 800;
    color: #fff !important;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-link:hover {
    color: #f2f4f6;
    background-color: #374151;
    border-radius: 5px;
}

ul {
    padding: 3rem .75rem;
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

ul > :first-child {
    padding: 0 20px 12px 20px !important;
}

.nav-link {
    display: block;
    padding: 10px 20px;
    font-size: .9375rem;
    position: relative;
    color: #ccc;
    font-weight: 600;
}

.sidebar .nav-link.active {
    color: #f0bc74;
    font-weight: 700;
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
