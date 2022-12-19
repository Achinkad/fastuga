<script setup>
import { ref, computed, onBeforeMount, inject, watch } from 'vue'
import { useProductStore } from '../../stores/product.js'

const productStore = useProductStore()

const axios = inject('axios')
const serverBaseUrl = inject("serverBaseUrl")

const loadProducts = (type) => { productStore.load_products(1, type) }
const products = computed(() => { return productStore.products })
const pagination = computed(() => { return productStore.pagination })

const photoFullUrl = (product) => { return serverBaseUrl + "/storage/products/" + product.photo_url }

const active_el = ref("all")
const active_page = ref(null)

const selectType = (type) => {
    active_el.value = type
    loadProducts(type)
    active_page.value = pagination.value
}

const loadMoreProducts = async (page_url, type) => {
    await axios.get(page_url, { params: { type: type } })
    .then((response) => {
        response.data.data.forEach(i => {
            products.value.push(i)
        })
        active_page.value = response.data
    })
    .catch((error) => {
        console.log(error)
    })
}

const truncate = (str, n) => {
    return (str.length > n) ? str.slice(0, n-1) + '...' : str;
}

onBeforeMount(() => {
    loadProducts(active_el.value)
    active_page.value = pagination.value
})
</script>

<template>
    <div class="container-fluid" v-if="active_page != null">
        <div class="row">
            <div class="col-12">
                <div class="d-flex p-title-box justify-content-center align-items-center">
                    <h4 class="p-title me-auto">Our Menu, Just for You</h4>
                    <div class="p-title-right">
                        <button type="button" name="button" class="btn btn-menu ms-3"
                            :class="{ active : active_el == 'all' }"
                            @click="selectType('all')">
                            All Products
                        </button>
                        <button type="button" name="button" class="btn btn-menu ms-3"
                            :class="{ active : active_el == 'hot dish' }"
                            @click="selectType('hot dish')">
                            Hot Dishes
                        </button>
                        <button type="button" name="button" class="btn btn-menu ms-3"
                            :class="{ active : active_el == 'cold dish' }"
                            @click="selectType('cold dish')">
                            Cold Dishes
                        </button>
                        <button type="button" name="button" class="btn btn-menu ms-3"
                            :class="{ active : active_el == 'dessert' }"
                            @click="selectType('dessert')">
                            Dessert
                        </button>
                        <button type="button" name="button" class="btn btn-menu ms-3"
                            :class="{ active : active_el == 'drink' }"
                            @click="selectType('drink')">
                            Drinks
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-6" v-for="product in products" :key="product.id">
                <div class="card">
                    <div class="card-body">
                        <div class="row d-flex align-items-center">
                            <div class="col-2 image">
                                <img :src="photoFullUrl(product)" class="product-photo"/>
                            </div>
                            <div class="col-10">
                                <div class="row">
                                    <div class="col-12">
                                        <span class="fw-bold product-name">{{product.name}}</span>
                                        <span class="ms-3">
                                            <i>
                                                {{product.price}}€
                                            </i>
                                        </span><br>
                                    </div>
                                    <div class="col" :title="product.description">
                                        <span class="product-description">{{truncate(product.description, 160)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-center mt-3">
            <button type="button" name="button" class="btn btn-secondary" v-if="active_page.links.next" @click="loadMoreProducts(active_page.links.next, active_el)">
                <span><b>Load More Products</b></span>
            </button>
            <button type="button" name="button" class="btn btn-secondary disabled" v-if="!active_page.links.next">
                <span><b>Load More Products</b></span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.btn-menu {
    color: #212529;
    background-color: transparent;
    border-color: #1f2937;
    font-size: 14px;
    padding: .5rem 1rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.15),0 1px 1px rgba(17,24,39,.075);
}

.btn-menu.active,
.btn-menu:hover,
.btn-menu:active {
    color: #fff !important;
    background-color: #1f2937 !important;
    border-color: #1f2937 !important;
}

.image {
    position: relative;
    left: 5px;
}

.product-name {
    font-size: 18px;
}

.product-description {
    font-size: 15px;
}

.product-photo {
    width: 5rem;
    height: 5rem;
    border-radius: 50%;
}

.btn-secondary {
    color: #1f2937;
    font-weight: 500;
    background-color: #f0bc74;
    border-color: #f0bc74;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.15),0 1px 1px rgba(17,24,39,.075);
    padding: .7rem 1.5rem;
}

.btn-secondary:hover {
    background-color: #f2c689;
    border-color: #eeb15d;
    color: #1f2937;
}

.btn-secondary:active {
    color: #1f2937 !important;
    background-color: #f0bc74 !important;
    border-color: #f0bc74 !important;
}
</style>
