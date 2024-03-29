<script setup>

import { computed, onBeforeMount, inject, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useOrderStore } from '../stores/order.js'
import { useProductStore } from '../stores/product.js'

const productStore = useProductStore()
const orderStore = useOrderStore()
const router = useRouter()
const socket = inject("socket");
const serverBaseUrl = inject("serverBaseUrl")

const loadBestProducts = () => {
    productStore.load_best_products()
        .catch((error) => {
            console.log(error)
        })
}

const anonymous_orders = computed(() => { return orderStore.get_anonymous_orders })
const best_products = computed(() => { return productStore.best_products })
const editClick = (order) => { router.push({ name: "Order", params: { id: order.id } }) }
const photoFullUrl = (product) => { return serverBaseUrl + "/storage/products/" + product.photo_url }


onMounted(() => {
    socket.emit("Anonymous")
})

onBeforeMount(() => {
    loadBestProducts()
})
</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="p-title-box">
                    <div>
                        <h4 class="p-title">Welcome to Fastuga</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-4 col-lg-4">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card widget-flat orange-bg">
                            <div class="card-body">
                                <h3 class="mt-2 mb-2 fw-bold">
                                    <router-link :to="{ name: 'Register' }"
                                        style="text-decoration:none!important;color:#1f2937;">
                                        Let's create an account!
                                    </router-link>
                                </h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-muted me-2">
                                        If you do, you can earn points to discount in your orders.
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="d-flex card-header justify-content-between align-items-center">
                                <h4 class="header-title">Our top 5 products</h4>
                            </div>
                            <div class="card-body pt-0">
                                <div class="best-products">
                                    <div class="row mb-3 align-items-center" v-for="product in best_products"
                                        :key="product.id">
                                        <div class="col-2 image">
                                            <img :src="photoFullUrl(product)" class="product-photo" />
                                        </div>
                                        <div class="col-10">
                                            <span><b>{{ product.name }}</b></span> <br>
                                            <span style="font-size:14px;"><i>{{ product.price }}€</i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8 col-lg-8">
                <div class="card card-h-100">
                    <div class="d-flex card-header justify-content-between align-items-center">
                        <h4 class="header-title">Your Orders</h4>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table table-responsive align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Ticket Number</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width:20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="anonymous_orders.length == 0">
                                    <td colspan="6" class="text-center" style="height:55px!important;">You don't have
                                        any orders... yet!</td>
                                </tr>
                                <tr v-for="order in anonymous_orders" :key="order.id">
                                    <td>#{{ order.id }}</td>
                                    <td>{{ order.ticket_number }}</td>
                                    <td>{{ order.total_price }}€</td>
                                    <td>
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
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <button class="btn btn-xs btn-light" title="View Order"
                                                @click="editClick(order)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.widget-flat {
    position: relative;
    overflow: hidden;
}

.card {
    border: none;
    margin-bottom: 24px;
    box-shadow: 0 0 35px 0 rgba(154, 161, 171, .15) !important;
    border-radius: 3px;
}

.card.orange-bg {
    background-color: #ffeed6 !important;
}

.product-photo {
    width: 2.8rem;
    height: 2.8rem;
    border-radius: 50%;
}

.card-header {
    margin-top: 0;
    background-color: #fff;
    border: 0;
    margin-bottom: 0;
    padding: 1.5rem;
}

.card-header .header-title {
    text-transform: uppercase;
    letter-spacing: .02em;
    font-size: .9rem;
    margin-top: 0;
}

.card-body {
    padding: 1.5rem;
}

.card-icon {
    color: #f0bc74;
    font-size: 16px;
    background-color: rgba(255, 240, 155, 0.25);
    height: 40px;
    width: 40px;
    text-align: center;
    line-height: 40px;
    border-radius: 3px;
    display: inline-block;
}

.text-muted {
    color: #8a969c !important;
}

.mt-3 {
    margin-top: 1.5rem !important;
}

.mb-3 {
    margin-bottom: 1.5rem !important;
}

.me-2 {
    margin-right: .75rem !important;
}

.text-success {
    color: rgb(10, 207, 151) !important;
}

.text-danger {
    color: rgb(250, 92, 124) !important;
}

#arrow-icons {
    font-size: 14.4px;
    margin: 0;
}

.bi {
    margin: 0 !important;
}

.text-nowrap {
    font-size: 14.4px;
    color: rgb(138, 150, 156);
}

.image img {
    position: relative;
    left: 5px;
}
</style>
