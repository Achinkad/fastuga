import { ref, inject, computed } from 'vue'
import { defineStore } from 'pinia'
import { useUserStore } from "./user.js"

export const useOrderStore = defineStore('orders', () => {
    const userStore = useUserStore()

    const socket = inject("socket")
    const axios = inject('axios')
    const toast = inject("toast")

    const orders = ref([])
    const number_orders = ref([])
    const number_orders_this_month = ref([])
    const revenue_orders = ref([])
    const pagination = ref([])
    const pagination_preparation = ref([])

    const order_items = ref([])
   

    const order_items_preparing = ref([])

    let url = null


    async function load_orders(page, status) {
        // URL builder
        if (userStore.user && userStore.user.type == "EM") url = `orders?page=${page}`
        if (userStore.user && userStore.user.type == "ED" || userStore.user.type == "C") url = `users/${userStore.userId}/orders?page=${page}`
       
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

    async function loadNumberOrdersMonth() {
        try {
            const response = await axios({
                method: 'GET',
                url: 'orders/numbers'
            })
            number_orders.value = response.data
            return number_orders.value
        } catch (error) {

            throw error
        }
    }

    const count_orders = ref(null)

    async function count_orders_by_status(status) {
        try {
            const response = await axios({
                method: 'GET',
                url: 'orders/status',
                params: { status }
            })
            count_orders.value = response.data
            console.log(response.data.data)
            return number_orders.value
        } catch (error) {

            throw error
        }
    }
    async function loadNumberOrdersThisMonth() {

        try {
            const response = await axios({
                method: 'GET',
                url: 'orders/this_month'
            })

            number_orders_this_month.value = response.data
            return number_orders_this_month.value
        } catch (error) {

            throw error
        }
    }

    async function loadRevenueOrders() {

        try {
            const response = await axios({
                method: 'GET',
                url: 'orders/revenue'
            })

            revenue_orders.value = response.data
            return revenue_orders.value
        } catch (error) {

            throw error
        }
    }

    async function loadOrderItems(page) {

        try {
            const response = await axios({
                method: 'GET',
                url: 'users/'+userStore.user.id+'/order-items?page='+page
            })

            order_items.value = response.data.data
            pagination.value = response.data
            return order_items.value
        } catch (error) {

            throw error
        }
    }

    async function loadOrderItemsWaiting(page) {

        try {
            const response = await axios({
                method: 'GET',
                url: 'order-items/waiting?page='+page
            })

            order_items.value = response.data.data
            pagination.value = response.data
           
            return order_items.value
        } catch (error) {

            throw error
        }
    }

    async function loadOrderItemsPreparing(page) {

        try {
            const response = await axios({
                method: 'GET',
                url: 'users/'+userStore.user.id+'/order-items/preparing?page='+page
            })

            order_items_preparing.value = response.data.data
            pagination_preparation.value = response.data
           
            return order_items_preparing.value
        } catch (error) {

            throw error
        }
    }

    const get_order_items_preparing = (() => { return order_items_preparing.value })
    const get_orders = (() => { return orders.value })
    const get_order_items = (() => { return order_items.value })
    const get_order_items_waiting = (() => { return order_items.value})
    const get_orders_this_month = (() => { return number_orders_this_month.value })
    const get_revenue_orders = (() => { return revenue_orders.value })

    const get_page = (() => { return pagination.value })
    const get_page_preparation = (() => { return pagination_preparation.value })

    const clear_orders = (() => { orders.value = [] })

    const total_orders = computed(() => { return orders.value.length })

    const my_orders = computed(() => { return orders.value.filter(or => or.customer.user.id == userStore.userId) })
    const my_orders_delivery = computed(() => { return orders.value.filter(or =>  userStore.userId) })
    const total_my_orders = computed(() => { return my_orders.value.length })

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

    const remove_order_item = ((order_item,order_items) => {
        
        let i = order_items.value.findIndex((t) => t.id === order_item.id)
        if (i >= 0) order_items.value.splice(i, 1)
      
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

    let data = {}

    async function update_order_status(order,status) {
        if(userStore.user && userStore.user.type == "ED"){
            data = {
                'status': status,
                'delivered_by': userStore.user.id
            }

        }

        const response = await axios({
            method: 'PATCH',
            url: 'orders/' + order.id + '/status',
            params: data
        })
        
        remove_order(response.data.data)
        socket.emit('updateOrder', response.data.data)
        return response.data.data
    }

    async function update_order_items_status(order_item,status) {
               
            if(userStore.user && userStore.user.type == "EC"){
                data = {
                    'status': status,
                    'prepared_by': userStore.user.id
                }
    
            }
            const response = await axios({
                method: 'PATCH',
                url: 'order-items/'+order_item.id+'/status',
                params: data
            })

            if(status=='P'){
                remove_order_item(response.data.data,order_items)
                loadOrderItemsPreparing()
            }
            if(status=='R'){
                remove_order_item(response.data.data,order_items_preparing)
            }
           
            return response.data.data

    }
 
    socket.on('updateOrder', (order) => {
        remove_order(order)
        toast.info(`The Order (#${order.id}) was updated!`)
    })

    return {
        orders,
        my_orders,
        total_my_orders,
        loadNumberOrdersMonth,
        load_orders,
        get_page,
        get_orders,
        total_orders,
        insert_order,
        delete_order,
        loadNumberOrdersMonth,
        loadRevenueOrders,
        get_revenue_orders,
        loadNumberOrdersThisMonth,
        get_orders_this_month,
        loadOrderItems,
        get_order_items,
        loadOrderItemsWaiting,
        get_order_items_waiting,
        my_orders_delivery,
        update_order_status,
        count_orders,
        count_orders_by_status,
        loadOrderItemsPreparing,
        get_order_items_preparing,
        update_order_items_status,
        get_page_preparation

    }
})
