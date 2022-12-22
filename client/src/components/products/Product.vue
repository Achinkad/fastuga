<script setup>
import { ref, watch, inject } from 'vue'
import ProductDetail from "./ProductDetail.vue"
import { useRouter } from 'vue-router'
import axios from 'axios'


const router = useRouter()
const toast = inject('toast')
const serverBaseUrl = inject("serverBaseUrl")
axios.defaults.headers.common.Authorization = "Bearer " + sessionStorage.token
const product = ref(newProduct())
const errors = ref(null)

const props = defineProps({
    id: {
        type: Number,
        default: null
    }
})

const newProduct = () => {
    return {
        id: null,
        name: '',
        price: '',
        photo_url: null,
        description: ''
    }
}

let originalValueStr = ''
const loadProduct = (id) => {
    originalValueStr = ''
    errors.value = null
    if (!id || (id < 0)) {
        product.value = newProduct()
        originalValueStr = dataAsString()

    } else {
        axios.get(serverBaseUrl + '/api/products/' + id)
            .then((response) => {
                product.value = response.data.data
                originalValueStr = dataAsString()
            })
            .catch((error) => {
                console.log(error)
            })
    }
}

const save = (product_values) => {
    axios.post(serverBaseUrl + '/api/products/' + props.id, product_values)
        .then((response) => {
            toast.success("Product updated successfuly!")
            router.push({ name: 'Products' })
        })
        .catch((error) => {
            if (error.response.status == 422) {
                toast.error('Product #' + props.id + ' was not updated due to validation errors!')
                errors.value = error.response.data.data
            } else {
                toast.error('Product #' + props.id + ' was not updated due to unknown server error!')
            }
        })
}

const add = (product_values) => {
    axios.post(serverBaseUrl + '/api/products', product_values)
        .then((response) => {
            toast.success("Product added successfuly")
            router.push({ name: 'Products' })
        })
        .catch((error) => {
            if (error.response.status == 422) {

                toast.error('Couldn\'t add the product due to validation errors!')
                errors.value = error.response.data.data
                // console.log(errors.value)
            } else {
                toast.error('Couldn\'t add the product due to unknown server error!')
            }
        })
}

const cancel = () => {
    originalValueStr = dataAsString()
    router.back()
}

const dataAsString = () => {
    return JSON.stringify(product.value)
}







watch(
    () => props.id,
    (newValue) => {
        loadProduct(newValue)
    },
    { immediate: true }
)


</script>

<template>
    <product-detail :product="product" :errors="errors" @save="save" @cancel="cancel" @add="add"></product-detail>
</template>
