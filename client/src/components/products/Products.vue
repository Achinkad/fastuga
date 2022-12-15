<script setup>
import { ref, computed, onMounted, inject, watch } from 'vue'
import { useRouter } from 'vue-router'
import ProductTable from "./ProductTable.vue"
import { Bootstrap5Pagination } from 'laravel-vue-pagination';

const router = useRouter()
const serverBaseUrl = inject("serverBaseUrl")

const axios = inject('axios')

const products = ref([])
const pagination = ref({})


//variável usada no filtro
var value_type = ref("all");



const loadProducts = (page = 1) => {
    axios.get(serverBaseUrl + '/api/products?page=' + page, {

        params: {
            type: value_type.value
        }

    })
        .then((response) => {
            products.value = response.data.data
            pagination.value = response.data
        })
        .catch((error) => {
            console.log(error)
        })
}

//WATCH PARA ESTAR SEMPRE A VER O VALOR DE VALUE_TYPE(valor do filtro)
watch(value_type, () => {
    console.log(value_type.value)
    loadProducts()
})

const addProduct = () => {
    router.push({ name: 'newProduct' })
}
const editProduct = (product) => {
    router.push({ name: 'Product', params: { id: product.id } })
}
const deletedProduct = (deletedProduct) => {
    //VER MAIS TARDE
    /*
    let idx = orders.value.findIndex((t) => t.id === deletedProduct.id)
    if (idx >= 0) {
    orders.value.splice(idx, 1)
}
*/
}

const forceRerender = () => {
    loadProducts()
    console.log("reload")
}


onMounted(() => {
    loadProducts()
})
</script>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex">
                    <div class="p-title-box">

                        <div class="p-title-right">

                    <h4 class="p-title">Products</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-xl-10">
        <div class="d-flex">
        <div class="col-3">
        <label for="selectType" class="form-label">Filter by type:</label>
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


<div class="col-xl-2">

    <div class="ms-auto align-self-center">
                    <div class="mx-0 mt-2" >
                          <button type="button" class="btn btn-warning px-5 btn-add" @click="addProduct">
                              <i class="bi bi-xs bi-plus-circle"></i>&nbsp;Add Product
                          </button>
                    </div>
                </div>
            </div>

   
    <product-table :products="products" :showId="true" @edit="editProduct" @forceRerender="forceRerender"
        @deleted="deletedProduct"></product-table>


    <div class="d-flex justify-content-end mt-3">
        <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadProducts" :limit="5"></Bootstrap5Pagination>
    </div>
    </div>
</div> 
            </div> 
    </div>
    </div>
    </div>
    
</template>

<style scoped>
.filter-div {
    min-width: 12rem;
}

.total-filtro {
    margin-top: 2.3rem;
}
</style>
