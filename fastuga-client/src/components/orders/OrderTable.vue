<script setup>
import { ref, watch, watchEffect, computed, inject } from "vue"

const axios = inject("axios")
const toast = inject("toast")

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
  showOwner: {
    type: Boolean,
    default: true,
  },
  showProject: {
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
    .patch("orders/" + order.id + "/completed", { completed: !order.completed })
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
    .delete("orders/" + orderToDelete.value.id)
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
