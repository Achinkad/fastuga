<script setup>
import { onBeforeUnmount, onBeforeMount } from 'vue'
import { useConfigStore } from '../stores/config.js'

const configStore = useConfigStore()

onBeforeMount(() => {
    configStore.showNavbar = false
    configStore.showSidebar = false
    configStore.showMain = false
    configStore.showFooter = false
})

onBeforeUnmount(() => {
    configStore.showNavbar = true
    configStore.showSidebar = true
    configStore.showMain = true
    configStore.showFooter = true
})
</script>

<template>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <h4 class="">Well, there's nothing to see here.</h4>
                        <p class="text-center mt-3">Wanna go back
                            <router-link :to="{ name: 'Dashboard' }" style="color:#212529!important;">
                                <b>home?</b>
                            </router-link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
