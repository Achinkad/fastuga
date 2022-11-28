<script setup>
import { ref, watch, watchEffect, computed, inject } from "vue"

const axios = inject("axios")
const toast = inject("toast")
const serverBaseUrl = ine

const props = defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
  showId: {
    type: Boolean,
    default: true,
  },
  showCompleted: {
    type: Boolean,
    default: true,
  },
  showCustomer: {
    type: Boolean,
    default: true,
  },
  showPrice: {
    type: Boolean,
    default: true,
  },
  showCompletedButton: {
    type: Boolean,
    default: true,
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
  
})

const emit = defineEmits(["completeToggled", "edit", "deleted"])

const editingOrders = ref(props.ordersDay)
const orderToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const orderToDeleteDescription = computed(() => {
  return orderToDelete.value
    ? `#${orderToDelete.value.id} (${orderToDelete.value.description})`
    : ""
})

watch(
  () => props.orders,
  (newOrders) => {
    editingOrders.value = newOrders
  }
)


const toogleClick = (order) => {
  axios
    .patch(serverBaseUrl+"(api/orders/" + order.id + "/completed", { completed: !order.completed })
    .then((response) => {
      order.completed = response.data.data.completed
      emit("completeToggled", order)
    })
    .catch((error) => {
      console.log(error)
    })
}

const editClick = (order) => {
  emit("edit", order)
}

const dialogConfirmedDelete = () => {
  axios
    .delete(serverBaseUrl+"/api/orders/" + orderToDelete.value.id)
    .then((response) => {
      emit("deleted", response.data.data)
      toast.info("Order " + orderToDeleteDescription.value + " was deleted")
    })
    .catch((error) => {
      console.log(error)
    })
}

const deleteClick = (order) => {
  orderToDelete.value = order
  deleteConfirmationDialog.value.show()
}
</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th v-if="showId">ID</th>
        <th class="text-center" v-if="showCompleted">Status</th>
      
      </tr>
    </thead>
    <tbody>
      <tr v-for="order in orders" :key="order.id">
        <td v-if="showId">{{ order.id }}</td>
        <td class="text-center" v-if="showCompleted">
          {{ order.completed ? "Done" : "Pending" }}
        </td>
        <td v-if="showId">{{ order.id }}</td>

        <td class="text-end" v-if="showCompletedButton || showEditButton || showDeleteButton">
          <div class="d-flex justify-content-end">
            <button class="btn btn-xs btn-light" @click="toogleClick(task)" v-if="showCompletedButton">
              <i class="bi bi-xs" :class="{
                'bi-square': !order.completed,
                'bi-check2-square': order.completed,
              }"></i>
            </button>

            <button class="btn btn-xs btn-light" @click="editClick(order)" v-if="showEditButton">
              <i class="bi bi-xs bi-pencil"></i>
            </button>
            <button class="btn btn-xs btn-light" @click="deleteClick(order)" v-if="showDeleteButton">
              <i class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>




</template>

<style scoped>
.completed {
  text-decoration: line-through;
}

button {
  margin-left: 3px;
  margin-right: 3px;
}
</style>
