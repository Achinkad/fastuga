<script setup>
import { ref, watch, computed } from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'
import productNoneUrl from '@/assets/product-none.png'

const serverBaseUrl = inject("serverBaseUrl")

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
  fixedProject: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(["save", "cancel"]);

const editingOrder = ref(props.order);

watch(
  () => props.order,
  (newOrder) => {
    editingOrder.value = newOrder;
  }
);


const orderTittle = computed(() => {
  if (!editingOrder.value) {
    return "";
  }
  return props.operationType == "insert" ? "New Order" : "Order #" + editingOrder.value.id;
});

const save = () => {
  emit("save", editingOrder.value);
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
</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
    <h3 class="mt-5 mb-3">{{ orderTittle }}</h3>
    <hr />

    <div class="mb-3">
        <label for="inputTickerNumber">Ticket Number</label>
        <input type="number" id="inputTicketNumber" name="ticket_number" min="1" max="99" v-model="editingOrder.ticket_number">
        <field-error-message :errors="errors" fieldName="ticket_number"></field-error-message>
    </div>
    <div class="mb-3">
            <label for="status">Status</label>
            <select id="status" name="status"  v-model="editingOrder.status">
              <option value="P">Preparing</option>
              <option value="R">Ready</option>
              <option value="D">Delivered</option>
              <option value="C">Cancelled</option>
            </select>
          <field-error-message :errors="errors" fieldName="status"></field-error-message>
    </div>
    
    <div class="mb-3">
          <label for="inputTotalPrice" class="form-label">Total Price</label>
          <input
            type="text"
            class="form-control"
            id="inputTotalPrice"
            placeholder="Total Price of Order"
            required
            v-model="editingOrder.total_price"
          />
          <field-error-message :errors="errors" fieldName="total_price"></field-error-message>
        </div>
        <div class="mb-3">
          <label for="inputTotalPaid" class="form-label">Total Paid</label>
          <input
            type="text"
            class="form-control"
            id="inputTotalPaid"
            placeholder="Total Price Paid"
            required
            v-model="editingOrder.total_paid"
          />
          <field-error-message :errors="errors" fieldName="total_paid"></field-error-message>
        </div>

         <div class="mb-3">
            <label for="payment_type">Payment Type</label>
            <select id="payment_type" name="payment_type"  v-model="editingOrder.payment_type">
              <option value="VISA">Visa</option>
              <option value="PAYPAL">PayPal</option>
              <option value="MBWAY">MBWay</option>
            </select>
          <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
        </div>

        <div class="mb-3">
          <label for="inputPaymentReference" class="form-label">Payment Reference</label>
          <input
            type="text"
            class="form-control"
            id="inputPaymentReference"
            placeholder="Payment Reference"
            required
            v-model="editingOrder.payment_reference"
          />
          <field-error-message :errors="errors" fieldName="payment_reference"></field-error-message>
        </div>

        <div class="mb-3">
          <label for="date">Date</label>
          <input type="date" id="date" name="date" v-model="editingOrder.date">
          <field-error-message :errors="errors" fieldName="date"></field-error-message>
        </div>
        
    <div class="mb-3">
      <label for="inputNotes" class="form-label">Notes</label>
      <textarea
        class="form-control"
        id="inputNotes"
        rows="4"
        v-model="editingOrder.notes"
      ></textarea>
      <field-error-message :errors="errors" fieldName="notes"></field-error-message>
    </div>

    <div class="mb-3" v-if="editingOrder.customer_id!=null">
          
          <label for="inputCustomer" class="form-label" >Customer Name: </label>
          <br>
          <img :src="userPhotoFullUrl(editingOrder.customer.user)" class="rounded-circle img_photo" />
          <span>{{editingOrder.customer.user.name}}</span>
         
         
    </div>

    <div class="mb-3" v-if="editingOrder.delivered_by!=null">
          
          <label for="inputDeliveredBy" class="form-label" >Delivered By: </label>
          <br>
           <img :src="userPhotoFullUrl(editingOrder.user)" class="rounded-circle img_photo" />
          <span>{{editingOrder.user.name}}</span>
          
         
         
    </div>

    <div class="mb-3" v-if="editingOrder.order_item!=null">
          
          <label for="inputDeliveredBy" class="form-label" >Products: </label>
          <br>
          <div v-for="n in editingOrder.order_item.length">
          <img :src="productPhotoFullUrl(editingOrder.order_item[n-1].product)" class="rounded-circle img_photo" />
            <span>{{editingOrder.order_item[n-1].product.name}}</span>
          
          </div>
    </div>


    <div class="mb-3 d-flex justify-content-end">
      <button type="button" class="btn btn-primary px-5" @click="save">Save</button>
      <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
    </div>
  </form>
</template>

<style scoped>

.checkCompleted {
  min-height: 2.375rem;
}
.img_photo {
  width: 3.2rem;
  height: 3.2rem;
}
</style>
