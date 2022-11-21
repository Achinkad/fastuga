<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import OrderTable from "./OrderTable.vue"

const axios = inject('axios')
const router = useRouter()

const loadOrders = () => {
  // Change later when authentication is implemented
  const userId = 1
  axios.get('users/' + userId + '/orders')
    .then((response) => {
      orders.value = response.data.data
    })
    .catch((error) => {
      console.log(error)
    })
}

const loadProjects = () => {
  axios.get('projects')
    .then((response) => {
      projects.value = response.data.data
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
  orderTittle: {
    type: String,
    default: 'Order',
  },
  onlyCurrentOrders: {
    type: Boolean,
    default: false
  }
})

const orders = ref([])
const filterByProjectId = ref(-1)
const filterByCompleted = ref(-1)

const filteredOrders = computed(() => {
  return orders.value.filter(t =>
    (props.onlyCurrentOrders && !t.completed)
    ||
    (!props.onlyCurrentOrders && (
      (filterByProjectId.value == -1
      ) &&
      (filterByCompleted.value == -1
        || filterByCompleted.value == 0 && !t.completed
        || filterByCompleted.value == 1 && t.completed
      ))))

})

const totalOrders = computed(() => {
  return orders.value.reduce((c, t) =>
    (props.onlyCurrentOrders && !t.completed)
      ||
      (!props.onlyCurrentOrders && (

        (filterByCompleted.value == -1
          || filterByCompleted.value == 0 && !t.completed
          || filterByCompleted.value == 1 && t.completed
        ))) ? c + 1 : c, 0)

})


onMounted(() => {
  loadOrders()
})
</script>

<template>

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
