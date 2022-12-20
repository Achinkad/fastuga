import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'

import avatarNoneUrl from '@/assets/avatar-none.png'

const router = useRouter()

export const useUserStore = defineStore('user', () => {
    const toast = inject("toast")
    const axios = inject('axios')
    const serverBaseUrl = inject('serverBaseUrl')

    const errors = ref(null)
    const number_customers_this_month = ref([])
    const user = ref(null)
    const customer = ref(null)

    const userPhotoUrl = computed(() => {
        if (!user.value?.photo_url) {
            return avatarNoneUrl
        }
        return serverBaseUrl + '/storage/fotos/' + user.value.photo_url
    })

    const userId = computed(() => {
        return user.value?.id ?? -1
    })



    async function loadUser () {
        try {
            const response = await axios.get('user')
            user.value = response.data.data
        } catch (error) {
            clearUser ()
            throw error
        }
    }

    function clearUser () {
        delete axios.defaults.headers.common.Authorization
        sessionStorage.removeItem('token')
        user.value = null
    }

    async function login (credentials) {
        try {
            const response = await axios.post('login', credentials)
            axios.defaults.headers.common.Authorization = "Bearer " + response.data.access_token
            sessionStorage.setItem('token', response.data.access_token)
            await loadUser()
            if (user.value.type == "C") { await get_customer(user.value) }
            return true
        }
        catch(error) {
            clearUser()
            return false
        }
    }

    async function logout() {
        try {
            await axios.post('logout')
            clearUser()
            return true
        } catch (error) {
            return false
        }
    }

    async function restoreToken () {
        let storedToken = sessionStorage.getItem('token')
        if (storedToken) {
            axios.defaults.headers.common.Authorization = "Bearer " + storedToken
            await loadUser()
            return true
        }
        clearUser()
        return false
    }

    function save (user_values, user_id) {
        axios.put(serverBaseUrl+'/api/users/' + user_id, user_values)
        .then((response) => {
            //user.value = response.data.data
            //originalValueStr = dataAsString()
            toast.success('User #' + user_id + ' was updated successfully.')

        })
        .catch((error) => {
            console.log(error)
            if (error.response.status == 422) {
                toast.error('User #' + user_id + ' was not updated due to validation errors!')
                errors.value = error.response.data.data
            } else {
                toast.error('User #' + user_id + ' was not updated due to unknown server error!')
            }
        })
    }

    async function get_customer(user) {
        try {
            const response = await axios.get('customers/users/' + user.id)
            customer.value = response.data.data
            return response.data.data
        } catch (error) {
            throw error
        }
    }

    async function loadCustomersCreatedThisMonth() {

        try {
            const response = await axios({
                method: 'GET',
                url: 'customers/numbers'
            })

            number_customers_this_month.value = response.data

            return number_customers_this_month.value
        } catch (error) {
            throw error
        }
    }

    const get_customers_this_month = (() => { return number_customers_this_month.value })



    return {
        user,
        userId,
        customer,
        get_customer,
        userPhotoUrl,
        login,
        logout,
        restoreToken,
        save,
        get_customers_this_month,
        loadCustomersCreatedThisMonth
    }
})
