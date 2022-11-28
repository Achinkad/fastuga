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

  const editProduct = (product) => {
    router.push({ name: 'Product', params: { id: product.id } })
  }

  onMounted (() => {
    loadProducts()
  })
</script>

<template>
  <h3 class="mt-5 mb-3">Products</h3>
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

