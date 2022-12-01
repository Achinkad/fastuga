<script setup>
import { ref, computed, onMounted, inject,watch } from 'vue'
import { useRouter } from 'vue-router'
import OrderTable from "./OrderTable.vue"

import { Bootstrap5Pagination } from 'laravel-vue-pagination'

const axios = inject('axios')
const router = useRouter()
const serverBaseUrl = inject("serverBaseUrl")

const pagination = ref({})

//variável usada no filtro
var value_status=ref("-1");

// funcao provisoria enquanto as rotas nao estao definidas
const loadOrders = (page = 1) => {

  axios.get(serverBaseUrl+'/orders?page='+page,{
    params:{
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

//WATCH PARA ESTAR SEMPRE A VER O VALOR DE VALUE_STATUS(valor do filtro)
watch(value_status,() =>{
  console.log(value_status.value)
  loadOrders()
}) 


//funcao a implementar com filtros para historicos de negocio
/*
const loadOrders = (page = 1) => {
    if(userStore.userType == 'EM'){
      axios.get('/api/orders?page='+page)
      .then((response) => {
        orders.value = response.data.data
        pagination.value = response.data
      })
      .catch((error) => {
        console.log(error)
      })
    if(userStore.userType == 'ED'){
      axios.get('/api/delivery/'+ userStore.userId+'/orders?page='+page)
      .then((response) => {
        orders.value = response.data.data
        pagination.value = response.data
      })
      .catch((error) => {
        console.log(error)
      })
    }
      /*
    axios.get('/api/users/'+ userStore.userId +'/orders?page='+page)
      .then((response) => {
        orders.value = response.data.data
      })
      .catch((error) => {
        console.log(error)
      })
      //

    }
}
*/
//funcao com historico de orders por utilizador nao funcional ainda por falta de rota e funcao na API
const loadHistoricOrders = (page = 1) => {
    axios.get('/users/'+ userStore.userId +'/orders?page='+page)
      .then((response) => {
        orders.value = response.data.data
      })
      .catch((error) => {
        console.log(error)
      })
}

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
  ordersTitle: {
    type: String,
    default: 'Order',
  },
  onlyCurrentOrders: {
    type: Boolean,
    default: false
  }
})

const orders = ref([])

//TALVEZ TIRAR,OU MELHORAR, NÃO FAZ SENTIDO O TOTAL APARECER 30
/*
const totalOrders = computed(() => {
    return orders.value.reduce((c,o) =>
    (props.onlyCurrentOrders && o.status=='P')
    ||
    (props.onlyCurrentOrders && o.status=='R')
    ||
    (!props.onlyCurrentOrders && (

      (value_status.value == "-1"
        || value_status.value == "P" && o.status =='P'
        || value_status.value == "D" && o.status =='D'
        || value_status.value == "R" && o.status =='R'
        || value_status.value == "C" && o.status =='C'

      ))) ? c + 1 : c, 0)
})
*/



onMounted(() => {
  loadOrders()
  //loadHistoricOrders()
  
})
</script>

<template>

  <div class="mx-2">
    <h3 class="mt-4">{{ ordersTitle }}</h3>
  </div>
  <!--
  <div class="mx-2 total-filtro">
    <h5 class="mt-4">Total: {{ totalOrders }}</h5>
  </div>
  -->
  <hr>
  <div v-if="!onlyCurrentOrders" class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectCompleted" class="form-label">Filter by status:</label>
      <select class="form-select" id="selectCompleted" v-model="value_status">
        <option value="-1" selected>Any</option>
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
  <div v-else class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectCompleted" class="form-label">Filter by status:</label>
      <select class="form-select" id="selectCompleted" v-model="value_status">
        <option value="-1" selected>Any</option>
        <option value="P">Preparing Orders</option>
        <option value="R">Ready Orders</option>
        <option value="D">Delivered Orders</option>
        <option value="C">Canceled Orders</option>
      </select>
    </div>

  </div>
  <!--
    <div v-else class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectCompleted" class="form-label">Filter by status:</label>
      <select class="form-select" id="selectCompleted" v-model="filterByStatus">
        <option value="-1">Any</option>
        <option value="P">Preparing Orders</option>
        <option value="R">Ready Orders</option>
      </select>
      <div class="mx-0 mt-2">
        <button type="button" class="btn btn-warning px-4 btn-addtask" @click="addOrder"><i
            class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Order</button>
      </div>
    </div>
    </div>
    
-->



  <order-table
    :orders="orders"
    :showId="true"
    @edit="editOrder"
    @deleted="deletedOrder"
  ></order-table>


<!--A prop OnlyCurrentOrders esta a devolver o historico de orders do utilizador logado(myOrders)-->

  <div v-if="!onlyCurrentOrders">
    
    <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrders" :limit="5"></Bootstrap5Pagination>
    <hr>
  </div>
  <div v-else>
  
  <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadHistoricOrders" :limit="5"></Bootstrap5Pagination>
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
