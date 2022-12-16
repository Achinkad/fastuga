<script setup>
import { ref, onMounted, inject, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user.js'
import { useOrderStore } from '../../stores/orders.js'
import { Bootstrap5Pagination } from 'laravel-vue-pagination'

import OrderTable from "./OrderTable.vue"
import OrderItemsTable from "./OrderItemsTable.vue"

const axios = inject('axios')
const serverBaseUrl = inject("serverBaseUrl")

const userStore = useUserStore()
const orderStore = useOrderStore()
const router = useRouter()

const order_items = ref([])
const pagination = ref({})
const status = ref("all")

const props = defineProps({
    onlyCurrentOrders: {
        type: Boolean,
        default: false
    }
})

const loadOrders = (page = 1) => {
    orderStore.load_orders(page, status.value)
}

const orders = computed(() => {
    pagination.value = orderStore.get_page()
    return orderStore.get_orders()
})

const addOrder = () => {
    router.push({ name: "NewOrder" });
}

const editOrder = (order) => {
    router.push({ name: "Order", params: { id: order.id } });
}

watch(status, () => { loadOrders() })

onMounted(() => { loadOrders() })

</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex">
                    <div class="p-title-box">
                        <div class="p-title-right">
                            <h4 class="p-title" v-if="!userStore.user || userStore.user.type != 'EC'">Orders (Total: 83)</h4>
                            <h4 class="p-title" v-if="userStore.user && userStore.user.type == 'EC'">Order Items</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="d-flex">
                <div class="col-3" v-if="userStore.user && userStore.user.type=='EM'">
                    <label for="selectCompleted" class="form-label">Filter by status:</label>
                    <select class="form-select" id="selectCompleted" v-model="status">
                        <option value="all" selected>Any</option>
                        <option value="P">Preparing Orders</option>
                        <option value="R">Ready Orders</option>
                        <option value="D">Delivered Orders</option>
                        <option value="C">Canceled Orders</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="ms-auto align-self-center">
            <div class="mx-0 mt-2" v-if="!userStore.user || (userStore.user && (userStore.user.type == 'EM' || userStore.user.type == 'C'))">
                <button type="button" class="btn btn-warning px-4 btn-add" @click="addOrder">
                    <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Order
                </button>
            </div>
        </div>

        <order-table :orders="orders" :showId="true" @edit="editOrder" v-if="userStore.user && userStore.user.type != 'EC'"></order-table>
        <order-items-table :order_items="order_items" v-if="userStore.user && userStore.user.type == 'EC'"></order-items-table>

        <div v-if="userStore.user" class="d-flex justify-content-end mt-3">
            <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrders" :limit="5"></Bootstrap5Pagination>
        </div>
    </div>
</template>

<style scoped>
.btn-addOrder {
    margin-top: 1.85rem;
}

button[type="button"] {
    background-color: #727cf5 !important;
    color: #fff;
    border-color: #727cf5;
    border-radius: 0.15rem;
    box-shadow: 0px 2px 6px 0px rgba(114, 124, 245, 0.5);
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
</style>
