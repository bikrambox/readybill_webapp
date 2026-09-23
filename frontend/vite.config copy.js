// import { fileURLToPath, URL } from 'node:url'

// import { defineConfig } from 'vite'
// import vue from '@vitejs/plugin-vue'
// import vueDevTools from 'vite-plugin-vue-devtools'

// // https://vite.dev/config/
// export default defineConfig({
//   plugins: [
//     vue(),
//     vueDevTools(),
//   ],
//   resolve: {
//     alias: {
//       '@': fileURLToPath(new URL('./src', import.meta.url)),
//       'jquery': 'jquery/dist/jquery.js'
//     },
//   },
//   server: {
//     port: 3000
//   }

// })


// import { fileURLToPath, URL } from 'node:url'
// import { defineConfig } from 'vite'
// import vue from '@vitejs/plugin-vue'
// import vueDevTools from 'vite-plugin-vue-devtools'

// // https://vite.dev/config/
// export default defineConfig({
//   plugins: [
//     vue(),
//     vueDevTools(),
//   ],
//   resolve: {
//     alias: {
//       '@': fileURLToPath(new URL('./src', import.meta.url)),
//       'jquery': 'jquery/dist/jquery.js'
//     },
//   },
//   server: {
//     port: 3000,
//     open: true, // Automatically open browser
//     host: true // Expose to network
//   },
//   build: {
//     outDir: 'dist', // Output directory
//     assetsDir: 'assets', // Assets directory
//     sourcemap: false, // Disable sourcemaps for production
//     minify: 'esbuild', // Use esbuild for faster minification
//     target: 'es2015', // Browser target
//     cssCodeSplit: true, // Enable CSS code splitting
//     rollupOptions: {
//       output: {
//         // Manual chunks for better caching
//         manualChunks: {
//           'vendor': ['vue', 'vue-router'], // Add vue-router if you use it
//           'jquery': ['jquery']
//         },
//         // Asset file naming
//         assetFileNames: (assetInfo) => {
//           let extType = assetInfo.name.split('.').at(-1);
//           if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
//             return `assets/images/[name]-[hash][extname]`;
//           }
//           if (/woff|woff2|eot|ttf|otf/i.test(extType)) {
//             return `assets/fonts/[name]-[hash][extname]`;
//           }
//           return `assets/[name]-[hash][extname]`;
//         },
//         chunkFileNames: 'assets/js/[name]-[hash].js',
//         entryFileNames: 'assets/js/[name]-[hash].js',
//       }
//     },
//     // Build optimization
//     chunkSizeWarningLimit: 1000, // Increase chunk size warning limit
//   },
//   // Optimize dependencies
//   optimizeDeps: {
//     include: ['vue', 'jquery']
//   }
// })



import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  base: './', // Add this for relative paths
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      'jquery': 'jquery/dist/jquery.js'
    },
  },
  server: {
    port: 3000
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    rollupOptions: {
      output: {
        manualChunks: undefined, // Disable code splitting for file:// protocol
      }
    }
  }
})
