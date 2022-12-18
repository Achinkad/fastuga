<script setup>
import { ref, watch, computed, inject } from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'

const axios = inject("axios");
const toast = inject('toast')

const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    showId: {
        type: Boolean,
        default: true,
    },

    showPhoto: {
        type: Boolean,
        default: true,
    },
    showEditButton: {
        type: Boolean,
        default: true,
    },
    showDeleteButton: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["edit", "deleted", "forceRerender"]);

const productToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const productToDeleteDescription = computed(() => {
    return productToDelete.value
    ? `#${productToDelete.value.id} (${productToDelete.value.name})`
    : ""
})

watch(
    () => props.orders,
    (newOrders) => {
        editingOrders.value = newOrders
    }
)

const photoFullUrl = (product) => {
    return product.photo_url
    ? serverBaseUrl + "/storage/products/" + product.photo_url
    : avatarNoneUrl;
}

const dialogConfirmedDelete = () => {
    axios
    .delete(serverBaseUrl + "/api/products/" + productToDelete.value.id)
    .then((response) => {
        emit("deleted", response.data.data)
        toast.info("Product " + productToDeleteDescription.value + " was deleted")
        emit("forceRerender", response.data.data)
    })
    .catch((error) => {
        console.log(error)
    })
}

const editClick = (product) => {
    emit("edit", product);
}

const deleteClick = (product) => {
    productToDelete.value = product
    deleteConfirmationDialog.value.show()
}

const capitalize = (word) => {
    const capitalizedFirst = word[0].toUpperCase();
    const rest = word.slice(1);
    return capitalizedFirst + rest;
}
</script>

<template>
    <confirmation-dialog ref="deleteConfirmationDialog" confirmationBtn="Delete product"
    :msg="`Do you really want to delete the product ${productToDeleteDescription}?`" @confirmed="dialogConfirmedDelete">
</confirmation-dialog>

<div class="table-wrapper">
    <table class="table table-responsive align-middle mt-4">
        <thead class="table-light">
            <tr>
                <th v-if="showPhoto">Photo</th>
                <th class="align-middle">Name</th>
                <th class="align-middle">Category</th>
                <th class="align-middle">Price</th>
                <th class="text-center" style="width:10%">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="product in products" :key="product.id">
                <td v-if="showPhoto" class="align-middle">
                    <img :src="photoFullUrl(product)" class="rounded-circle img_photo" />
                </td>
                <td class="align-middle">{{ product.name }}</td>
                <td class="align-middle">{{ capitalize(product.type) }}</td>
                <td class="align-middle">{{ product.price }}€</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-xs btn-light" title="Edit Product" @click="editClick(product)" v-if="showEditButton">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-light" title="Delete Product" @click="deleteClick(product)" v-if="showDeleteButton">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</template>

<style scoped>
button {
    margin-left: 3px;
    margin-right: 3px;
}

.img_photo {
    width: 3.2rem;
    height: 3.2rem;
}
</style>
