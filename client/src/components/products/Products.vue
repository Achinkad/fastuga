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

var value_type = ref("all")

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
                <div class="d-flex p-title-box">
                    <h4 class="p-title me-auto">Products List</h4>
                    <div class="p-title-right">
                        <h6 class="p-title">Viewing 0 of 0</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-xl-8">
                                <div class="d-flex">
                                    <div class="d-flex align-items-center">
                                        <label for="selectType" class="me-2">Type</label>
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

                            <div class="col-xl-4 d-flex justify-content-end align-items-end">
                                <button type="button" class="btn btn-add px-4 align-self-end" @click="addProduct">
                                    <i class="bi bi-xs bi-plus-circle me-2"></i> Add Product
                                </button>
                            </div>

                            <product-table
                                :products="products"
                                :showId="true"
                                @edit="editProduct"
                                @forceRerender="forceRerender"
                              
                            >
                            </product-table>

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
