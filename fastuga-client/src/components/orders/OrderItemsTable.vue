<script setup>
import { ref, watch, computed, inject } from "vue"

const axios = inject("axios")
const toast = inject("toast")

const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
  order_items: {
    type: Array,
    default: () => [],
  },
  showProductPhoto: {
    type: Boolean,
    default: true,
  },
  showProductName: {
    type: Boolean,
    default: true,
  },
  showStatus: {
    type: Boolean,
    default: true,
  },
  showOrderId: {
    type: Boolean,
    default: false,
  },
  showNotes: {
    type: Boolean,
    default: true,

  },
  showDate: {
    type: Boolean,
    default: true,
  }
 
})

const photoFullUrl = (product) => {
  return product.photo_url
    ? serverBaseUrl + "/storage/products/" + product.photo_url
    : avatarNoneUrl;
};

</script>

<template>
  
  <table class="table">
    <thead>
      <tr>
        <th v-if="showProductPhoto">Product Photo</th>
        <th v-if="showProductPhoto">Product Name</th>
        <th v-if="showStatus">Status</th>
        <th v-if="showNotes">Notes</th>
        <!-- <th v-if="showDate">Date</th>-->
        <th v-if="showOrderId">Order_id</th>

      </tr>
    </thead>
    <tbody>
      <tr v-for="order_item in order_items">
        <td v-if="showProductPhoto" class="align-middle">
          <img :src="photoFullUrl(order_item.product)" class="rounded-circle img_photo" />
        </td>
        <td v-if="showProductName">{{ order_item.product.name }}</td>
        <td v-if="showStatus">{{ order_item.status }}</td>
        <td v-if="showNotes">{{ order_item.notes }}</td>
       <!--<td v-if="showNotes">{{ order_item.order.date }}</td>-->
        <td v-if="showOrderId">{{ order_item.order_id }}</td>
      </tr>
    </tbody>
  </table>

</template>

<style scoped>
.completed {
  text-decoration: line-through;
}
.img_photo {
  width: 3.2rem;
  height: 3.2rem;
}
button {
  margin-left: 3px;
  margin-right: 3px;
}
</style>
