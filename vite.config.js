// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'


export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.scss'],
                refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // erreichbar im Docker-Netz
        port: 5173,
        strictPort: true,
        hmr: {
            // Hinweis: Wenn HMR im Browser nicht connectet, setze hier deine Host-IP statt "localhost" ein.
            host: 'localhost',
            port: 5173,
        },
        watch: {
            // Verhindert File-watcher-Probleme in gemounteten Volumes (Docker Desktop/WSL)
            usePolling: true,
        },
    },
})