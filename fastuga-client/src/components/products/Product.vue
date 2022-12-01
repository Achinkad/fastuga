<script setup>
  import { ref, watch, inject } from 'vue'
  import ProductDetail from "./ProductDetail.vue"
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

  const newProduct = () => {
      return {
        id: null,
        name: '',
        photo_url: null
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
        axios.get(serverBaseUrl+'/products/' + id)
          .then((response) => {
            product.value = response.data.data
            originalValueStr = dataAsString()
          })
          .catch((error) => {
            console.log(error)
          })
      }
  }

  const save = () => {
      errors.value = null
      axios.put(serverBaseUrl+'/products/' + props.id, product.value)
        .then((response) => {
          product.value = response.data.data
          originalValueStr = dataAsString()
          toast.success('Product #' + product.value.id + ' was updated successfully.')
          router.back()
        })
        .catch((error) => {
          if (error.response.status == 422) {
              toast.error('Product #' + props.id + ' was not updated due to validation errors!')
              errors.value = error.response.data.errors
            } else {
              toast.error('Product #' + props.id + ' was not updated due to unknown server error!')
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

  const product = ref(newProduct())
  const errors = ref(null)
  const confirmationLeaveDialog = ref(null)

  watch(
    () => props.id,
    (newValue) => {
        loadProduct(newValue)
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

  <product-detail
    :product="product"
    :errors="errors"
    @save="save"
    @cancel="cancel"
  ></product-detail>
</template>
