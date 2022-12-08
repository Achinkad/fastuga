<script setup>
import { inject } from "vue";
import { useUserStore } from '../stores/user.js'

const userStore = useUserStore()
const toast = inject("toast")

const logout = async () => {
    if (await userStore.logout()) {
        toast.success("User has logged out of the application.")
        router.push({ name: 'login' })
    } else {
        toast.error("There was a problem logging out of the application!")
    }
}
</script>

<template>
    <nav class="navbar navbar-expand-md navbar-light bg-light sticky-top flex-md-nowrap p-0 shadow">
        <div class="container-fluid">
            <button id="buttonSidebarExpandId" class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav">
                    <li class="nav-item d-flex" style="align-items:center;">
                        <router-link class="nav-link" :class="{ active: $route.name === 'Register' }"
                            :to="{ name: 'Register' }">
                            Register
                        </router-link>
                    </li>
                    <li class="nav-item d-flex" v-show="!userStore.user" style="align-items:center;">
                        <router-link style="vertical-align:middle;" class="nav-link"
                            :class="{ active: $route.name === 'Login' }" :to="{ name: 'Login' }">
                            Login
                        </router-link>
                    </li>
                    <div class="topbar-divider d-none d-sm-block"></div>
                    <li class="nav-item dropdown" v-if="userStore.user">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-text">{{ userStore.user.name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu dropdown-menu-end"
                            aria-labelledby="navbarDropdownMenuLink">
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
        </div>
    </nav>
</template>

<style>
.navbar {
    height: 4.375rem;
}

.navbar.shadow {
    box-shadow: 0 .5rem 2rem rgba(0, 0, 0, .1) !important;
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
</style>