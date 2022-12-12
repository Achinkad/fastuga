import { defineStore } from 'pinia'

export const useConfigStore = defineStore('config', () => {
    const state = {
        showNavbar: true,
        showSidebar: true,
        showMain: true
    }
    return state
})
