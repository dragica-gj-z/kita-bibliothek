import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.scss', 'resources/js/app.js'],
      refresh: true,
    }),
    vue(),
  ],

  build: {
    outDir: 'public/build',   
    manifest: true,           
    emptyOutDir: true,   
    rollupOptions: {
      input: 'resources/js/app.js', 
    },     
  },
})
