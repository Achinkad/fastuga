<script setup>
import { onMounted, ref, watch, computed, inject } from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'
import productNoneUrl from '@/assets/product-none.png'
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
const serverBaseUrl = inject("serverBaseUrl")
const axios = inject('axios')
const paginationNewOrder = ref({})


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
    id: null,
    status: "R",
    price: 0,
    notes:null,
    custom: null,
    order_id: 0,
    order_local_number: 0,
    product_id: null,
    product: null,
    preparation_by: 0
  };
};

const emit = defineEmits(["save", "cancel","add"]);
const products = ref([]);
var value_type = ref("all");
const editingOrder = ref(props.order);

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
  axios.get(serverBaseUrl + '/api/products?page='+page, {
    params: {
      type: value_type.value
    }

  })
    .then((response) => {
      products.value = response.data.data
      paginationNewOrder.value=response.data
      console.log(products.value)
    })
    .catch((error) => {
      console.log(error);
    });
};

const save = () => {
  emit("save", editingOrder.value);
  console.log(editingOrder.value);
};


const addProduct = (product) => {
  const orderItem = ref(newOrderItem());
  const size = editingOrder.value.order_item.length
  if(product.type === "hot dish"){
    orderItem.value.status = "P"
  }
  
  orderItem.value.product_id = product.id;
  orderItem.value.order_local_number = size +1
  orderItem.value.id = size +1
  
  orderItem.value.order_id = editingOrder.value.id
  orderItem.value.price = product.price
  orderItem.value.product = product;
  editingOrder.value.order_item.push(orderItem.value);
  console.log(editingOrder.value);
  
};

const deleteProduct = (product,position) => {

  console.log(editingOrder.value.order_item)
  editingOrder.value.order_item.splice(position-1,1)
  
  console.log("No items to delete")
  console.log(editingOrder.value)

};

const deleteProductInAdd = (product) =>{
var flag=0

  editingOrder.value.order_item.forEach((value,index) =>{
    if(value.product.id===product.id){
      if(flag==0){
        editingOrder.value.order_item.splice(index,1)
        flag=1
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
  console.log(total)
  return total.toFixed(2);
};
const countProduct = (product) =>{
  let count = 0
 editingOrder.value.order_item.forEach(order =>{
    if(order.product.id==product.id){
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
  console.log(editingOrder.value)
  
});
</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
    <h3 class="mt-5 mb-3" v-if="$route.name == 'Order'">
      Editing Order #{{ editingOrder.id }}
    </h3>
    <h3 class="mt-5 mb-3" v-if="$route.name == 'NewOrder'">Adding New Order</h3>
    <hr />
    <div class="mb-3">
      <label for="payment_type">Payment Type</label>
      <select id="payment_type" name="payment_type" v-model="editingOrder.payment_type">
        <option value="VISA">Visa</option>
        <option value="PAYPAL">PayPal</option>
        <option value="MBWAY">MBWay</option>
      </select>
      <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
    </div>

    <div class="mb-3">
      <label for="inputPaymentReference" class="form-label">Payment Reference</label>
      <input type="text" class="form-control" id="inputPaymentReference" placeholder="Payment Reference" required
        v-model="editingOrder.payment_reference" />
      <field-error-message :errors="errors" fieldName="payment_reference"></field-error-message>
    </div>

    <div class="mb-3">
      <label for="date">Date</label>
      <input type="date" id="date" name="date" v-model="editingOrder.date" />
      <field-error-message :errors="errors" fieldName="date"></field-error-message>
    </div>

    <div class="mb-3" v-if="editingOrder.customer_id != null">
      <label for="inputCustomer" class="form-label">Customer Name: </label>
      <br />
      <img :src="userPhotoFullUrl(editingOrder.customer.user)" class="rounded-circle img_photo" />
      <span>{{ editingOrder.customer.user.name }}</span>
    </div>
    <span> Total Price: {{totalPrice()}} €</span>
    <div class="mb-3" v-if="editingOrder.delivered_by != null">
      <label for="inputDeliveredBy" class="form-label">Delivered By: </label>
      <br />
      <img :src="userPhotoFullUrl(editingOrder.user)" class="rounded-circle img_photo" />
      <span>{{ editingOrder.user.name }}</span>
    </div>
    
      

  <div class="container">
    <div class="row">
    
      <div class="col-md child">  
      <label class="form-label">Products In the Order: </label>
          <div v-for="n in editingOrder.order_item.length">
            <br>
            <img :src="productPhotoFullUrl(editingOrder.order_item[n - 1].product)" class="rounded-circle img_photo" />
            <span class="item">{{ editingOrder.order_item[n - 1].product.name }}</span>
            <button type="button" class="bi bi-plus" id="add"
              @click="addProduct(editingOrder.order_item[n - 1].product);countProduct(editingOrder.order_item[n - 1].product)" v-if="editingOrder.status!='C'"></button>
            <button type="button" class="bi bi-dash" id="add"
              @click="deleteProduct(editingOrder.order_item[n - 1].product,n)" v-if="editingOrder.status!='C'"></button>
            <p>Count is: {{ countProduct(editingOrder.order_item[n - 1].product) }}</p>
             <div class="mb-3">
               <label for="inputNotes" class="form-label">Notes</label>
               <textarea class="form-control" id="inputNotes" rows="1" v-model="editingOrder.order_item[n-1].notes"></textarea>
               <field-error-message :errors="errors" fieldName="notes"></field-error-message>
             </div>
          </div>
      </div>

      <!-- CONDIÇOES AINDA NAO FUNCIONAIS-->
    
    <div class="col-md child" v-if="editingOrder.status!='C'">
       <label class="form-label">All Products: </label>
       <br>
     <select class="form-select" id="selectType" v-model="value_type">
            <option value="all" selected>Any</option>
            <option value="hot dish">Hot Dishes</option>
            <option value="cold dish">Cold Dishes</option>
            <option value="drink">Drinks</option>
            <option value="dessert">Desserts</option>
        </select>
      <div class="mb-3">
        <div v-for="n in products.length">
          <img :src="productPhotoFullUrl(products[n-1])" class="rounded-circle img_photo" />
          <span class="item"> {{ products[n-1].name }}</span>
          <button type="button" class="bi bi-plus" id="add" @click="addProduct(products[n-1]);countProduct(products[n-1])"></button>
          <button type="button" class="bi bi-dash" id="add" @click="deleteProductInAdd(products[n-1]);countProduct(products[n-1])"></button>
          <p>Count is: {{ countProduct(products[n-1]) }}</p>

        </div>
      </div>
      <Bootstrap5Pagination :data="paginationNewOrder" @pagination-change-page="getProducts" :limit="5"></Bootstrap5Pagination>
 
      </div>
    </div>
  </div>

    <div class="mb-3 d-flex justify-content-end">
      <button type="button" id="button" class="btn btn-primary px-5" @click="save" v-if="$route.name == 'NewOrder'">
        Add Order
      </button>
      <button type="button" id="button" class="btn btn-primary px-5" @click="save" v-if="$route.name == 'Order'">
        Save Order
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
.child {
  display: inline;
  border: 1px solid red;
  padding: 1rem 1rem;
  vertical-align: middle;
  border-radius: 25px;
  margin:10px;

}
.item {
  width: max-content;
  height: 30px;
  font-size: 20px;
  padding-left: 10px;
  padding-right: 20px;
}

.form-label {
  font-size: 20px;
}
#add{
  margin: auto;
  float: right;
}
.img_photo {
  width: 3.2rem;
  height: 3.2rem;
}
</style>
