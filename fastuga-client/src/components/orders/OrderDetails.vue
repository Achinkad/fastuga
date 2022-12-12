<script setup>
import { onMounted, ref, watch, inject } from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'
import productNoneUrl from '@/assets/product-none.png'
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { useUserStore } from '../../stores/user.js'

const serverBaseUrl = inject("serverBaseUrl")
const axios = inject('axios')
const paginationNewOrder = ref({})

axios.defaults.headers.common.Authorization = "Bearer " + sessionStorage.token

const userStore = useUserStore()

const props = defineProps({
    order: {
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
});
const newOrderItem = () => {
    return {
        notes: null,
        product_id: null,
    };
};

const emit = defineEmits(["cancel", "add"]);
const products = ref([]);

var value_type = ref("all");
const editingOrder = ref(props.order);
var currentCustomer = ref();

watch(
    () => props.order,
    (newOrder) => {
        editingOrder.value = newOrder;
    }
);
watch(value_type, () => {
    getProducts();
    totalPrice();
});

const getProducts = (page = 1) => {
    axios.get(serverBaseUrl + '/api/products?page=' + page, {
        params: {
            type: value_type.value
        }

    })
        .then((response) => {
            products.value = response.data.data
            paginationNewOrder.value = response.data
        })
        .catch((error) => {
            console.log(error);
        });
};

const getCurrentCustomer = () => {

    axios.get(serverBaseUrl + '/api/customers/user/' + userStore.user.id)
        .then((response) => {
            console.log(response)
            if (response.data) {
                currentCustomer = response.data.data
            console.log(currentCustomer)
            }

        })
        .catch((error) => {
            console.log(error);
        });
}


const addProduct = (product) => {
    const orderItem = ref(newOrderItem());

    orderItem.value.product_id = product.id;

    orderItem.value.price = product.price

    orderItem.value.product = product;
    editingOrder.value.order_item.push(orderItem.value);


};
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
   
  
    formData.append('points_used_to_pay',editingOrder.value.points_used_to_pay)
   
    editingOrder.value.order_item.forEach((item) => { formData.append('items[]', JSON.stringify(item))});
    
    emit("add", formData);
}

const fillOrder = () => {
    editingOrder.value.total_price = totalPrice();


    editingOrder.value.status='P';
    editingOrder.value.ticket_number=1;
    if(userStore.user && currentCustomer){
        editingOrder.value.customer_id=currentCustomer.id;
        console.log(currentCustomer)
    }

    editingOrder.value.total_paid=2.8; //ver como funciona realmente o pagamento
    editingOrder.value.points_used_to_pay=0;

}

const deleteProduct = (product, position) => {

    editingOrder.value.order_item.splice(position - 1, 1)

};

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
    var total = 0;
    editingOrder.value.order_item.forEach((value) => {
        //console.log(value.price)
        total += parseFloat(value.price)
    });
    return total.toFixed(2);
};
const points = () => {
   return currentCustomer.points;
};

const countProduct = (product) => {
    let count = 0
    editingOrder.value.order_item.forEach(order => {
        if (order.product.id == product.id) {
            count++
        }
    })
    //console.log(count)
    return count
}
const cancel = () => {
    emit("cancel", editingOrder.value);
};
const userPhotoFullUrl = (user) => {
    return user.photo_url
        ? serverBaseUrl + "/storage/fotos/" + user.photo_url
        : avatarNoneUrl;
};
const productPhotoFullUrl = (product) => {
    return product.photo_url
        ? serverBaseUrl + "/storage/products/" + product.photo_url
        : productNoneUrl;
};


onMounted(() => {
    getProducts();
    if (userStore.user && userStore.user.type == 'C') {
        getCurrentCustomer();
        points();
    }

});
</script>

