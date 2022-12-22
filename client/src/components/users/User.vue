<script setup>
import { ref, watch, inject} from 'vue'
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import UserDetail from "./UserDetail.vue"

const router = useRouter()
const axios = inject('axios')
const toast = inject('toast')
const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
    id: {
        type: Number,
        default: null
    }
})

const newUser = () => {
    return {
        name: '',
        email: '',
        type: '',
        photo_url: null,
        blocked: 0,
        password :'',
        custom : ''
    }
}

let originalValueStr = ''
const loadUser = (id) => {
    originalValueStr = ''
    errors.value = null
    if (!id || (id < 0)) {
        user.value = newUser()
        originalValueStr = dataAsString()
    } else {
        axios.get(serverBaseUrl+'/api/users/' + id)
        .then((response) => {
            user.value = response.data.data
            originalValueStr = dataAsString()
        })
        .catch((error) => {
            console.log(error)
        })
    }
}
const user = ref(newUser())

const add = (user_values) => {
    axios.post(serverBaseUrl + '/api/users', user_values)
    .then((response) => {
        user.value = response.data.data
        originalValueStr = dataAsString()
        toast.success(user.value.name + ' (#' + user.value.id + ') was created successfully!')
        router.back()
    })
    .catch((error) => {
        if (error.response.status == 422) {
            toast.error('You messed up. Check the form again!')
            errors.value = error.response.data.data
        } else {
            toast.error('Oops, something went wrong on the server side.')
        }
    })
}

const cancel = () => {
    originalValueStr = dataAsString()
    router.back()
}

const dataAsString = () => {
    return JSON.stringify(user.value)
}

let nextCallBack = null
const leaveConfirmed = () => {
    if (nextCallBack) {
        nextCallBack()
    }
}

onBeforeRouteLeave((to, from, next) => {
    nextCallBack = null
    let newValueStr = dataAsString()
    if (originalValueStr != newValueStr) {
        nextCallBack = next
        confirmationLeaveDialog.value.show()
    } else {
        next()
    }
})

const errors = ref(null)
const confirmationLeaveDialog = ref(null)

watch(
    () => props.id,
    (newValue) => {
        loadUser(newValue)
    },
    {immediate: true}
)

</script>

<template>
    <confirmation-dialog
    ref="confirmationLeaveDialog"
    confirmationBtn="Discard changes and leave"
    msg="Do you really want to leave? You have unsaved changes!"
    @confirmed="leaveConfirmed"
    >
</confirmation-dialog>

<user-detail
:user="user"
:errors="errors"
@add="add"
@cancel="cancel"
></user-detail>
</template>
