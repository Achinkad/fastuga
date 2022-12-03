<script setup>
import { ref, computed, onMounted, inject,watch } from 'vue'
import {useRouter} from 'vue-router'
import ProductTable from "./ProductTable.vue"
import { Bootstrap5Pagination } from 'laravel-vue-pagination';

const router = useRouter()
const serverBaseUrl = inject("serverBaseUrl")

const axios = inject('axios')

const products = ref([])
const pagination = ref({})


//variável usada no filtro
var value_type=ref("-1");

const totalProducts = computed(() => {
    return products.value.length
})

const loadProducts = (page = 1) => {
    axios.get(serverBaseUrl +'/api/products?page='+page,{
        params:{
            type: value_type.value
        }

    })
    .then((response) => {
        products.value = response.data.data
        pagination.value=response.data
    })
    .catch((error) => {
        console.log(error)
    })
}

//WATCH PARA ESTAR SEMPRE A VER O VALOR DE VALUE_TYPE(valor do filtro)
watch(value_type,() =>{
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


onMounted (() => {
    loadProducts()
})
</script>

<template>
    <h3 class="mt-5 mb-3">Products</h3>
    <hr>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectType" class="form-label">Filter by type:</label>
        <select class="form-select" id="selectType" v-model="value_type">
            <option value="all" selected>Any</option>
            <option value="hot dish">Hot Dishes</option>
            <option value="cold dish">Cold Dishes</option>
            <option value="drink">Drinks</option>
            <option value="dessert">Desserts</option>
        </select>
        <div class="mx-0 mt-2">
            <button type="button" class="btn btn-warning px-4 btn-addtask" @click="addProduct">
                <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Product</button>
            </div>
        </div>
        <hr>
        <product-table
        :products="products"
        :showId="true"
        @edit="editProduct"
        @forceRerender="forceRerender"
        @deleted="deletedProduct"
        ></product-table>

        <Bootstrap5Pagination :data="pagination" @pagination-change-page="loadProducts" :limit="5"></Bootstrap5Pagination>
        <hr>
    </template>

    <style scoped>
    .filter-div {
        min-width: 12rem;
    }
    .total-filtro {
        margin-top: 2.3rem;
    }
    </style>
