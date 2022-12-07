<script setup>
  import { ref, watch, inject, computed} from 'vue'
  import UserDetail from "./UserDetail.vue"
  import { useRouter, onBeforeRouteLeave } from 'vue-router'


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

  const operation = computed(() => (!props.id || props.id < 0) ? 'insert' : 'update')

  const newUser = () => {
      return {
        id: null,
        name: '',
        email: '',
        type: 'C',
        photo_url: null,
        blocked: 0,
        password :'',
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

  const save = () => {
      errors.value = null
      if (operation.value == 'insert') {
        axios.post(serverBaseUrl + '/api/users', user.value)
        .then((response) => {
        user.value = response.data.data
        originalValueStr = dataAsString()
        toast.success('user #' + user.value.id + ' was created successfully.')
        router.back()
      })
      .catch((error) => {
        if (error.response.status == 422) {
          toast.error('order was not created due to validation errors!')
          errors.value = error.response.data.errors
        } else {
          toast.error('order was not created due to unknown server error!')
        }
      })
  } else{
      axios.put(serverBaseUrl+'/api/users/' + props.id, user.value)
        .then((response) => {
          user.value = response.data.data
          originalValueStr = dataAsString()
          toast.success('User #' + user.value.id + ' was updated successfully.')
          router.back()
        })
        .catch((error) => {
          if (error.response.status == 422) {
              toast.error('User #' + props.id + ' was not updated due to validation errors!')
              errors.value = error.response.data.errors
            } else {
              toast.error('User #' + props.id + ' was not updated due to unknown server error!')
            }
        })
      }
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

  const user = ref(newUser())
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
    :operationType="insert"
    @save="save"
    @cancel="cancel"
  ></user-detail>
</template>
