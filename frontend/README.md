# ReadyBill — Frontend Application

ReadyBill is a modern, high-performance grocery and retail point-of-sale (POS) and inventory management web application. This repository contains the frontend client built with modern web technologies, providing a swift, interactive, and responsive user experience.

---

## 🛠️ Tech Stack & Key Libraries

- **Core Framework**: [Vue 3](https://vuejs.org/) (Composition API)
- **Build Tool**: [Vite](https://vite.dev/) (super-fast hot-reloads and optimized production bundling)
- **State Management**: [Pinia](https://pinia.vuejs.org/) (with persistent state support via `pinia-plugin-persistedstate`)
- **Routing**: [Vue Router](https://router.vuejs.org/)
- **Styling**: [Bootstrap 5](https://getbootstrap.com/) + [Bootstrap Icons](https://icons.getbootstrap.com/) (augmented with custom styling & popper.js)
- **Networking**: [Axios](https://axios-http.com/) (configured with standard timeouts and base endpoints)
- **Real-Time Capabilities**: [Laravel Echo](https://laravel.com/docs/broadcasting) & [Pusher JS](https://pusher.com/) for WebSocket broadcasting (e.g., Reverb server updates)
- **Interactive Tables**: [DataTables.net](https://datatables.net/) with Bootstrap 5 templates
- **QR / Barcode Scanning**: Powered by `@zxing/library` & `html5-qrcode` for seamless camera-based barcode inputs
- **Internationalization (i18n)**: [Vue I18n](https://vue-i18n.intlify.dev/) for localization (supporting India, Germany, etc.)

---

## 🚀 GitHub Deployable Status Checklist

Before publishing this repository to GitHub or initiating a deployment pipeline, verify that:

- [x] **No Active Secrets**: The local `.env` has been permanently deleted from this directory.
- [x] **Redacted Variables**: A clean template file `.env.production` has been created with all critical values replaced by generic placeholders (e.g., `"YOUR_API_BASE_URL"`).
- [x] **Environment Exclusion**: The `.gitignore` file has been updated to prevent any `.env`, `.env.production`, or local overrides (`.env.*.local`) from being committed to Git.
- [x] **Distribution Excluded**: Build output directory (`/dist`) and dependency folders (`node_modules`) are configured in `.gitignore` so they are not uploaded to Git.

---

## 💻 Getting Started

### 1. Prerequisites
Ensure you have **Node.js** installed (Version range specified in `package.json`):
- `Node.js >= 20.19.0` or `>= 22.12.0`

### 2. Installation
Install project dependencies:
```bash
npm install
```

### 3. Setup Environment Variables
1. Duplicate the template file:
   ```bash
   cp .env.production .env
   ```
2. Fill in the actual endpoints, secret keys, and Reverb properties inside `.env`. (This file is ignored by Git, keeping your production credentials safe).

### 4. Local Development
Start the Vite development server locally:
```bash
npm run dev
```

### 5. Build for Production
To compile and minify the assets into a clean deployable package:
```bash
npm run build
```
The built assets will be generated in the `dist/` directory, ready to be deployed to static hosting solutions (e.g., Netlify, Vercel, AWS S3, or Nginx).

---

## 🎨 Recommended IDE Setup

- **Editor**: [VS Code](https://code.visualstudio.com/)
- **Extensions**: [Vue (Official / Volar)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (please disable Vetur if active).
- **Vue DevTools**: Install [Vue.js Devtools](https://chromewebstore.google.com/detail/vuejs-devtools/nhdogjmejiglipccpnnnanhbledajbpd) in your browser for advanced debugging.
