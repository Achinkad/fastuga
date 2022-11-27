<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import OrderTable from "./OrderTable.vue"

const axios = inject('axios')
const router = useRouter()

const loadOrders = () => {
  axios.get('orders')
    .then((response) => {
      orders.value = response.data.data
      console.log(orders.value)
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
  let idx = orders.value.findIndex((t) => t.id === deletedOrder.id)
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
const filterByCompleted = ref("D")

const filteredOrders = computed(() => {
  return orders.value.filter(t =>
    (props.onlyCurrentOrders && !t.completed)
    ||
    (!props.onlyCurrentOrders && (

      (filterByCompleted.value == "D"
        || filterByCompleted.value == "C" && !t.completed
        || filterByCompleted.value == "D" && t.completed
      ))))

})

const totalOrders = computed(() => {
  return orders.value.reduce((c, t) =>
    (props.onlyCurrentOrders && !t.completed)
      ||
      (!props.onlyCurrentOrders && (

        (filterByCompleted.value == "D"
          || filterByCompleted.value == "C" && !t.completed
          || filterByCompleted.value == "D" && t.completed
        ))) ? c + 1 : c, 0)

})


onMounted(() => {
  loadOrders()
})
</script>

<template>

  <div class="mx-2">
    <h3 class="mt-4">{{ ordersTittle }}</h3>
  </div>
  <div class="mx-2 total-filtro">
    <h5 class="mt-4">Total: {{ totalOrders }}</h5>
  </div>
  <hr>
  <div v-if="!onlyCurrentOrders" class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectCompleted" class="form-label">Filter by completed:</label>
      <select class="form-select" id="selectCompleted" v-model="filterByCompleted">
        <option value="-1">Any</option>
        <option value="0">Pending Orders</option>
        <option value="1">Completed Orders</option>
      </select>
      <div class="mx-0 mt-2">
        <button type="button" class="btn btn-warning px-4 btn-addtask" @click="addOrder"><i
            class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Order</button>
      </div>

    </div>


  </div>
  <order-table :orders="orders" :showId="true" :showOwner="false" @edit="editOrder" @deleted="deletedOrder">
  </order-table>
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
