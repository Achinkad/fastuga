<script setup>
import { inject, ref, computed } from "vue";
import avatarNoneUrl from "@/assets/avatar-none.png";

const axios = inject('axios')
const toast = inject('toast')
const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },

});

const emit = defineEmits(["edit", "deleted", "forceRerender", "blockToggled"]);
const userToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const userToDeleteDescription = computed(() => {
    return userToDelete.value
        ? `#${userToDelete.value.id} (${userToDelete.value.name})`
        : ""
})


const photoFullUrl = (user) => {
    return user.photo_url
        ? serverBaseUrl + "/storage/fotos/" + user.photo_url
        : avatarNoneUrl;
};

const editClick = (user) => {
    emit("edit", user)
};

const toogleClick = (user) => {
    axios
        .patch("users/block/" + user.id, { blocked: user.blocked = 1 })
        .then((response) => {
            user.blocked = response.data.data.blocked
            if (user.blocked) {
                toast.success(user.name + " was blocked with success.")
            } else {
                toast.success(user.name + " was unblocked with success.")
            }
            emit("blockToggled", user)
        })
        .catch((error) => {
            console.log(error)
        })
}

const dialogConfirmedDelete = () => {
    axios
        .delete(serverBaseUrl + "/api/users/" + userToDelete.value.id)
        .then((response) => {
            emit("deleted", response.data.data)
            toast.info("User " + userToDeleteDescription.value + " was deleted")
            emit("forceRerender", response.data.data)
        })
        .catch((error) => {
            console.log(error)
        })

}

const deleteClick = (user) => {
    userToDelete.value = user
    deleteConfirmationDialog.value.show()
}
</script>

<template>
    <confirmation-dialog ref="deleteConfirmationDialog" confirmationBtn="Delete user"
        :msg="`Do you really want to delete the user ${userToDeleteDescription}?`" @confirmed="dialogConfirmedDelete">
    </confirmation-dialog>
    <div class="table-responsive">
        <table class="table align-middle mt-4">
            <thead class="table-light">
                <tr>
                    <th class="align-middle">Photo</th>
                    <th class="align-middle">Name</th>
                    <th class="align-middle">Email</th>
                    <th class="align-middle">Role</th>
                    <th class="text-center">Blocked</th>
                    <th class="text-center" style="width:10%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id">
                    <td class="align-middle">
                        <img alt="userPhoto" :src="photoFullUrl(user)" class="rounded-circle img_photo" />
                    </td>
                    <td class="align-middle">{{ user.name }}</td>
                    <td class="align-middle">{{ user.email }}</td>
                    <td class="align-middle">
                        <span v-if="user.type == 'EM'">Manager</span>
                        <span v-if="user.type == 'EC'">Chef</span>
                        <span v-if="user.type == 'ED'">Delivery</span>
                        <span v-if="user.type == 'C'">Customer</span>
                    </td>
                    <td>
                        <div class="form-check form-switch text-center">
                            <div class="d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" role="switch" @click="toogleClick(user)"
                                    :checked="user.blocked">
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-xs btn-light" @click="editClick(user)">
                                <i class="bi bi-xs bi-pencil"></i>
                            </button>
                            <button class="btn btn-xs btn-light" @click="deleteClick(user)">
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
