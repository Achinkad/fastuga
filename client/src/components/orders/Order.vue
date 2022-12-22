<script setup>
import { ref, watch, inject, computed, onMounted, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user.js';
import { useOrderStore } from '../../stores/order.js';
import OrderDetail from "./OrderDetails.vue"

const router = useRouter()
const userStore = useUserStore()
const orderStore = useOrderStore()
const axios = inject('axios')
const toast = inject('toast')
const serverBaseUrl = inject("serverBaseUrl")

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


const loadOrder = (id) => {
    console.log("aaaaa")
    if (!id || (id < 0)) {
        order.value = newOrder()
        console.log("bbbb")
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
            console.log(error);
            if (error.response.status == 422) {
                toast.error('Couldn\'t add the order due to validation errors!')
                errors.value = error.response.data.data
            } else {
                toast.error('Couldn\'t add the order due to unknown server error!')
            }
        })
}

const cancel = () => {
    router.push({ name: 'Orders' })
}

const dataAsString = () => {
    return JSON.stringify(order.value)
}

const order = ref(newOrder())
const errors = ref(null)

const loadCustomer = () => {
    userStore.loadCustomer(userStore.user)
}

const customer = computed(() => { return userStore.get_customer() })

watch(
    () => props.id,
    (newValue) => {

        loadOrder(newValue)
    },
    { immediate: true }
)

onBeforeMount(() => {
    if (userStore.user && userStore.user.type == 'C') {
        loadCustomer()
    }
})

onBeforeMount(async () => {
    await axios.get(serverBaseUrl + '/api/orders/' + props.id)
    .catch((error) => {
        if (error.response.status == 404) {
            router.push({ name: 'NotFound' })
        }
    })
})
</script>

<template>
    <order-detail :order="order" :errors="errors" @cancel="cancel" @add="add" :customer="customer" ></order-detail>
</template>