<script setup>
import { RouterView } from 'vue-router'

import Navbar from "@/layout/Navbar.vue";
import Sidebar from "@/layout/Sidebar.vue";


</script>

<template>
    <Navbar/>
    <Sidebar/>
    <!-- TODO: Padding -->
    <main class="col-md-8 ms-sm-auto col-lg-10" style="margin-left: 280px !important;">
        <router-view></router-view>
    </main>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap');
@import "./assets/layout.css";


main {
    width: calc(100% - 280px) !important;
    overflow-x: hidden !important;
    padding: .5rem 2rem !important;
}
</style>
