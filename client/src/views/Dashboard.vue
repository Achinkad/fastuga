<script setup>
import { ref,onMounted, inject} from 'vue'

const axios = inject('axios')
const serverBaseUrl = inject("serverBaseUrl");
const orders = ref([])
var value_status = ref("all");
const loadOrders = () => {
    axios.get(serverBaseUrl + '/api/orders', {
        params: {
            status: value_status.value
        }
    })
        .then((response) => {
            orders.value = response.data.data

        })
        .catch((error) => {
            console.log(error)
        })
}
onMounted(() => {
    loadOrders()
})
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
            show: true,
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

const series = [{
    name: 'Orders',
    data: [30, 40, 45, 50, 49, 60, 70, 91, 50, 67, 75, 87]
}]

</script>

<template>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="p-title-box">
                    <div class="p-title-right">
                        <h4 class="p-title">Dashboard</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-5 col-lg-6">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="bi bi-people-fill card-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Customers</h5>
                                <h3 class="mt-3 mb-3">36,254</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2">
                                        <i class="bi bi-arrow-up" id="arrow-icons"></i>
                                        5.27%
                                    </span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="bi bi-cart-plus-fill card-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Orders</h5>
                                <h3 class="mt-3 mb-3">5,543</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-danger me-2">
                                        <i class="bi bi-arrow-down" id="arrow-icons"></i>
                                        1.03%
                                    </span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="bi bi-currency-euro card-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Revenue</h5>
                                <h3 class="mt-3 mb-3">6,254€</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2">
                                        <i class="bi bi-arrow-up" id="arrow-icons"></i>
                                        0.23%
                                    </span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="bi bi-graph-up card-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0">Growth</h5>
                                <h3 class="mt-3 mb-3">+ 30.56%</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2">
                                        <i class="bi bi-arrow-up" id="arrow-icons"></i>
                                        1.98%
                                    </span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7 col-lg-6">
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
</style>
