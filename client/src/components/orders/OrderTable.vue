<script setup>
import { ref, watch, computed, inject } from "vue"
import { useUserStore } from '../../stores/user.js'
import { useOrderStore } from '../../stores/orders.js'

const userStore = useUserStore()
const orderStore = useOrderStore()

const axios = inject("axios")
const toast = inject("toast")
const serverBaseUrl = inject("serverBaseUrl")

const emit = defineEmits(["completeToggled", "edit"])

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
    }
})

const editingOrders = ref(props.orders)
const orderToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const editClick = (order) => { emit("edit", order) }

const orderToDeleteDescription = computed(() => {
    return orderToDelete.value
    ? `#${orderToDelete.value.id} (${orderToDelete.value.id})`
    : ""
})

const dialogConfirmedDelete = () => {
    orderStore.delete_order(orderToDelete.value)
    .then((response) => {
        toast.info("Order " + orderToDeleteDescription.value + " was deleted!")
    })
    .catch((error) => {
      console.log(error)
    })
}

const deleteClick = (order) => {
    orderToDelete.value = order
    deleteConfirmationDialog.value.show()
}

watch(
    () => props.orders,
    (newOrders) => {
        editingOrders.value = newOrders
    }
)
</script>

<template>
    <confirmation-dialog ref="deleteConfirmationDialog" confirmationBtn="Confirm Cancel order"
    :msg="`Do you really want to cancel the order ${orderToDeleteDescription}?`" @confirmed="dialogConfirmedDelete">
</confirmation-dialog>
<div class="table-responsive">
    <table class="table align-middle mt-4">
        <thead class="table-light">
            <tr>
                <th v-if="showTicketNumber">Ticket Number</th>
                <th v-if="showPrice">Total Price</th>
                <th v-if="showStatus">Status</th>
                <th class="text-center" v-if="userStore.user && (userStore.user.type=='EM' ||  userStore.user.type=='C')" style="width:10%">Options</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="order in orders" :key="order.id">
                <td v-if="showTicketNumber">{{ order.ticket_number }}</td>
                <td v-if="showPrice">{{ order.total_price }}€</td>
                <td v-if="showStatus">
                    <span v-if="order.status == 'P'">Preparing</span>
                    <span v-if="order.status == 'R'">Ready</span>
                    <span v-if="order.status == 'D'">Delivered</span>
                    <span v-if="order.status == 'C'">Cancelled</span>
                </td>
                <td class="text-center" v-if="userStore.user.type == 'EM' ||  userStore.user.type=='C'">
                    <div class="d-flex justify-content-center">
                        <div  v-if="userStore && userStore.user.type == 'EM'">
                            <div v-if="order.status != 'C' && order.status != 'D'">
                                <button class="btn btn-xs btn-light" @click="deleteClick(order)" v-if="showDeleteButton">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <button class="btn btn-xs btn-light" @click="editClick(order)" v-if="showEditButton">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</template>

<style scoped>
.completed {
    text-decoration: line-through;
}

button {
    margin-left: 3px;
    margin-right: 3px;
}

td {
    word-wrap: break-word;
}

table {
    table-layout: fixed;
}

</style>
