<script setup>
import { computed, watch, onBeforeMount, inject, ref } from 'vue'
import { useUserStore } from '../stores/user.js'
import { useOrderStore } from '../stores/order.js'
import { Bootstrap5Pagination } from 'laravel-vue-pagination'

const toast = inject("toast")
const userStore = useUserStore()
const orderStore = useOrderStore()
const serverBaseUrl = inject("serverBaseUrl")
const user = ref(userStore.user)


const loadOrderItemsWaiting = (page = 1) => {
    orderStore.loadOrderItemsWaiting(page)

}
const loadOrderItemsPreparing = (page = 1) => {
    orderStore.loadOrderItemsPreparing(page)

}

const updateStatus = (order_item, status) => {

    orderStore.update_order_items_status(order_item, status)
        .then((response) => {
            if(status == "P"){
                   toast.info("Order-item " + order_item.id + " changed to status preparing!")
            }
            if(status == "R"){
                   toast.info("Order-item " + order_item.id + " changed to status ready!")
            }

        })
        .catch((error) => {
            console.log(error)
        })
}

const pagination = computed(() => {
    return orderStore.get_page()
})

const pagination_preparation = computed(() => { return orderStore.get_page_preparation() })
const order_items_waiting = computed(() => { return orderStore.get_order_items_waiting() })
const order_items_preparing = computed(() => { return orderStore.get_order_items_preparing() })

const photoFullUrl = (product) => { return serverBaseUrl + "/storage/products/" + product.photo_url }


watch(() => userStore.user, function () {
    loadOrderItemsWaiting()
    loadOrderItemsPreparing()
})
watch(() => orderStore.order_items, function () {
    loadOrderItemsWaiting()
    loadOrderItemsPreparing()
})

onBeforeMount(() => {
    loadOrderItemsWaiting()
    loadOrderItemsPreparing()
})
</script>

<template>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="p-title-box">
                    <div>
                        <h4 class="p-title">Welcome to Fastuga, {{ user.name }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card card-h-100" style="border-top: 3px solid #39afd1!important;">
                    <div class="d-flex card-header justify-content-between align-items-center">
                        <h4 class="header-title" v-if="order_items_waiting.length != 0">Orders
                            Waiting({{ order_items_waiting.length }})</h4>
                        <h4 class="header-title" v-else>Waiting Orders</h4>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table table-responsive align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Photo</th>
                                    <th>Product Name</th>
                                    <th>Notes</th>
                                    <th class="text-center" style="width:20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="order_items_waiting.length == 0">
                                    <td colspan="6" class="text-center" style="height:55px!important;">No data
                                        available.</td>
                                </tr>
                                <tr v-for="order_item in order_items_waiting" :key="order_item.id">
                                    <td class="align-middle">
                                        <img :src="photoFullUrl(order_item.product)" class="rounded-circle img_photo" />
                                    </td>
                                    <td>{{ order_item.product.name }}</td>
                                    <td style="max-width: 150px;word-wrap: break-word;">{{ order_item.notes }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <button class="btn btn-xs btn-light" title="Accept Order-Item"
                                                @click="updateStatus(order_item, 'P')">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-3">
                            <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadOrderItemsWaiting"
                                :limit="2">
                            </Bootstrap5Pagination>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6">
                <div class="card card-h-100" style="border-top: 3px solid #0acf97!important;">
                    <div class="d-flex card-header justify-content-between align-items-center">
                        <h4 class="header-title" v-if="order_items_preparing.length != 0">Orders being prepared
                            ({{ order_items_preparing.length }})</h4>
                        <h4 class="header-title" v-else>Orders being prepared </h4>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table table-responsive align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Photo</th>
                                    <th>Product Name</th>
                                    <th>Notes</th>
                                    <th class="text-center" style="width:20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="order_items_preparing.length == 0">
                                    <td colspan="6" class="text-center" style="height:55px!important;">No data
                                        available.</td>
                                </tr>
                                <tr v-for="order_item in order_items_preparing" :key="order_item.id">
                                    <td class="align-middle">
                                        <img :src="photoFullUrl(order_item.product)" class="rounded-circle img_photo" />
                                    </td>
                                    <td>{{ order_item.product.name }}</td>
                                    <td style="max-width: 150px;word-wrap: break-word;">{{ order_item.notes }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <button class="btn btn-xs btn-light" title="Done Cooking"
                                                @click="updateStatus(order_item, 'R')">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-3">
                       
                            <Bootstrap5Pagination :data="pagination_preparation"
                                @pagination-change-page="loadOrderItemsPreparing" :limit="2">
                            </Bootstrap5Pagination></div>
                    
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

.img_photo {
    width: 3.2rem;
    height: 3.2rem;
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
</style>
