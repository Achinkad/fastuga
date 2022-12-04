<script setup>
  import { ref, watch, computed, onMounted, inject } from 'vue'
  import { useRouter, onBeforeRouteLeave } from 'vue-router'  

  import OrderDetail from "./OrderDetails.vue"

 const serverBaseUrl = inject("serverBaseUrl")

  const router = useRouter()  
  const axios = inject('axios')
  const toast = inject('toast')

  const newOrder = () => {
      return {
        id: null,
        ticket_number: 1,  // Change it later
        status: "D",
        completed: false,
        total_price: 0,
        customer_id: null,
        delivered_by: null
      }
  }

  let originalValueStr = ''
  const loadOrder = (id) => {
      originalValueStr = ''
      errors.value = null
      if (!id || (id < 0)) {
        order.value = newOrder()
        originalValueStr = dataAsString()
      } else {
        axios.get(serverBaseUrl+'/api/orders/' + id)
          .then((response) => {
            order.value = response.data.data
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
        axios.post(serverBaseUrl +'/api/order', order.value)
          .then((response) => {
            order.value = response.data.data
            originalValueStr = dataAsString()
            toast.success('order #' + order.value.id + ' was created successfully.')
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
      } else {
        axios.put(serverBaseUrl +'/api/order/' + props.id, order.value)
          .then((response) => {
            order.value = response.data.data
            originalValueStr = dataAsString()
            toast.success('order #' + order.value.id + ' was updated successfully.')
            router.back()
          })
          .catch((error) => {
            if (error.response.status == 422) {
              toast.error('order #' + props.id + ' was not updated due to validation errors!')
              errors.value = error.response.data.errors
            } else {
              toast.error('order #' + props.id + ' was not updated due to unknown server error!')
            }
          })
      }
    }
    
  const cancel = () => {
    originalValueStr = dataAsString()
    router.back()
  }

  const dataAsString = () => {
    return JSON.stringify(order.value)
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

  const props = defineProps({
    id: {
      type: Number,
      default: null
    },
 
  })

  const order = ref(newOrder())
  const errors = ref(null)
  const confirmationLeaveDialog = ref(null)

  const operation = computed( () => (!props.id || props.id < 0) ? 'insert' : 'update')
  
    // beforeRouteUpdate was not fired correctly
    // Used this watcher instead to update the ID
  watch(
    () => props.id,
    (newValue) => {
        loadOrder(newValue)
      }, 
    { immediate: true}
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
  <order-detail
    :operationType="operation"
    :order="order"
    :errors="errors"
    @save="save"
    @cancel="cancel"
  ></order-detail>
</template>