<script setup>
import { inject, onMounted, ref, watch, computed } from "vue"
import { useUserStore } from '../../stores/user.js'
import { Bootstrap5Pagination } from 'laravel-vue-pagination'
import { useProductStore } from '../../stores/product.js'


import productNoneUrl from '@/assets/product-none.png'

const serverBaseUrl = inject("serverBaseUrl")
const axios = inject('axios')

const productStore = useProductStore()

var value_type = ref("all")


const userStore = useUserStore()

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    customer: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        required: false,
    },
    operationType: {
        type: String,
        default: "insert",
    },
})

const newOrderItem = () => {
    return {
        notes: null,
        product_id: null,
    }
}

const emit = defineEmits(["cancel", "add"])

const editingOrder = ref(props.order)

const customer = ref(props.customer)

editingOrder.value.points_used_to_pay = "0"

watch(() => props.order, (newOrder) => { editingOrder.value = newOrder })
watch(value_type, () => {
    loadProducts()
    totalPrice()
})

const loadProducts = (page = 1) => {
   productStore.load_products(page,value_type.value)
}

const paginationNewOrder = computed(() => { return productStore.get_page() })
const products = computed(() => { return productStore.get_products() })

const addProduct = (product) => {
    const orderItem = ref(newOrderItem())
    orderItem.value.product_id = product.id
    orderItem.value.price = product.price
    orderItem.value.product = product
    editingOrder.value.order_item.push(orderItem.value)
}

const add = () => {
    fillOrder();
    let formData = new FormData()

    formData.append('total_price', editingOrder.value.total_price);

    if (editingOrder.value.payment_type != undefined) {
        formData.append('payment_type', editingOrder.value.payment_type);
    }

    if (editingOrder.value.payment_reference != undefined) {
        formData.append('payment_reference', editingOrder.value.payment_reference);
    }

    if (editingOrder.value.customer_id != undefined) {
        formData.append('customer_id', editingOrder.value.customer_id);

    }
    if (userStore.user && userStore.user.type == 'EM') {
        editingOrder.value.points_used_to_pay = 0;
    }
    formData.append('points_used_to_pay', editingOrder.value.points_used_to_pay)

    editingOrder.value.order_item.forEach((item) => { formData.append('items[]', JSON.stringify(item)) });

    emit("add", formData);
}

const fillOrder = () => {
    editingOrder.value.total_price = totalPrice()
    editingOrder.value.status = 'P'
    editingOrder.value.ticket_number = 1
    editingOrder.value.total_paid = editingOrder.value.total_price;
}


const deleteProductInAdd = (product) => {
    var flag = 0
    editingOrder.value.order_item.forEach((value, index) => {
        if (value.product.id === product.id) {
            if (flag == 0) {
                editingOrder.value.order_item.splice(index, 1)
                flag = 1
            }
        }
    })
}

const totalPrice = () => {
    var total = 0
    editingOrder.value.order_item.forEach((value) => {
        total += parseFloat(value.price)
    })

    if (total <= points_to_pay.value / 2) {
        points_to_pay.value = 0
    } else {
        total -= points_to_pay.value / 2
    }

    return total.toFixed(2)
}

const countProduct = (product) => {
    let count = 0
    editingOrder.value.order_item.forEach(order => {
        if (order.product.id == product.id) {
            count++
        }
    })
    return count
}

const cancel = () => { emit("cancel", editingOrder.value) }

const productPhotoFullUrl = (product) => {
    return product.photo_url ? serverBaseUrl + "/storage/products/" + product.photo_url : productNoneUrl
}

const points_to_pay = ref(0)
watch(() => editingOrder.value.points_used_to_pay, (newValue) => {
    points_to_pay.value = newValue
})

watch(
    () => props.customer,
    (newCustomer) => {

        customer.value = newCustomer
        editingOrder.value.payment_reference=newCustomer.default_payment_reference
        editingOrder.value.payment_type=newCustomer.default_payment_type
    }
)

const payment_type = ref("Payment Reference")
watch(() => editingOrder.value.payment_type, (newValue) => {

    switch (newValue) {
        case "VISA":
            payment_type.value = "Visa ID"
            break;

        case "MBWAY":
            payment_type.value = "Phone Number"
            break;

        case "PAYPAL":
            payment_type.value = "E-Mail Address"
            break;

        default:
            payment_type.value = "Payment Reference"
            break;
    }
})


