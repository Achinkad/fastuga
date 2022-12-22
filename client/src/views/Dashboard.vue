<script setup>
import { ref,onMounted, computed } from 'vue'
import { useOrderStore } from '../stores/order.js'
import { useUserStore } from '../stores/user.js'
import { useProductStore } from '../stores/product.js'

const userStore = useUserStore()
const orderStore = useOrderStore()
const productStore = useProductStore()

const series = ref([{
    name: 'Orders',
    data: []
}])

const loadBestProducts = () => { productStore.load_best_products() }
const best_products = computed(() => { return productStore.best_products })
const loadCustomersCreatedThisMonth = () => { userStore.loadCustomersCreatedThisMonth() }
const customers_this_month = computed(() => { return userStore.get_customers_this_month() })
const loadRevenueOrders = () => { orderStore.loadRevenueOrders() }
const loadNumberOrdersThisMonth = () => { orderStore.loadNumberOrdersThisMonth() }
const orders_this_month = computed(() => { return orderStore.get_orders_this_month() })
const revenue = computed(() => { return orderStore.get_revenue_orders() })

const loadNumberOrdersMonth = async () => {
    const numbers = await orderStore.loadNumberOrdersMonth()
    series.value[0].data = numbers
}

const options = {
    chart: {
        id: 'orders-per-month',
        toolbar: {
            show: true,
            offsetX: -10,
            offsetY: -53,
        }
    },
    xaxis: {
        categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        labels: {
            style: {
                colors: '#6c757d'
            }
        }
    },
    yaxis: {
        labels: {

            style: {
                colors: '#6c757d'
            }
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '20%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    fill: {
        colors: '#f0bc74'
    },
    grid: {
        borderColor: '#f9f9f9',
    }
}
onMounted(async () => {
   await loadNumberOrdersMonth()
   await loadRevenueOrders()
   await loadNumberOrdersThisMonth()
   await loadCustomersCreatedThisMonth()
   await loadBestProducts()
})
</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="p-title-box">
                    <div>
                        <h4 class="p-title">Dashboard</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-5 col-lg-12">
                <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="bi bi-people-fill card-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">All Customers</h5>
                                <h3 class="mt-3 mb-3">{{ customers_this_month[0] }}</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2" v-if="customers_this_month[1] > 0">
                                        <i class="bi bi-arrow-up" id="arrow-icons"></i>
                                        {{ customers_this_month[1].toFixed(2) }}%
                                    </span>
                                    <span class="text-danger me-2" v-if="customers_this_month[1] <= 0">
                                        <i class="bi bi-arrow-down" id="arrow-icons"></i>
                                        {{ customers_this_month[1].toFixed(2) }}%
                                    </span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="bi bi-cart-plus-fill card-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Orders This Month</h5>
                                <h3 class="mt-3 mb-3">{{ orders_this_month[0] }}</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2" v-if="orders_this_month[1] > 0">
                                        <i class="bi bi-arrow-up" id="arrow-icons"></i>
                                        {{ orders_this_month[1].toFixed(2) }}%
                                    </span>
                                    <span class="text-danger me-2" v-if="orders_this_month[1] <= 0">
                                        <i class="bi bi-arrow-down" id="arrow-icons"></i>
                                        {{ orders_this_month[1].toFixed(2) }}%
                                    </span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="bi bi-currency-euro card-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Revenue This Month</h5>
                                <h3 class="mt-3 mb-3">{{ Math.floor(revenue[0]) }}€</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2" v-if="revenue[1] > 0">
                                        <i class="bi bi-arrow-up" id="arrow-icons"></i>
                                        {{ revenue[1].toFixed(2) }}%
                                    </span>
                                    <span class="text-danger me-2" v-if="revenue[1] <= 0">
                                        <i class="bi bi-arrow-down" id="arrow-icons"></i>
                                        {{ revenue[1].toFixed(2) }}%
                                    </span>

                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="bi bi-graph-up card-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Best Selling Products</h5>
                                <br>
                                <div class="row mb-2" v-for="(product,index) in best_products" :key="product.id">
                                    <div class="row d-flex">
                                        <div class="col">
                                            <span class="me-1">{{index+1}}.</span>
                                            <router-link
                                            style="text-decoration:underline;color:#212529;"
                                            :to="{ name: 'Product', params: { id: product.id } }" :title="`View product ${product.name}`">
                                                {{product.name}}
                                            </router-link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7 col-lg-12">
                <div class="card card-h-100">
                    <div class="d-flex card-header justify-content-between align-items-center">
                        <h4 class="header-title">Orders Per Month</h4>
                    </div>
                    <div class="card-body pt-0">
                        <div class="chart">
                            <apexchart width="100%" height="247px" type="bar" :options="options" :series="series">
                            </apexchart>
                        </div>
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

.product-photo {
    width: 2.8rem;
    height: 2.8rem;
    border-radius: 50%;
}
</style>
