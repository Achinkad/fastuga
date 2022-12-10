<script setup>
import { inject,ref,computed } from "vue";
import avatarNoneUrl from "@/assets/avatar-none.png";

const axios = inject('axios')
const toast = inject('toast')


const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  showId: {
    type: Boolean,
    default: true,
  },
  showEmail: {
    type: Boolean,
    default: true,
  },
  showRole: {
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
  showBlockButton: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["edit", "deleted", "forceRerender","blockToggled"]);

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
  emit("edit", user);
};

const toogleClick = (user) => {
  axios
    .patch("users/block/" + user.id, { blocked: user.blocked = 1 })
    .then((response) => {
      user.blocked = response.data.data.blocked
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
  <table class="table">
    <thead>
      <tr>
        <th v-if="showId" class="align-middle">#</th>
        <th v-if="showPhoto" class="align-middle">Photo</th>
        <th class="align-middle">Name</th>
        <th v-if="showEmail" class="align-middle">Email</th>
        <th v-if="showRole" class="align-middle">Role</th>
        <th v-if="showBlockButton" class="align-middle">Blocked?</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="user in users" :key="user.id">
        <td v-if="showId" class="align-middle">{{ user.id }}</td>
        <td v-if="showPhoto" class="align-middle">
          <img :src="photoFullUrl(user)" class="rounded-circle img_photo" />
        </td>
        <td class="align-middle">{{ user.name }}</td>
        <td v-if="showEmail" class="align-middle">{{ user.email }}</td>
        <td v-if="showRole" class="align-middle">
          <span v-if="user.type == 'EM'">Manager</span>
          <span v-if="user.type == 'EC'">Chef</span>
          <span v-if="user.type == 'ED'">Delivery</span>
          <span v-if="user.type == 'C'">Customer</span>
        </td>
        <td>
        <button
              class="btn btn-xs btn-light"
              @click="toogleClick(user)"

              v-if="showBlockButton"
            >
          
              <i
                class="bi bi-xs"
                
                :class="{
                  'bi-square': !user.blocked,
                  'bi-check2-square': user.blocked,
                }"
               
              ></i>
            </button>
          </td>
        <td class="text-end align-middle" v-if="showEditButton">
          <div class="d-flex justify-content-end">

            <button
              class="btn btn-xs btn-light"
              @click="editClick(user)"
              v-if="showEditButton"
            >
              <i class="bi bi-xs bi-pencil"></i>
            </button>
            <button class="btn btn-xs btn-light" @click="deleteClick(user)" v-if="showDeleteButton">
            <i class="bi bi-trash3"></i>
          </button>


          </div>
        </td>
      </tr>
    </tbody>
  </table>
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
