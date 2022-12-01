<script setup>
import { ref, watch, watchEffect, computed, inject } from "vue"

const axios = inject("axios")
const toast = inject("toast")

const serverBaseUrl ="http://fastuga.test";

const props = defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
  showId: {
    type: Boolean,
    default: true,
  },
  showStatus: {
    type: Boolean,
    default: true,
  },
  showStatus: {
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
   showTicketNumber: {
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

const editingOrders = ref(props.orders)
const orderToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const orderToDeleteDescription = computed(() => {
  return orderToDelete.value
    ? `#${orderToDelete.value.id} (${orderToDelete.value.id})`
    : ""
})

watch(
  () => props.orders,
  (newOrders) => {
    editingOrders.value = newOrders
  }
)

/*
const toogleClick = (order) => {
  axios
    .patch(serverBaseUrl + "(api/orders/" + order.id + "/completed", { completed: !order.completed })
    .then((response) => {
      order.completed = response.data.data.completed
      emit("completeToggled", order)
    })
    .catch((error) => {
      console.log(error)
    })
}
*/
const editClick = (order) => {
  emit("edit", order)
}

const dialogConfirmedDelete = () => {
  axios
    .delete(serverBaseUrl + "/api/orders/" + orderToDelete.value.id)
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
    <confirmation-dialog
    ref="deleteConfirmationDialog"
    confirmationBtn="Delete order"
    :msg="`Do you really want to delete the order ${orderToDeleteDescription}?`"
    @confirmed="dialogConfirmedDelete"
  >
  </confirmation-dialog>
  <table class="table">
    <thead>
      <tr>
        <th v-if="showId">ID</th>
        <th v-if="showStatus">Status</th>
        <th v-if="showPrice">Total Paid/Total Price</th>
        <th v-if="showTicketNumber">Ticket Number</th>
        <th v-if="showCompletedButton || showEditButton || showDeleteButton"></th>

      </tr>
    </thead>
    <tbody>
      <tr v-for="order in editingOrders" :key="order.id">
        <td v-if="showId">{{ order.id }}</td>
        <td v-if="showStatus">
         <span v-if="order.status=='P'">Preparing</span>
         <span v-if="order.status=='R'">Ready</span>
         <span v-if="order.status=='D'">Delivered</span>
         <span v-if="order.status=='C'">Cancelled</span>
        </td>
        <td v-if="showPrice">{{ order.total_paid }}€/{{order.total_price}}€</td>
        <td v-if="showTicketNumber">{{ order.ticket_number}}</td>

        <td class="text-end" v-if="showCompletedButton || showEditButton || showDeleteButton">
          <div class="d-flex justify-content-end">

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
