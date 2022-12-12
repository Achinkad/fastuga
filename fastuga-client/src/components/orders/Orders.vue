<script setup>
import { ref, onMounted, inject, watch } from 'vue'
import { useRouter } from 'vue-router'
import OrderTable from "./OrderTable.vue"
import OrderItemsTable from "./OrderItemsTable.vue"
import { useUserStore } from '../../stores/user.js'
import { Bootstrap5Pagination } from 'laravel-vue-pagination'


const userStore = useUserStore()
const axios = inject('axios')
const router = useRouter()
const serverBaseUrl = inject("serverBaseUrl");
const pagination = ref({})

//variável usada no filtro
var value_status = ref("all");

//WATCH PARA ESTAR SEMPRE A VER O VALOR DE VALUE_STATUS(valor do filtro)
watch(value_status, () => {
  console.log(value_status.value)
  loadOrders()
})

//funcao a implementar com filtros para historicos de negocio

const loadOrders = (page = 1) => {

    if(userStore.user && userStore.user.type == 'EM'){

      axios.get(serverBaseUrl + '/api/orders?page=' + page, {
        params: {
          status: value_status.value
        }
      })
      .then((response) => {

        orders.value = response.data.data
        pagination.value = response.data
      })
      .catch((error) => {
        console.log(error)
      })
    }

    if(userStore.user && (userStore.user.type == 'ED' || userStore.user.type == 'C')){

      axios.get(serverBaseUrl+'/api/users/'+ userStore.userId +'/orders?page='+page)
      .then((response) => {

        orders.value = response.data.data
        pagination.value = response.data
      })
      .catch((error) => {
        console.log(error)
      })
    }
    if(userStore.user && userStore.user.type == 'EC'){
      console.log("aaaa")
      axios.get(serverBaseUrl+'/api/users/'+ userStore.userId +'/order-items?page='+page)
      .then((response) => {
        order_items.value = response.data.data
        pagination.value = response.data

      })
      .catch((error) => {
        console.log(error)
      })
    }


    }

const addOrder = () => {
  router.push({ name: "NewOrder" });
};

const editOrder = (order) => {
  router.push({ name: "Order", params: { id: order.id } });
};

const deletedOrder = (deletedOrder) => {
  let idx = orders.value.findIndex((o) => o.id === deletedOrder.id)
  if (idx >= 0) {
    orders.value.splice(idx, 1);
  }
};

const props = defineProps({
  onlyCurrentOrders: {
    type: Boolean,
    default: false,
  },
});

const forceRerender = () => {
    loadOrders()
    console.log("reload ORDERS")
}

const orders = ref([])
const order_items = ref([])

onMounted(() => {
  if(userStore.user){
    loadOrders()
  }


})
</script>

<template>

  <div class="mx-2">
    <h3 class="mt-4" v-if="!userStore.user || userStore.user.type != 'EC'">Orders</h3>
    <h3 class="mt-4" v-if="userStore.user && userStore.user.type == 'EC'">Order Items</h3>
  </div>

  <hr>
  <div v-if="!onlyCurrentOrders && userStore.user &&userStore.user.type == 'EM'" class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectCompleted" class="form-label">Filter by status:</label>
      <select class="form-select" id="selectCompleted" v-model="value_status">
        <option value="all" selected>Any</option>
        <option value="P">Preparing Orders</option>
        <option value="R">Ready Orders</option>
        <option value="D">Delivered Orders</option>
        <option value="C">Canceled Orders</option>
      </select>
    </div>
  </div>
  <div class="mx-0 mt-2" v-if="!userStore.user || userStore.user.type == 'EM' || userStore.user.type == 'C'">
        <button type="button" class="btn btn-warning px-4 btn-addtask" @click="addOrder"><i
            class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Order</button>
  </div>

  <order-table :orders="orders" :showId="true" @edit="editOrder" @deleted="deletedOrder" @forceRerender="forceRerender" v-if="userStore.user && userStore.user.type != 'EC'"></order-table>
  <order-items-table :order_items="order_items" @forceRerender="forceRerender" v-if="userStore.user && userStore.user.type == 'EC'"></order-items-table>

  <div v-if="userStore.user && !onlyCurrentOrders">

    <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrders" :limit="5"></Bootstrap5Pagination>
    <hr>
  </div>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}

.total-filtro {
  margin-top: 0.35rem;
}

.btn-addOrder {
  margin-top: 1.85rem;
}
</style>
