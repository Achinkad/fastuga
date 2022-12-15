<script setup>
import { ref, onMounted, inject, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user.js'
import { Bootstrap5Pagination } from 'laravel-vue-pagination'

import OrderTable from "./OrderTable.vue"
import OrderItemsTable from "./OrderItemsTable.vue"

const axios = inject('axios')
const serverBaseUrl = inject("serverBaseUrl")

const userStore = useUserStore()
const router = useRouter()

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
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex">
                    <div class="p-title-box">
                        <div class="p-title-right">

                          <!-- Total 83 ???? -->
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
                    <select class="form-select" id="selectCompleted" v-model="value_status">
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
          
       

        <order-table :orders="orders" :showId="true" @edit="editOrder" @deleted="deletedOrder" @forceRerender="forceRerender" v-if="userStore.user && userStore.user.type != 'EC'"></order-table>
        <order-items-table :order_items="order_items" @forceRerender="forceRerender" v-if="userStore.user && userStore.user.type == 'EC'"></order-items-table>

        <div v-if="userStore.user && !onlyCurrentOrders" class="d-flex justify-content-end mt-3">
            <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrders" :limit="5"></Bootstrap5Pagination>
        </div>
    </div>
</template>

<style scoped>
.btn-addOrder {
  margin-top: 1.85rem;
}

button[type="button"] {
    background-color: rgb(255,165,0) !important;
    color: #fff;
    border-color: rgb(255,165,0);
    border-radius: 0.15rem;
    box-shadow: 0px 2px 6px 0px rgb(255,165,0);
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
