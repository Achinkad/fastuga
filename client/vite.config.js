import { fileURLToPath, URL } from 'node:url'
import viteCompression from 'vite-plugin-compression';
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [vue(),viteCompression()],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url))
        }
    }
})
