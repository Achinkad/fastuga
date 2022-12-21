<script setup>
import { ref, onMounted, watch, computed  } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user.js'
import { useOrderStore } from '../../stores/order.js'
import { Bootstrap5Pagination } from 'laravel-vue-pagination'

import OrderTable from "./OrderTable.vue"
import OrderItemsTable from "./OrderItemsTable.vue"


const userStore = useUserStore()
const orderStore = useOrderStore()
const router = useRouter()
const pagination_aux = ref({});

const status = ref("all")

var total_orders = 0

const props = defineProps({
    onlyCurrentOrders: {
        type: Boolean,
        default: false
    }
})

const loadOrders = (page = 1) => { orderStore.load_orders(page, status.value) }
const loadOrderItems = (page = 1) => { orderStore.loadOrderItems(page) }

const total = computed(() => {
    pagination_aux.value = orderStore.get_page()
    if (pagination_aux.value.meta != undefined) {
       total_orders = pagination_aux.value.meta.total
    }
    return total_orders
})

const anonymous_orders = computed(() => { return orderStore.get_anonymous_orders })
const orders = computed(() => { return orderStore.get_orders() })
const order_items = computed(() => { return orderStore.get_order_items() })
const pagination = computed(() => { return orderStore.get_page() })

const addOrder = () => { router.push({ name: "NewOrder" }) }
const editOrder = (order) => { router.push({ name: "Order", params: { id: order.id } }) }

watch(status, () => { loadOrders() })
onMounted(() => {
    if (userStore.user && userStore.user.type == 'EC') {
        loadOrderItems()
    } else if (userStore.user) {
        loadOrders()
    }
})

</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex p-title-box">
                    <h4 class="p-title me-auto" v-if="!userStore.user || userStore.user.type != 'EC'">Orders List</h4>
                    <h4 class="p-title me-auto" v-if="userStore.user && userStore.user.type == 'EC'">Order Items</h4>
                    <div class="p-title-right">
                        <h6 class="p-title">Viewing {{orderStore.total_orders}} of {{total}}</h6>
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
                                    <div  class="d-flex align-items-center">
                                        <label for="selectCompleted" class="me-2">Status</label>
                                        
                                        <select  class="form-select" id="selectCompleted" v-model="status">
                                            <option value="all" selected>Any</option>
                                            <option value="P">Preparing</option>
                                            <option value="R">Ready</option>
                                            <option value="D">Delivered</option>
                                            <option value="C">Canceled</option>
                                        </select>
                                    </div>
                                   <!-- <div v-if = "userStore.user.type == 'ED'" class="d-flex align-items-center">
                                        <label for="selectCompleted" class="me-2">Status</label>
                                        
                                        <select  class="form-select" id="selectCompleted" v-model="status">
                                            <option value="R" selected>Ready</option>
                                            <option value="D">Delivered</option>
                                            <option value="C">Canceled</option>
                                        </select>
                                    </div>-->
                                </div>
                            </div>

                            <div class="col-xl-4 d-flex justify-content-end align-items-end" v-if="!userStore.user || (userStore.user && (userStore.user.type == 'EM' || userStore.user.type == 'C'))">
                                <button type="button" class="btn btn-warning px-4 btn-add" @click="addOrder">
                                    <i class="bi bi-xs bi-plus-circle me-2"></i>Add Order
                                </button>
                            </div>

                            <order-table :orders="orders" @edit="editOrder" v-if="(userStore.user && userStore.user.type != 'EC')"></order-table>
                            <order-table :orders="anonymous_orders" @edit="editOrder" v-if="!userStore.user"></order-table>

                            <order-items-table :order_items="order_items" v-if="userStore.user && userStore.user.type == 'EC'"></order-items-table>

                            <div v-if="userStore.user && userStore.user.type != 'EC'" class="d-flex justify-content-end mt-3">
                                <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrders" :limit="5"></Bootstrap5Pagination>
                            </div>
                             <div v-if="userStore.user && userStore.user.type=='EC'" class="d-flex justify-content-end mt-3">
                                <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrderItems" :limit="5"></Bootstrap5Pagination>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
