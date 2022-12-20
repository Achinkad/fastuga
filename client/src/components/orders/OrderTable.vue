<script setup>
import { ref, watch, computed, inject, onMounted } from "vue"
import { useUserStore } from '../../stores/user.js'
import { useOrderStore } from '../../stores/order.js'

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
                <th v-if="showId">Order ID</th>
                <th v-if="showTicketNumber">Ticket Number</th>
                <th v-if="showCustomer && userStore.user.type != 'C'">Customer ID</th>
                <th v-if="userStore.user.type == 'C'">Points Gained</th>
                <th v-if="showPrice">Price</th>
                <th v-if="showStatus">Order Status</th>
                <th class="text-center" v-if="userStore.user && (userStore.user.type=='EM' ||  userStore.user.type=='C')" style="width:10%">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="orders.length==0">
                <td colspan="6" class="text-center" style="height:55px!important;">No data available.</td>
            </tr>
            <tr v-for="order in orders" :key="order.id">
                <td v-if="showId">#{{ order.id }}</td>
                <td v-if="showTicketNumber">{{ order.ticket_number }}</td>
                <td v-if="order.customer && userStore.user.type != 'C'">
                    <router-link :to="{ name: 'User', params: { id: order.customer.user_id } }" :title="`View profile of ${order.customer.user.name}`">
                        #{{ order.customer_id }}
                    </router-link>
                </td>
                <td v-if="!order.customer && userStore.user.type != 'C'"> -- </td>
                <td v-if="userStore.user.type == 'C'">{{ order.points_gained }}</td>
                <td v-if="showPrice">{{ order.total_price }}€</td>
                <td v-if="showStatus">
                    <span v-if="order.status == 'P'">
                        <span class="badge badge-info-lighten">Pending</span>
                    </span>
                    <span v-if="order.status == 'R'">
                        <span class="badge badge-warning-lighten">Ready</span>
                    </span>
                    <span v-if="order.status == 'D'">
                        <span class="badge badge-success-lighten">Delivered</span>
                    </span>
                    <span v-if="order.status == 'C'">
                        <span class="badge badge-danger-lighten">Cancelled</span>
                    </span>
                </td>
                <td class="text-center" v-if="userStore.user.type == 'EM' ||  userStore.user.type=='C'">
                    <div class="d-flex justify-content-center">
                        <div v-if="userStore && userStore.user.type == 'EM'">
                            <div v-if="order.status != 'C' && order.status != 'D'">
                                <button class="btn btn-xs btn-light" title="Delete Order" @click="deleteClick(order)" v-if="showDeleteButton">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <button class="btn btn-xs btn-light" title="View Order" @click="editClick(order)" v-if="showEditButton">
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
td {
    word-wrap: break-word;
}

table {
    table-layout: fixed;
}
</style>
