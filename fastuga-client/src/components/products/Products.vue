<script setup>
  import { ref, computed, onMounted, inject } from 'vue'
  import {useRouter} from 'vue-router'
  import ProductTable from "./ProductTable.vue"
  
  const router = useRouter()

  const axios = inject('axios')

  const products = ref([])

  const totalProducts = computed(() => {
    return products.value.length
  })

  const loadProducts = () => {
    axios.get('products')
        .then((response) => {
          products.value = response.data.data
        })
        .catch((error) => {
          console.log(error)
        })
    }

const addProduct = () => {
  router.push({ name: 'newProduct' })
}
const editProduct = (product) => {
  router.push({ name: 'Product', params: { id: product.id } })
}
const deletedOrder = (deletedProduct) => {
  let idx = orders.value.findIndex((t) => t.id === deletedProduct.id)
  if (idx >= 0) {
    orders.value.splice(idx, 1)
  }
}
  onMounted (() => {
    loadProducts()
  })
</script>

<template>
  <h3 class="mt-5 mb-3">Products</h3>
  <div class="mx-0 mt-2">
        <button type="button" class="btn btn-warning px-4 btn-addtask" @click="addProduct"><i
            class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Product</button>
      </div>
  <hr>
  <product-table
    :products="products"
    :showId="false"
    @edit="editProduct"
  ></product-table>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}
.total-filtro {
  margin-top: 2.3rem;
}
</style>

