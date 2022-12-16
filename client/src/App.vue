<script setup>
import { RouterView } from 'vue-router'
import { useConfigStore } from './stores/config.js'

import Navbar from "@/layout/Navbar.vue"
import Sidebar from "@/layout/Sidebar.vue"

const configStore = useConfigStore()
</script>

<template>
    <head>
        <meta name="description" content="FastugaApp">
    </head>
    <div class="wrapper">
        <Navbar v-if="configStore.showNavbar" />
        <Sidebar v-if="configStore.showSidebar" />
        <main v-if="configStore.showMain">
            <div class="content">
                <router-view />
            </div>
        </main>
        <section v-if="!configStore.showMain">
            <router-view />
        </section>
        <footer class="footer" v-if="configStore.showFooter">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        2022 © Project - Distributed Applications Development
                    </div>
                    <div class="col-md-6">
                        <div class="text-md-end footer-links d-none d-md-block">
                            <a target="_blank" href="https://github.com/joseareia/fastuga/">Code</a>
                            <a href="#">Contacts</a>
                            <a href="#">About Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap');
@import "./assets/css/master.css";
@import "./assets/css/components.css";

</style>
