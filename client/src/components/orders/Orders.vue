<script setup>
import { ref, onMounted, inject, watch, computed  } from 'vue'
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
var total_orders=0;

const props = defineProps({
    onlyCurrentOrders: {
        type: Boolean,
        default: false
    }
})

const loadOrders = (page = 1) => {
    orderStore.load_orders(page, status.value)
}

const total = computed(() => {
    pagination.value = orderStore.get_page()
   if(pagination.value.meta!=undefined){
        total_orders=pagination.value.meta.total
   }
       
    return total_orders
})

const orders = computed(() => {
   
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
                            <h4 class="p-title" v-if="!userStore.user || userStore.user.type != 'EC'">Orders (Total: {{total}} )</h4>
                            <h4 class="p-title" v-if="userStore.user && userStore.user.type == 'EC'">Order Items</h4>
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
                  <div class="col-xl-10">


            <div class="d-flex">
                <div class="col-3" v-if="userStore.user ">
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

                <div class="col-xl-2">
                <div class="ms-auto align-self-center">
                    <div class="mx-0 mt-2" v-if="!userStore.user || (userStore.user && (userStore.user.type == 'EM' || userStore.user.type == 'C'))">
                          <button type="button" class="btn btn-warning px-4 btn-add" @click="addOrder">
                              <i class="bi bi-xs bi-plus-circle"></i>&nbsp;Add Order
                          </button>
                    </div>
                </div>
                </div>







        <order-table :orders="orders" :showId="true" @edit="editOrder" v-if="userStore.user && userStore.user.type != 'EC'"></order-table>
        <order-items-table :order_items="order_items" v-if="userStore.user && userStore.user.type == 'EC'"></order-items-table>

        <div v-if="userStore.user" class="d-flex justify-content-end mt-3">
            <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrders" :limit="5"></Bootstrap5Pagination>
        </div>
      </div>
            </div>
              </div>
            </div>
    </div>
    </div>
</template>

<style scoped>

</style>
