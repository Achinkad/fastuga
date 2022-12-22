<script setup>
import { inject } from "vue"

const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
    order_items: {
        type: Array,
        default: () => [],
    }
})

const photoFullUrl = (product) => {
    return product.photo_url ? serverBaseUrl + "/storage/products/" + product.photo_url : avatarNoneUrl;
}
</script>

<template>
    <div class="table-responsive">
        <table class="table align-middle mt-4">
            <thead class="table-light">
                <tr>
                    <th style="width:7%;">ID</th>
                    <th style="width:10%;">Photo</th>
                    <th style="width:17%;">Name</th>
                    <th>Notes</th>
                    <th style="width:10%;">Status</th>
                    <th style="width:10%;">Date</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="order_item in order_items">
                    <td>{{ order_item.order.ticket_number }}-{{order_item.order_local_number}}</td>
                    <td  class="align-middle">
                        <img alt="product" :src="photoFullUrl(order_item.product)" class="rounded-circle img_photo" />
                    </td>
                    <td>{{ order_item.product.name }}</td>
                    <td v-if="order_item.notes">{{ order_item.notes }}</td>
                    <td v-else> -- </td>
                    <td>
                        <span v-if="order_item.status == 'W'">
                            <span class="badge badge-info-lighten">Waiting</span>
                        </span>
                        <span v-if="order_item.status == 'P'">
                            <span class="badge badge-warning-lighten">Preparing</span>
                        </span>
                        <span v-if="order_item.status == 'R'">
                            <span class="badge badge-success-lighten">Ready</span>
                        </span>
                    </td>
                    <td>{{ order_item.order.date }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.img_photo {
    width: 3.2rem;
    height: 3.2rem;
}

td {
    word-wrap: break-word;
}

table {
    table-layout: fixed;
}
</style>