<template>

    <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
        <div class="box_form">
            <h3 class="mt-5 mb-3" v-if="$route.name == 'Order'">
                Order #{{ editingOrder.id }} Details
            </h3>
            <h3 class="mt-5 mb-3" v-if="$route.name == 'NewOrder'">Adding New Order</h3>
            <hr style="color:beige" />
            <div class="mb-3">
                <label for="payment_type">Payment Type</label>
                <select id="payment_type" name="payment_type" v-model="editingOrder.payment_type"
                    v-if="(userStore.user && (userStore.user.type == 'C' || (userStore.user.type == 'EM' && $route.name == 'NewOrder'))) || !userStore.user">
                    <option value="VISA">Visa</option>
                    <option value="PAYPAL">PayPal</option>
                    <option value="MBWAY">MBWay</option>
                </select>
                <input type="text" class="form-control" placeholder="Payment Type" required
                    v-model="editingOrder.payment_type" readonly v-if="userStore.user && userStore.user.type == 'EM' && $route.name == 'Order'" />

                <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
            </div>

            <div class="mb-3">
                <label for="inputPaymentReference" class="form-label">Payment Reference</label>
                <input type="text" class="form-control" id="inputPaymentReference" placeholder="Payment Reference"
                    required v-model="editingOrder.payment_reference" v-if="(userStore.user && (userStore.user.type == 'C' || (userStore.user.type == 'EM' && $route.name == 'NewOrder'))) || !userStore.user" />

                <input type="text" class="form-control" id="inputPaymentReference" placeholder="Payment Reference"
                    required v-model="editingOrder.payment_reference" readonly v-if="userStore.user && userStore.user.type == 'EM' && $route.name == 'Order'" />

                <field-error-message :errors="errors" fieldName="payment_reference"></field-error-message>

            </div>
            
            <span style="font-size: large;"> Total Price: {{ totalPrice() }} €</span>
            <br>
            <br>
            <div v-if="userStore.user && userStore.user.type=='C'">
                <span style="font-size: large;">Points available: {{ points() }} </span>
                <br>
                <br>
                <span style="font-size: large;">Points Used:</span>
                <input type="range" :value="points()" min="0" :max="points()" step="1" oninput="this.nextElementSibling.value = this.value">
                <output>{{ points() }}</output>
                <br>
                <br>
            </div>
            <div v-if="userStore.user && userStore.user.type == 'EM' && $route.name == 'Order'">
                <div class="mb-3">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" v-model="editingOrder.date" readonly />
                    <field-error-message :errors="errors" fieldName="date"></field-error-message>
                </div>

                <div class="mb-3" v-if="editingOrder.customer_id != null">
                    <label for="inputCustomer" class="form-label">Customer Name: </label>
                    <br />
                    <img :src="userPhotoFullUrl(editingOrder.customer.user)" class="rounded-circle img_photo" />
                    <span style="padding-left: 10px;">{{ editingOrder.customer.user.name }}</span>
                </div>

                <div class="mb-3" v-if="editingOrder.delivered_by != null">
                    <label for="inputDeliveredBy" class="form-label">Delivered By: </label>
                    <br />
                    <img :src="userPhotoFullUrl(editingOrder.user)" class="rounded-circle img_photo" />
                    <span style="padding-left: 10px;">{{ editingOrder.user.name }}</span>
                </div>
            </div>
        </div>

        <field-error-message :errors="errors" fieldName="order_item"></field-error-message>

        <div class="container">
            <div class="row">

                <div class="col-md child">
                    <label class="form-label" style="font-size: xx-large;color:white;">Products In the Order: </label>
                    <div v-for="n in editingOrder.order_item.length">
                        <br>
                        <img :src="productPhotoFullUrl(editingOrder.order_item[n - 1].product)"
                            class="rounded-circle img_photo" />
                        <span class="item">{{ editingOrder.order_item[n - 1].product.name }}</span>
                        <div v-if="$route.name != 'Order'">
                            <button type="button" class="bi bi-plus" id="add"
                                @click="addProduct(editingOrder.order_item[n - 1].product); countProduct(editingOrder.order_item[n - 1].product)"
                                v-if="editingOrder.status != 'C'"></button>
                            <button type="button" class="bi bi-dash" id="add"
                                @click="deleteProduct(editingOrder.order_item[n - 1].product, n)"
                                v-if="editingOrder.status != 'C'"></button>
                        </div>
                        <span id="badge" class="badge bg-secondary">{{ countProduct(editingOrder.order_item[n -
                                1].product)
                        }}</span>

                        <div>
                            <label for="inputNotes" style="color:white" class="form-label">Notes</label>
                            <textarea class="form-control" id="inputNotes" rows="1"
                                v-model="editingOrder.order_item[n - 1].notes"
                                v-if="userStore.user && userStore.user.type == 'C'"></textarea>
                            <textarea class="form-control" id="inputNotes" rows="1"
                                v-model="editingOrder.order_item[n - 1].notes" v-if="userStore.user && userStore.user.type == 'EM'"
                                readonly></textarea>
                            <field-error-message :errors="errors" fieldName="notes"></field-error-message>
                        </div>
                    </div>
                </div>


                <div class="col-md twin "
                    v-if="(editingOrder.status == 'C' || editingOrder.status == 'EM') || $route.name == 'NewOrder'">
                    <label style="font-size: xx-large;color:white" class="form-label">Menu: </label>
                    <br>
                    <Bootstrap5Pagination :data="paginationNewOrder" @pagination-change-page="getProducts" :limit="5">
                    </Bootstrap5Pagination>
                    <select class="form-select" id="selectType" v-model="value_type">
                        <option value="all" selected>Any</option>
                        <option value="hot dish">Hot Dishes</option>
                        <option value="cold dish">Cold Dishes</option>
                        <option value="drink">Drinks</option>
                        <option value="dessert">Desserts</option>
                    </select>
                    <div class="mb-3">
                        <div v-for="n in products.length">
                            <br>
                            <img :src="productPhotoFullUrl(products[n - 1])" class="rounded-circle img_photo" />
                            <span class="item"> {{ products[n - 1].name }}</span>

                            <button type="button" class="bi bi-plus" id="add"
                                @click="addProduct(products[n - 1]); countProduct(products[n - 1])"></button>
                            <button type="button" class="bi bi-dash" id="add"
                                @click="deleteProductInAdd(products[n - 1]); countProduct(products[n - 1])"></button>
                            <hr id="hr" />

                </div>
            </div>


        </div>
    </div>
