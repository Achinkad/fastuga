<script setup>
import { ref, watch, computed } from "vue"
import { useUserStore } from '../../stores/user.js'
import { useOrderStore } from '../../stores/order.js'

const userStore = useUserStore()
const orderStore = useOrderStore()

const emit = defineEmits(["completeToggled", "edit"])

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
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
                <th >Order ID</th>
                <th >Ticket Number</th>
                <th v-if=" (userStore.user && userStore.user.type != 'C')">Customer ID</th>
                <th v-if="userStore.user && userStore.user.type == 'C'">Points Gained</th>
                <th v-if="userStore.user && userStore.user.type != 'ED'">Price</th>
                <th >Order Status</th>
                <th class="text-center" v-if="(userStore.user && (userStore.user.type == 'EM' ||  userStore.user.type=='C')) || !userStore.user" style="width:10%">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="orders.length==0">
                <td v-if="userStore.user" colspan="6" class="text-center" style="height:55px!important;"> No data available.</td>
                <td v-else colspan="5" class="text-center" style="height:55px!important;"> No data available.</td>
            </tr>
            <tr v-for="order in orders" :key="order.id">
                <td >#{{ order.id }}</td>
                <td>{{ order.ticket_number }}</td>
                <td v-if="order.customer && (userStore.user.type == 'EM' || userStore.user.type == 'ED')">
                    <div v-if = "userStore.user.type == 'EM'" >
                    <router-link :to="{ name: 'User', params: { id: order.customer.user_id } }" :title="`View profile of ${order.customer.user.name}`">
                        {{ order.customer_id }}
                    </router-link></div>
                    <div v-if = "userStore.user.type == 'ED'" >
                   
                        {{ order.customer_id }}
                   </div>
                </td>
                <td v-if="order.customer_id == null" > -- </td>
                <td v-if="userStore.user && userStore.user.type == 'C'">{{ order.points_gained }}</td>
                <td  v-if="userStore.user && userStore.user.type != 'ED'" >{{ order.total_price }}€</td>
                <td >
                    <span v-if="order.status == 'P'">
                        <span class="badge badge-info-lighten">Preparing</span>
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
                <td class="text-center" v-if="(userStore.user && (userStore.user.type == 'EM' ||  userStore.user.type=='C')) || !userStore.user">
                    <div class="d-flex justify-content-center">
                        <div v-if="userStore.user && userStore.user.type == 'EM'">
                            <div v-if="order.status != 'C' && order.status != 'D'">
                                <button class="btn btn-xs btn-light" title="Delete Order" @click="deleteClick(order)">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <button class="btn btn-xs btn-light" title="View Order" @click="editClick(order)" >
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
