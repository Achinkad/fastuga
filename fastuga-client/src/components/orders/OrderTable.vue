<script setup>
import { ref, watch, computed, inject } from "vue"
import { useUserStore } from '../../stores/user.js'

const userStore = useUserStore()
const axios = inject("axios")
const toast = inject("toast")

const serverBaseUrl = inject("serverBaseUrl")

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

const emit = defineEmits(["completeToggled", "edit", "deleted", "forceRerender"])

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

const editClick = (order) => {
  emit("edit", order)
}

const dialogConfirmedDelete = () => {
  console.log(orderToDelete.value)
  axios
    .patch(serverBaseUrl + "/api/orders/" + orderToDelete.value.id + "/status", { status: 'C' })
    .then((response) => {
      emit("forceRerender");
      toast.info("Order " + orderToDeleteDescription.value + " was deleted")
    })
    .catch((error) => {
      console.log(error);
    });

}


const deleteClick = (order) => {
  orderToDelete.value = order
  deleteConfirmationDialog.value.show()
}
</script>

<template>
  <confirmation-dialog ref="deleteConfirmationDialog" confirmationBtn="Delete order"
    :msg="`Do you really want to delete the order ${orderToDeleteDescription}?`" @confirmed="dialogConfirmedDelete">
  </confirmation-dialog>
  <table class="table">
    <thead>
      <tr>
        <th v-if="showId && userStore.user && userStore.user.type == 'EM'">ID</th>
        <th v-if="showStatus">Status</th>
        <th v-if="showPrice">Total Paid/Total Price</th>
        <th v-if="showTicketNumber">Ticket Number</th>
        <th v-if="showCompletedButton || showEditButton || showDeleteButton"></th>

      </tr>
    </thead>
    <tbody>
      <tr v-for="order in editingOrders" :key="order.id">
        <td v-if="showId && userStore.user && userStore.user.type == 'EM'">{{ order.id }}</td>
        <td v-if="showStatus">
          <span v-if="order.status == 'P'">Preparing</span>
          <span v-if="order.status == 'R'">Ready</span>
          <span v-if="order.status == 'D'">Delivered</span>
          <span v-if="order.status == 'C'">Cancelled</span>
        </td>
        <td v-if="showPrice">{{ order.total_paid }}€/{{ order.total_price }}€</td>
        <td v-if="showTicketNumber">{{ order.ticket_number }}</td>
        <td class="text-end" v-if="userStore.user.type == 'EM'">
          <div class="d-flex justify-content-end">
            <div v-if="order.status != 'C'">
              <button class="btn btn-xs btn-light" @click="deleteClick(order)" v-if="showDeleteButton">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
            <button class="btn btn-xs btn-light" @click="editClick(order)" v-if="showEditButton">
              <i class="bi bi-pencil"></i>
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
