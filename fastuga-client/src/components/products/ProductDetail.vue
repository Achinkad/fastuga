<script setup>
import { ref, watch, computed, inject } from "vue";
import avatarNoneUrl from '@/assets/product-none.png'

const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        required: false
    },
})

const emit = defineEmits(["save", "cancel","add"]);

const editingProduct = ref(props.product)

watch(
    () => props.product,
    (newProduct) => {
        editingProduct.value = newProduct
    },
    { immediate: true }
)

const photoFullUrl = computed(() => {
    return editingProduct.value.photo_url
    ? serverBaseUrl + "api/storage/products/" + editingProduct.value.photo_url
    : avatarNoneUrl
})

const handleUpload = (files) => {
    editingProduct.value.photo_url = files[0];
}

const save = () => {
    let formData = new FormData()
    formData.append('name', editingProduct.value.name);
    formData.append('price', editingProduct.value.price);
    formData.append('type', editingProduct.value.type);
    formData.append('description', editingProduct.value.description);
    formData.append('photo_url', editingProduct.value.photo_url);

    emit("save", formData);
}

const add = () => {
    let formData = new FormData()
    formData.append('name', editingProduct.value.name);
    formData.append('price', editingProduct.value.price);
    formData.append('type', editingProduct.value.type);
    formData.append('description', editingProduct.value.description);
    formData.append('photo_url', editingProduct.value.photo_url);

    emit("add", formData);
}

const cancel = () => {
    emit("cancel", editingProduct.value);
}


</script>

<template>
    <form class="row g-3 needs-validation" enctype="multipart/form-data">
        <h3 class="mt-5 mb-3" v-if="$route.name=='Product'">Editing Product #{{ editingProduct.id }}</h3>
        <h3 class="mt-5 mb-3" v-if="$route.name=='newProduct'">Adding New Product</h3>
        <hr />
        <div class="d-flex flex-wrap justify-content-between">
            <div class="w-75 pe-4">
                <div class="mb-3">
                    <label for="inputName" class="form-label">Name</label>
                    <input
                    type="text"
                    class="form-control"
                    id="inputName"
                    placeholder="Product Name"
                    required
                    v-model="editingProduct.name"
                    />
                    <field-error-message :errors="errors" fieldName="name"></field-error-message>
                </div>
                <div class="mb-3">
                    <label for="inputPrice" class="form-label">Price</label>
                    <input
                    type="text"
                    class="form-control"
                    id="inputPrice"
                    placeholder="Product Price"
                    required
                    v-model="editingProduct.price"
                    />
                    <field-error-message :errors="errors" fieldName="price"></field-error-message>
                </div>
                <div class="mb-3">
                    <label for="type">Type:</label>
                    <select id="type" name="type"  v-model="editingProduct.type" required>
                        <option value="hot dish">Hot Dish</option>
                        <option value="cold dish">Cold Dish</option>
                        <option value="drink">Drink</option>
                        <option value="dessert">Dessert</option>
                    </select>
                    <field-error-message :errors="errors" fieldName="type"></field-error-message>
                </div>

                <div class="mb-3">
                    <label for="inputDescription" class="form-label">Description</label>
                    <textarea
                    class="form-control"
                    id="inputDescription"
                    rows="4"
                    v-model="editingProduct.description"
                    required
                    ></textarea>
                    <field-error-message :errors="errors" fieldName="description"></field-error-message>
                </div>

            </div>
            <div class="w-25">
                <div class="mb-3">
                    <label class="form-label">Photo</label>
                    <div class="form-control text-center">
                        <img :src="photoFullUrl" class="w-100" />
                        <input type="file" name='upload' @change="handleUpload($event.target.files)" required>

                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3 d-flex justify-content-end">
            <button type="button" class="btn btn-primary px-5" @click="add" v-if="$route.name=='newProduct'">Add Product</button>
            <button type="button" class="btn btn-primary px-5" @click="save" v-if="$route.name=='Product'">Save Product</button>
            <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
        </div>
    </form>
</template>

<style scoped>
.total_hours {
    width: 26rem;
}
#label {
    background-color: orange;
    color: white;
    padding: 0.5rem;
    font-family: sans-serif;
    border-radius: 0.3rem;
    cursor: pointer;
    margin-top: 1rem;

}
</style>
