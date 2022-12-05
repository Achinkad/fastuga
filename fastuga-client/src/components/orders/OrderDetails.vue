<script setup>
import { onMounted, ref, watch, computed, inject } from "vue";
import avatarNoneUrl from "@/assets/avatar-none.png";
import chunk from "chunk";
import productNoneUrl from "@/assets/product-none.png";
const serverBaseUrl = inject("serverBaseUrl");
const axios = inject("axios");
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
    product_id: 0,
    product: null,
  };
};

const emit = defineEmits(["save", "cancel"]);
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

const getProducts = () => {
  axios
    .get(serverBaseUrl + "/api/products", {
      params: {
        type: value_type.value,
      },
    })
    .then((response) => {
      products.value = response.data.data;

    })
    .catch((error) => {
      console.log(error);
    });
};
const chunkedProducts = computed(() => {

  return chunk(products.value, 3);
});


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
const deleteProduct = (product) => {

  if (editingOrder.value.order_item.length > 0) {
    editingOrder.value.order_item.forEach((value, index) => {
      if (value.product.id === product.id) {
        editingOrder.value.order_item.pop();
      }
    });
  }
  console.log("No items to delete");
  console.log(editingOrder.value);
};

const totalPrice = () => {
  var total = 0;
  editingOrder.value.order_item.forEach((value) => {
    console.log(value.price)
    total += parseFloat(value.price)
  });
  console.log(total)
  return total.toFixed(2);
};
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

const count = ref(0);
onMounted(() => {
  getProducts();

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

    <div class="mb-3">
      <label for="inputNotes" class="form-label">Notes</label>
      <textarea class="form-control" id="inputNotes" rows="4" v-model="editingOrder.notes"></textarea>
      <field-error-message :errors="errors" fieldName="notes"></field-error-message>
    </div>

    <div class="mb-3" v-if="editingOrder.customer_id != null">
      <label for="inputCustomer" class="form-label">Customer Name: </label>
      <br />
      <img :src="userPhotoFullUrl(editingOrder.customer.user)" class="rounded-circle img_photo" />
      <span>{{ editingOrder.customer.user.name }}</span>
    </div>
    <span> Total Price: {{totalPrice()}} € </span>
    <div class="mb-3" v-if="editingOrder.delivered_by != null">
      <label for="inputDeliveredBy" class="form-label">Delivered By: </label>
      <br />
      <img :src="userPhotoFullUrl(editingOrder.user)" class="rounded-circle img_photo" />
      <span>{{ editingOrder.user.name }}</span>
    </div>
    <div id="box">
      <label for="inputDeliveredBy" class="form-label">Products: </label>
      <select class="form-select" id="selectType" v-model="value_type">
        <option value="all" selected>Any</option>
        <option value="hot dish">Hot Dishes</option>
        <option value="cold dish">Cold Dishes</option>
        <option value="drink">Drinks</option>
        <option value="dessert">Desserts</option>
      </select>
      <div class="mb-3" v-if="$route.name == 'Order'">
        <br />

        <div v-for="n in editingOrder.order_item.length">
          <br />
          <img :src="productPhotoFullUrl(editingOrder.order_item[n - 1].product)" class="rounded-circle img_photo" />
          <span class="item">{{
              editingOrder.order_item[n - 1].product.name
          }}</span>
          <button type="button" class="bi bi-plus" @click="addProduct(editingOrder.order_item[n - 1].product); count++;
          "></button>
          <button type="button" class="bi bi-dash"
            @click="deleteProduct(editingOrder.order_item[n - 1].product); count--; "></button>
          <p>Count is: {{ count }}</p>
        </div>
        <br>
        <div v-for="products in chunkedProducts" class="row">
          <div v-for="product in products" class="col-lg-4">
            <img :src="productPhotoFullUrl(product)" class="rounded-circle img_photo" />
            <span class="item">{{ product.name }}</span>
            <button type="button" class="bi bi-plus bg-none" @click="addProduct(product); count++;"></button>
            <button type="button" class="bi bi-dash" @click="deleteProduct(product); count--; "></button>
          </div>
        </div>
      </div>
      <!-- CONDIÇOES AINDA NAO FUNCIONAIS-->
      <!-- CONDIÇOES AINDA NAO FUNCIONAIS-->
      <!-- CONDIÇOES AINDA NAO FUNCIONAIS-->
      <div v-if="$route.name == 'NewOrder'">
        <div v-for="products in chunkedProducts" class="row">
          <div v-for="product in products" class="col-lg-4">
            <img :src="productPhotoFullUrl(product)" class="rounded-circle img_photo" />
            <span class="item">{{ product.name }}</span>
            <button type="button" class="bi bi-plus bg-none" @click="addProduct(product); count++;"></button>
            <button type="button" class="bi bi-dash" @click="deleteProduct(product); count--; "></button>
          </div>
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

#box {
  background-color: rgb(255, 255, 255);
  width: 100%;
  border: 1px solid orange;

  border-radius: 25px;
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

.img_photo {
  width: 3.2rem;
  height: 3.2rem;
}
</style>
