<script setup>
import { ref, watch, inject } from 'vue'
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { useUserStore } from '../../stores/user.js';
import { useOrderStore } from '../../stores/orders.js';
import OrderDetail from "./OrderDetails.vue"

const serverBaseUrl = inject("serverBaseUrl")

const router = useRouter()
const userStore = useUserStore()
const orderStore = useOrderStore()

const axios = inject('axios')
const toast = inject('toast')

const props = defineProps({
    id: {
        type: Number,
        default: null
    },
    user_id: {
        type: Number,
        default: null
    }
})

const currentCustomer= ref({})

const newOrder = () => {
    return {
        id: null,
        ticket_number: null,
        status: null,
        completed: false,
        total_price: null,
        total_paid: null,
        total_paid_with_points: null,
        points_gained: null,
        points_used_to_pay: null,
        payment_reference: null,
        date: null,
        custom: null,
        customer_id: null,
        customer: [],
        delivered_by: null,
        user: [],
        order_item: [],
    }
}

let originalValueStr = ''
const loadOrder = (id) => {
    originalValueStr = ''
    errors.value = null
    if (!id || (id < 0)) {
        order.value = newOrder()
        originalValueStr = dataAsString()
    } else {
        axios.get(serverBaseUrl + '/api/orders/' + id)
        .then((response) => {
            order.value = response.data.data
            originalValueStr = dataAsString()
        })
        .catch((error) => {
            console.log(error)
        })
    }
}

const add = (order) => {
    orderStore.insert_order(order)
    .then((response) => {
        toast.success("Order added successfuly!")
        router.push({ name: 'Orders' })
    })
    .catch((error) => {
        
        if (error.response.status == 422) {
            toast.error('Couldn\'t add the order due to validation errors!')
            errors.value = error.response.data.data
        } else {
            toast.error('Couldn\'t add the order due to unknown server error!')
        }
    })
}

const cancel = () => {
    originalValueStr = dataAsString()
    router.push({ name: 'Orders' })
}


const getCurrentCustomer = () => {

    axios.get(serverBaseUrl + '/api/customers/user/' + userStore.user.id)
        .then((response) => {

            if (response.data) {
                currentCustomer.value = response.data.data

                order.value.payment_reference=currentCustomer.value.default_payment_reference;
                order.value.payment_type=currentCustomer.value.default_payment_type;

                order.value.customer_id = currentCustomer.value.id;


            }

        })
        .catch((error) => {
            console.log(error);
        });
}

const dataAsString = () => {
    return JSON.stringify(order.value)
}
/*
let nextCallBack = null
const leaveConfirmed = () => {
    if (nextCallBack) {
        nextCallBack()
    }
}

onBeforeRouteLeave((to, from, next) => {
    nextCallBack = null

    let newValueStr = dataAsString()
    if (originalValueStr != newValueStr) {
        nextCallBack = next
        confirmationLeaveDialog.value.show()
    } else {
        next()
    }
})
*/
const order = ref(newOrder())
const errors = ref(null)
//const confirmationLeaveDialog = ref(null)

// beforeRouteUpdate was not fired correctly
// Used this watcher instead to update the ID
watch(
    () => props.id,
    (newValue) => {
        loadOrder(newValue)
    },
    { immediate: true }
)

</script>


<template>

    <!--<confirmation-dialog ref="confirmationLeaveDialog" confirmationBtn="Discard changes and leave"
    msg="Do you really want to leave? You have unsaved changes!" @confirmed="leaveConfirmed">

    </confirmation-dialog>-->

    <order-detail :currentCustomer="currentCustomer" :order="order" :errors="errors" @cancel="cancel" @add="add" @getCurrentCustomer="getCurrentCustomer"></order-detail>
</template>
