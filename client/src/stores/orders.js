import { ref, inject, computed } from 'vue'
import { defineStore } from 'pinia'
import { useUserStore } from "./user.js"

export const useOrderStore = defineStore('orders', () => {
    const userStore = useUserStore()

    const socket = inject("socket")
    const axios = inject('axios')
    const toast = inject("toast")

    const orders = ref([])
    const pagination = ref([])

    let url = null

    async function load_orders(page, status) {
        // URL builder
        if (userStore.user.type == "EM") url = `orders?page=${page}`
        if (userStore.user.type == "ED" || userStore.user.type == "C") url = `users/${userStore.userId}orders?page=${page}`

        try {
            const response = await axios({
                method: 'GET',
                url: url,
                params: {
                    status: status
                }
            })
        
            orders.value = response.data.data
            pagination.value = response.data
           
            return orders.value
        } catch (error) {
            clear_orders()
            throw error
        }
    }

    const get_orders = (() => { return orders.value })

    const get_page = (() => { return pagination.value })

    const clear_orders = (() => { orders.value = [] })

    const total_orders = computed(() => { return orders.value.length })

    async function insert_order(order) {
        const response = await axios.post('/orders', order)
        orders.value.push(response.data.data)
        socket.emit('newOrder', response.data.data)
        return response.data.data
    }

    socket.on('newOrder', (order) => {
        orders.value.push(order)
        toast.success(`A new order has arrived. Check your order menu. (#${order.id})`)
    })

    const remove_order = ((order) => {
        let i = orders.value.findIndex((t) => t.id === order.id)
        if (i >= 0) orders.value.splice(i, 1)
    })

    async function delete_order(order) {
        const response = await axios.delete('orders/' + order.id)
        remove_order(response.data.data)
        socket.emit('deleteOrder', response.data.data)
        return response.data.data
    }

    socket.on('deleteOrder', (order) => {
        remove_order(order)
        toast.info(`The Order (#${order.id}) was deleted!`)
    })

    return { orders, load_orders, get_page, get_orders, total_orders, insert_order, delete_order }
})