</div>

        <div class="mb-3 d-flex justify-content-end">
            <button type="button" id="button" class="btn btn-primary px-5" @click="add"
                v-if="$route.name == 'NewOrder'">
                Add Order
            </button>

            <button type="button" class="btn btn-light px-5" @click="cancel">
                Cancel
            </button>
        </div>
    </form>
</template>

<style scoped>
.checkCompleted {
    min-height: 2.375rem;
}

.box_form {
    border: 3px solid #dc9c37ed;
    border-radius: 25px;
}

.child {
    display: flex;
    border: 3px solid #dc9c37ed;
    padding: 1rem 1rem;
    vertical-align: middle;
    border-radius: 25px;
    margin: 40px;
    flex-direction: column;
    height: 100%;
    flex-grow: 1;
    background-image: linear-gradient(rgba(0, 0, 0, 0.6),
            rgba(0, 0, 0, 0.6)), url(@/assets/italian.jpg);
    background-size: cover;
    background-position-x: center;

}

#hr {
    color: white;

}

.twin {
    display: flex;
    border: 3px solid #dc9c37ed;
    padding: 1rem 1rem;
    vertical-align: middle;
    border-radius: 25px;
    margin: 40px;
    flex-direction: column;
    height: 100%;
    flex-grow: 1;
    background-image: linear-gradient(rgba(0, 0, 0, 0.6),
            rgba(0, 0, 0, 0.6)), url(@/assets/italian.jpg);
    background-size: cover;
    background-position-x: center;
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
    color: beige;
    font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
    font-size: large;
}

.form-label {
    font-size: 20px;
}

#add {
    margin: 2px;
    float: right;
}

.img_photo {
    width: 3.2rem;
    height: 3.2rem;
}
</style>
