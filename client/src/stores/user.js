import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import avatarNoneUrl from '@/assets/avatar-none.png'
import { useRouter } from 'vue-router'
const router = useRouter()
export const useUserStore = defineStore('user', () => {
    const toast = inject("toast")
    const axios = inject('axios')
    const serverBaseUrl = inject('serverBaseUrl')
    const errors = ref(null)


    const user = ref(null)

    const userPhotoUrl = computed(() => {
        if (!user.value?.photo_url) {
            return avatarNoneUrl
        }
        return serverBaseUrl + '/storage/fotos/' + user.value.photo_url
    })

    const userId = computed(() => {
        return user.value?.id ?? -1
    })

    /*const userName = computed(() => {
        return user.value?.name ?? -1
    })

    const userType = computed(() => {
        return user.value?.type ?? -1
    })*/

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
  

    return { user, userId, userPhotoUrl, login, logout, restoreToken, save}
})
