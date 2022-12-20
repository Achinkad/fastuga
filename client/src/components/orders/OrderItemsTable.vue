<script setup>
import { inject } from "vue"

const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
  order_items: {
    type: Array,
    default: () => [],
  },

})

const photoFullUrl = (product) => {
  return product.photo_url
    ? serverBaseUrl + "/storage/products/" + product.photo_url
    : avatarNoneUrl;
}
</script>

<template>

  <div class="table-responsive">
    <table class="table align-middle mt-4">
        <thead class="table-light">
      <tr>
        <th >Product Photo</th>
        <th >Product Name</th>
        <th >Status</th>
        <th >Notes</th>
        <th>Date</th>
        <th >Order_id</th>

      </tr>
    </thead>
    <tbody>
      <tr v-for="order_item in order_items">
        <td  class="align-middle">
          <img alt="product" :src="photoFullUrl(order_item.product)" class="rounded-circle img_photo" />
        </td>
        <td>{{ order_item.product.name }}</td>
        <td>{{ order_item.status }}</td>
        <td>{{ order_item.notes }}</td>
       <td>{{ order_item.order.date }}</td>
        <td>{{ order_item.order_id }}</td>
      </tr>
    </tbody>
  </table>
</div>
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