onMounted(() => {
    loadProducts()

})
</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex p-title-box">
                    <div>
                        <h4 class="p-title" v-if="$route.name == 'Order'"> Order #{{ editingOrder.id }} Details </h4>
                        <h4 class="p-title" v-if="$route.name == 'NewOrder'">Register your order</h4>
                    </div>
                </div>
            </div>
        </div>
        <form class="needs-validation" novalidate @submit.prevent="save">
            <div class="row">
                <div class="col-8">
                    <div class="card h-100" v-if="(!userStore.user || (userStore.user && userStore.user.type == 'C')) && $route.name == 'NewOrder'">
                        <div class="card-body d-flex align-items-center">
                            <div class="w-100">
                                <div class="row">
                                    <div class="col">
                                        <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span> </label>
                                        <select id="payment_type" name="payment_type" class="form-select" v-model="editingOrder.payment_type">
                                            <option value="VISA">Visa</option>
                                            <option value="PAYPAL">PayPal</option>
                                            <option value="MBWAY">MBWay</option>
                                        </select>
                                        <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
                                    </div>

                                    <div class="col">
                                        <label for="inputPaymentReference" class="form-label">Payment Reference <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inputPaymentReference"
                                        :placeholder="payment_type" required
                                        v-model="editingOrder.payment_reference"
                                        v-if="((userStore.user && (userStore.user.type == 'C' || userStore.user.type == 'EM')) || !userStore.user) && $route.name == 'NewOrder'" />
                                        <field-error-message :errors="errors" fieldName="payment_reference"></field-error-message>
                                    </div>
                                </div>
                                <div class="row mt-3 mb-3" v-if="userStore.user && userStore.user.type == 'C'">
                                    <div class="col">
                                        <label class="form-label">Points to use</label>
                                        <select name="points" class="form-select" v-model="editingOrder.points_used_to_pay">
                                            <option value="0" selected>0</option>
                                            <option v-if="Math.floor(customer.points / 10) > 0" v-for="n in Math.floor(customer.points / 10)" :value="n * 10">
                                                {{ n * 10 }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <field-error-message :errors="errors" fieldName="items"></field-error-message>
                        </div>
                    </div>

                    <div class="card" v-if="$route.name == 'Order'">
                        <div class="d-flex card-header justify-content-between align-items-center mb-0 pb-1">
                            <h4 class="header-title">Your Order Products</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md">
                                    <table class="table table-responsive align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:47%">Product</th>
                                                <th>Price</th>
                                                <th style="width:40%">Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="editingOrder.order_item.length == 0">
                                                <td colspan="3" class="text-center" style="height:55px!important;">You don't have any products :(</td>
                                            </tr>
                                            <tr v-for="n in editingOrder.order_item.length">
                                                <td>
                                                    <img :src="productPhotoFullUrl(editingOrder.order_item[n - 1].product)" class="rounded-circle img_photo"/>
                                                    <span class="ms-3">{{ editingOrder.order_item[n - 1].product.name }}</span>
                                                </td>
                                                <td>{{ editingOrder.order_item[n - 1].product.price }}€</td>
                                                <td class="center">
                                                    <textarea class="form-control" id="inputNotes" rows="1" v-model="editingOrder.order_item[n - 1].notes" v-if="$route.name == 'NewOrder'"></textarea>
                                                    <textarea class="form-control" id="inputNotes" rows="1" v-model="editingOrder.order_item[n - 1].notes" v-if="$route.name == 'Order'" readonly></textarea>
                                                    <field-error-message :errors="errors" fieldName="notes"></field-error-message>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <div class="row">
                        <div class="col">
                            <div class="card widget-flat">
                                <div class="card-body d-flex align-items-center">
                                <div class="row">
                                    <div class="col-12">
                                        <h3 class="mt-2 mb-2 fw-bold">Total price: {{ totalPrice() }}€</h3>
                                    </div>
                                    <div class="col">
                                        <field-error-message :errors="errors" fieldName="total_price"></field-error-message>
                                    </div>
                                    
                                </div>
                                    
                                    
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="row" v-if="(userStore.user && userStore.user.type == 'C') && $route.name == 'NewOrder'">
                        <div class="col">
                            <div class="card widget-flat orange-bg h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <h3 class="mt-2 mb-2 fw-bold">You've {{ customer.points }} Points!</h3>
                                        <p class="mb-2 text-muted">
                                            <span class="text-muted me-2">
                                                You can discount until {{(Math.floor(customer.points / 10)) * 5}}€ in this order.
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-if="((userStore.user && (userStore.user.type == 'C' || userStore.user.type == 'EM')) || !userStore.user) && $route.name == 'Order'">
                        <div class="col">
                            <div class="card widget-flat orange-bg h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="row">
                                        <h3 class="mb-3"><b>Payment Information</b></h3>
                                        <div class="col-12">
                                            <p class="mb-1">
                                                Payment Type: {{ editingOrder.payment_type }}
                                            </p>
                                        </div>
                                        <div class="col-12">
                                            <p class="mb-1">
                                                Payment Reference: {{ editingOrder.payment_reference }}
                                            </p>
                                        </div>
                                        <div class="col-12" v-if="userStore.user">
                                            <p class="mb-1">
                                                Points Used: {{ editingOrder.points_used_to_pay }}
                                            </p>
                                        </div>
                                        <div class="col-12" v-if="userStore.user && userStore.user.type == 'C'">
                                            <p class="mb-0">
                                                Points Gained: {{ editingOrder.points_gained }}
                                            </p>
                                        </div>
                                        <div class="col-12" v-if="userStore.user && userStore.user.type == 'EM'">
                                            <p class="mb-0">
                                                Total paid with points: {{ editingOrder.total_paid_with_points }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4" v-if="$route.name == 'NewOrder'">
                <div class="col-7">
                    <div class="card">
                        <div class="d-flex card-header justify-content-between align-items-center mb-0 pb-1">
                            <h4 class="header-title">Your Order Products</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md">
                                    <table class="table table-responsive align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:47%">Product</th>
                                                <th>Price</th>
                                                <th style="width:40%">Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="editingOrder.order_item.length == 0">
                                                <td colspan="3" class="text-center" style="height:55px!important;">You don't have any products selected yet :(</td>
                                            </tr>
                                            <tr v-for="n in editingOrder.order_item.length">
                                                <td>
                                                    <img :src="productPhotoFullUrl(editingOrder.order_item[n - 1].product)" class="rounded-circle img_photo"/>
                                                    <span class="ms-3">{{ editingOrder.order_item[n - 1].product.name }}</span>
                                                </td>
                                                <td>{{ editingOrder.order_item[n - 1].product.price }}€</td>
                                                <td class="center">
                                                    <textarea class="form-control" id="inputNotes" rows="1" v-model="editingOrder.order_item[n - 1].notes" v-if="$route.name == 'NewOrder'"></textarea>
                                                    <textarea class="form-control" id="inputNotes" rows="1" v-model="editingOrder.order_item[n - 1].notes" v-if="$route.name == 'Order'" readonly></textarea>
                                                    <field-error-message :errors="errors" fieldName="notes"></field-error-message>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-5">
                    <div class="card">
                        <div class="d-flex card-header justify-content-between align-items-center mb-0 pb-1">
                            <h4 class="header-title">Menu of products</h4>
                        </div>
                        <div class="card-body">
                            <div class="row d-flex align-item-center">
                                <div class="col-8">
                                    <div class="d-flex">
                                        <div class="d-flex align-items-center">
                                            <label for="selectType" class="me-2">Type</label>
                                            <select class="form-select" id="selectType" v-model="value_type">
                                                <option value="all" selected>Any</option>
                                                <option value="hot dish">Hot Dishes</option>
                                                <option value="cold dish">Cold Dishes</option>
                                                <option value="drink">Drinks</option>
                                                <option value="dessert">Desserts</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-4 d-flex justify-content-end align-items-end">
                                    <Bootstrap5Pagination :data="paginationNewOrder" @pagination-change-page="loadProducts" :limit="5"></Bootstrap5Pagination>
                                </div>
                            </div>
                            <div class="mb-3 mt-2">
                                <div v-for="n in products.length" class="mb-4">
                                    <div class="row">
                                        <div class="col-1">
                                            <img :src="productPhotoFullUrl(products[n - 1])" class="rounded-circle img_photo" />
                                        </div>
                                        <div class="col-7">
                                            <span class="ms-3"> <b>{{ products[n - 1].name }}</b> </span><br>
                                            <span class="ms-3"> {{ products[n - 1].price }}€</span>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" class="btn btn-menu bi bi-plus" id="add" @click="addProduct(products[n - 1]); countProduct(products[n - 1])"></button>
                                            <button type="button" class="btn btn-menu bi bi-dash ms-2" id="add" @click="deleteProductInAdd(products[n - 1]); countProduct(products[n - 1])"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <div class="mb-3 d-flex justify-content-end" v-if="$route.name == 'NewOrder'">
            <button type="button" id="button" class="btn btn-warning px-4 btn-add me-2" @click="add">
                Add a New Order
            </button>
            <button type="button" class="btn btn-light px-4" @click="cancel">
                Cancel
            </button>
        </div>
    </form>
</div>
</template>

<style scoped>

td {
    word-wrap: break-word;
}

table {
    table-layout: fixed;
}

.btn.btn-menu {
    background-color: #fafbfe;
    border-radius: 10px;
}

.card.orange-bg {
    background-color: #ffeed6 !important;
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

.checkCompleted {
    min-height: 2.375rem;
}

.box_form {
    border: 3px solid #dc9c37ed;
    border-radius: 25px;
}

#slider {
    display: inline-block;
    vertical-align: middle;
}

.center {
    margin: auto;
    width: 50%;
    padding: 10px;
}

#hr {
    color: white;
}

#badge {
    font-size: medium;
}

.item {
    width: max-content;
    height: 30px;
    font-size: 20px;
    padding-left: 10px;
    padding-right: 20px;
    font-size: large;
}

#add {
    margin: 2px;
    float: right;
}

.img_photo {
    width: 3.2rem;
    height: 3.2rem;
}

.item-order {
    border: #dc9c37ed;
    border-width: 30px;
}
</style>
