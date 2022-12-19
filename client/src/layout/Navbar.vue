<script setup>
import { inject } from "vue";
import { useUserStore } from '../stores/user.js'
import { useRouter } from 'vue-router'
import avatarNoneUrl from '@/assets/avatar-none.png'

const serverBaseUrl = inject("serverBaseUrl")
const router = useRouter()
const userStore = useUserStore()
const toast = inject("toast")

const logout = () => {
    if (userStore.logout()) {
        router.push({ name: 'Login' })
        toast.success("User has logged out of the application.")
    } else {
        toast.error("There was a problem logging out of the application!")
    }
}
const photoFullUrl = () => {
    return userStore.user.photo_url ? serverBaseUrl + '/storage/fotos/' + userStore.user.photo_url : avatarNoneUrl
}
</script>

<template>
    <nav class="navbar navbar-expand-md flex-md-nowrap">
        <button id="buttonSidebarExpandId" class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item d-flex" style="align-items:center;" v-if="!userStore.user">
                    <router-link class="nav-link" :class="{ active: $route.name === 'Register' }"
                        :to="{ name: 'Register' }">
                        Register
                    </router-link>
                </li>
                <li class="nav-item d-flex" v-if="!userStore.user" style="align-items:center;">
                    <router-link style="vertical-align:middle;" class="nav-link"
                        :class="{ active: $route.name === 'Login' }" :to="{ name: 'Login' }">
                        Login
                    </router-link>
                </li>
                <div class="topbar-divider d-none d-sm-block" v-if="!userStore.user"></div>
                <li class="nav-item dropdown nav-user" v-if="userStore.user" id="dropdown-user">
                    <a class="nav-link" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <span class="account-user-avatar">
                            <img alt="user image" :src="photoFullUrl()" class="rounded-circle img_photo">
                        </span>
                        <span>
                            <span class="account-user-name">{{ userStore.user.name }}</span>
                            <span class="account-position" v-if="userStore.user.type == 'EM'">Manager</span>
                            <span class="account-position" v-if="userStore.user.type == 'EC'">Chef</span>
                            <span class="account-position" v-if="userStore.user.type == 'ED'">Delivery</span>
                            <span class="account-position" v-if="userStore.user.type == 'C'">Customer</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        <li>
                            <router-link class="dropdown-item" :class="{ active: $route.name === 'ChangeProfile' }"
                                :to="{ name: 'ChangeProfile' }">
                                Profile
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" :class="{ active: $route.name === 'ChangePassword' }"
                                :to="{ name: 'ChangePassword' }">
                                Change password
                            </router-link>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <a class="dropdown-item" @click.prevent="logout" style="cursor:pointer;">Logout</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item" v-else="!userStore.user">
                    <a class="nav-link" href="#" role="button">
                        <span class="avatar-text">Customer (Anonymous)</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</template>

<style scoped>
.img_photo {
    width: 32px;
    height: 32px;
}

.nav-link {
    padding: 0;
    min-width: 32px;
    display: block;
    text-align: center;
    margin: 0 7px;
    margin-right: 7px;
    position: relative;
}

.nav-user {
    padding: calc(31px * .5) 20px calc(31px * .5) 57px !important;
    text-align: left !important;
    position: relative;
    border-width: 0 1px;
    min-height: 70px;
    transition: none;
}

.nav-user .account-user-avatar {
    position: absolute;
    top: calc(9px * .5);
    left: -35px;
}

.nav-user .account-user-name {
    display: block;
    font-weight: 600;
}

.nav-user .account-position {
    display: block;
    font-size: 12px;
    margin-top: -3px;
    text-align: initial;
}

.navbar {
    height: 4.375rem;
    padding: 0 12px;
    min-height: 70px;
    position: fixed;
    left: 260px;
    top: 0;
    right: 0;
    z-index: 1000;
    box-shadow: 0 0 35px 0 rgba(154, 161, 171, .15) !important;
    background-color: #fff;
}

.navbar-brand {
    background-color: transparent !important;
    box-shadow: none !important;
}

.topbar-divider {
    width: 0;
    border-right: 1px solid #e3e6f0;
    height: calc(4.375rem - 2rem);
    margin: auto 1rem;
}

.avatar-text {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 38px;
}
</style>
