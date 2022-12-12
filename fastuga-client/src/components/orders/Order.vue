<script setup>
import { ref, watch, computed, inject } from 'vue'
import { useRouter, onBeforeRouteLeave } from 'vue-router'

import OrderDetail from "./OrderDetails.vue"

const serverBaseUrl = inject("serverBaseUrl")

const router = useRouter()
const axios = inject('axios')

const toast = inject('toast')

const props = defineProps({
    id: {
        type: Number,
        default: null
    }
})

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

const add = (order_value) => {
    axios({
        method: 'POST',
        url: serverBaseUrl + '/api/orders',
        data: order_value
    })
    .then((response) => {
        console.log("feito")
    })
    .catch((error) => {
        if (error.response.status == 422) {
            toast.error('order #' + props.id + ' was not updated due to validation errors!')
            errors.value = error.response.data.data
        } else {
            toast.error('order #' + props.id + ' was not updated due to unknown server error!')
        }
    })
}

const cancel = () => {
    originalValueStr = dataAsString()
    router.back()
}

const dataAsString = () => {
    return JSON.stringify(order.value)
}

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

const order = ref(newOrder())
const errors = ref(null)
const confirmationLeaveDialog = ref(null)

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
    <confirmation-dialog ref="confirmationLeaveDialog" confirmationBtn="Discard changes and leave"
    msg="Do you really want to leave? You have unsaved changes!" @confirmed="leaveConfirmed">
    </confirmation-dialog>
    <order-detail :order="order" :errors="errors" @cancel="cancel" @add="add"></order-detail>
</template>
