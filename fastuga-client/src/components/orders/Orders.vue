<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import OrderTable from "./OrderTable.vue"
import { Bootstrap5Pagination } from 'laravel-vue-pagination';

const axios = inject('axios')
const router = useRouter()
const serverBaseUrl = inject("serverBaseUrl")

const pagination = ref({})

const loadOrders = (page = 1) => {
  axios.get(serverBaseUrl+'/api/orders')
    .then((response) => {
      orders.value = response.data.data
      pagination.value = response.data
    })
    .catch((error) => {
      console.log(error)
    })
}
/* Prefiro esta versao da funcao, no entanto tem que se discutir isto.
const loadOrders = () => {
    if(userStore.userType == 'EM'){
      axios.get('/api/orders')
      .then((response) => {
        orders.value = response.data.data
      })
      .catch((error) => {
        console.log(error)
      })
    }else{
    axios.get('/api/orders/user/' + userStore.userId)
      .then((response) => {
        orders.value = response.data.data
      })
      .catch((error) => {
        console.log(error)
      })
    }
  }
    */



const addOrder = () => {
  router.push({ name: 'NewOrder' })
}

const editOrder = (order) => {
  router.push({ name: 'Order', params: { id: order.id } })
}

const deletedOrder = (deletedOrder) => {
  let idx = orders.value.findIndex((o) => o.id === deletedOrder.id)
  if (idx >= 0) {
    orders.value.splice(idx, 1)
  }
}

const props = defineProps({
  ordersTittle: {
    type: String,
    default: 'Order',
  },
  onlyCurrentOrders: {
    type: Boolean,
    default: false
  }
})

const orders = ref([])
const filterByStatus = ref("-1")

const filteredOrders = computed(() => {
  return orders.value.filter((o) =>
    (props.onlyCurrentOrders && o.status=='P')
    ||
    (props.onlyCurrentOrders && o.status=='R')
    ||
    (!props.onlyCurrentOrders && (

      (filterByStatus.value == "-1"
        || filterByStatus.value == "P" && o.status =='P'
        || filterByStatus.value == "D" && o.status =='D'
        || filterByStatus.value == "R" && o.status =='R'
        || filterByStatus.value == "C" && o.status =='C'

      ))))
})


const totalOrders = computed(() => {
    return orders.value.reduce((c,o) =>
    (props.onlyCurrentOrders && o.status=='P')
    ||
    (props.onlyCurrentOrders && o.status=='R')
    ||
    (!props.onlyCurrentOrders && (

      (filterByStatus.value == "-1"
        || filterByStatus.value == "P" && o.status =='P'
        || filterByStatus.value == "D" && o.status =='D'
        || filterByStatus.value == "R" && o.status =='R'
        || filterByStatus.value == "C" && o.status =='C'

      ))) ? c + 1 : c, 0)
})


onMounted(() => {
  loadOrders()
})
</script>

<template>

  <div class="mx-2">
    <h3 class="mt-4">{{ ordersTitle }}</h3>
  </div>
  <div class="mx-2 total-filtro">
    <h5 class="mt-4">Total: {{ totalOrders }}</h5>
  </div>
  <hr>
  <div v-if="!onlyCurrentOrders" class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectCompleted" class="form-label">Filter by status:</label>
      <select class="form-select" id="selectCompleted" v-model="filterByStatus">
        <option value="-1">Any</option>
        <option value="P">Preparing Orders</option>
        <option value="R">Ready Orders</option>
        <option value="D">Delivered Orders</option>
        <option value="C">Canceled Orders</option>


      </select>
      <div class="mx-0 mt-2">
        <button type="button" class="btn btn-warning px-4 btn-addtask" @click="addOrder"><i
            class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Order</button>
      </div>

    </div>


  </div>


  <order-table
    :orders="filteredOrders"
    :showId="true"
    :showOwner="false"
    @edit="editOrder"
    @deleted="deletedOrder"
  ></order-table>

  <hr>
  <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrders" :limit="5"></Bootstrap5Pagination>
  <hr>
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
