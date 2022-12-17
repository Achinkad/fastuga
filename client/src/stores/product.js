import { ref, inject, computed } from 'vue'
import { defineStore } from 'pinia'

export const useProductStore = defineStore('products', () => {
    const socket = inject("socket")
    const axios = inject('axios')
    const toast = inject("toast")

    const products = ref([])
    const best_products = ref([])

    async function load_best_products() {
        try {
            const response = await axios({
                method: 'GET',
                url: 'products/best-selling'
            })
            best_products.value = response.data.data
            return best_products.value
        } catch (error) {
            clear_orders()
            throw error
        }
    }

    return {
        load_best_products,
        best_products
    }
})
