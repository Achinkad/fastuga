<script setup>
import { ref, watch, watchEffect, computed, inject } from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'




const axios = inject("axios");
const toast = inject('toast')


const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
  showId: {
    type: Boolean,
    default: true,
  },

  showPhoto: {
    type: Boolean,
    default: true,
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["edit", "deleted", "forceRerender"]);
const productToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const productToDeleteDescription = computed(() => {
  return productToDelete.value
    ? `#${productToDelete.value.id} (${productToDelete.value.name})`
    : ""
})

watch(
  () => props.orders,
  (newOrders) => {
    editingOrders.value = newOrders
  }
)

const photoFullUrl = (product) => {
  return product.photo_url
    ? serverBaseUrl + "/storage/products/" + product.photo_url
    : avatarNoneUrl;
};
const dialogConfirmedDelete = () => {
  axios
    .delete(serverBaseUrl + "/api/products/" + productToDelete.value.id)
    .then((response) => {
      emit("deleted", response.data.data)
      toast.info("Product " + productToDeleteDescription.value + " was deleted")
      emit("forceRerender", response.data.data)
    })
    .catch((error) => {
      console.log(error)
    })

}
const editClick = (product) => {
  emit("edit", product);
};
const deleteClick = (product) => {
  productToDelete.value = product

  deleteConfirmationDialog.value.show()
}

</script>

<template>

  <confirmation-dialog ref="deleteConfirmationDialog" confirmationBtn="Delete task"
    :msg="`Do you really want to delete the product ${productToDeleteDescription}?`" @confirmed="dialogConfirmedDelete">
  </confirmation-dialog>
  <table class="table">
    <thead>
      <tr>
        <th v-if="showId">ID</th>
        <th v-if="showPhoto">Photo</th>
        <th class="align-middle">Name</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="product in products" :key="product.id">
        <td v-if="showId" class="align-middle">{{ product.id }}</td>
        <td v-if="showPhoto" class="align-middle">
          <img :src="photoFullUrl(product)" class="rounded-circle img_photo" />
        </td>
        <td class="align-middle">{{ product.name }}</td>
        <td class="align-middle">{{ product.type }}</td>
        <td class="align-middle">{{ product.price }}€</td>
        <div class="d-flex justify-content-end">
          <button class="btn btn-xs btn-light" @click="deleteClick(product)" v-if="showDeleteButton">
            <i class="bi bi-trash3"></i>
          </button>
          <button class="btn btn-xs btn-light" @click="editClick(product)" v-if="showEditButton">
            <i class="bi bi-pencil"></i>
          </button>
        </div>
      </tr>
    </tbody>
  </table>

</template>

<style scoped>
button {
  margin-left: 3px;
  margin-right: 3px;
}

.img_photo {
  width: 3.2rem;
  height: 3.2rem;
}
</style>
