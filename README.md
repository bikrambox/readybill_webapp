# ReadyBill Full-Stack Application

Welcome to the **ReadyBill** repository. This is a unified workspace comprising a high-performance modular backend and a modern single-page frontend application designed for multi-region and multi-role operations.

---

## 📂 Repository Structure

- **[`/backend`](/backend)**: A comprehensive, modular **Laravel 10** RESTful API with distinct domains for Admins, Agents, and operations in Germany and India.
- **[`/frontend`](/frontend)**: A reactive and dynamic Single-Page Application (SPA) built using **Vue.js** (delivered via Vite) that acts as the customer and agent dashboard.

---

## 🛠️ Stack & Features At A Glance

### 🖥️ Backend Application
* **Framework**: PHP 8.1+ & **Laravel 10.x**
* **Modularity**: Domain-driven sub-applications (`Modules/Core`, `Modules/Authentication`, `Modules/Admin`, `Modules/Agent`, `Modules/GroceryGermany`, and `Modules/GroceryIndia`) powered by `nwidart/laravel-modules`.
* **Security & Auth**: Secure endpoint access utilizing **Laravel Passport** (OAuth2 clients and personal tokens) and **Laravel Sanctum**.
* **Real-Time Communication**: Multi-channel event broadcasting and WebSocket message routing using **Laravel Reverb**.
* **Exports & Documents**: Dynamic template rendering to PDF (`laravel-dompdf`) and high-speed multi-sheet spreadsheet generators (`phpspreadsheet`).

### 🎨 Frontend Application
* **Framework**: **Vue.js 3**
* **Build System**: **Vite** for lightning-fast compilation, HMR (Hot Module Replacement), and optimized production bundling.
* **Key Visual Features**: Built-in interactive dashboards, responsive multi-step checkout layouts, real-time reactive tables, inline barcode scanners (`@zxing/library`), custom notification toggles, and multi-currency/multi-tax formatting for regional branches.
* **Production Build**: Bundled and optimized production-ready assets located in the `/dist` directory.

---

## 🚀 Quick Setup & Getting Started

Ensure you have **PHP >= 8.1**, **Composer**, and **Node.js** installed on your system.

### 1. Initialize the Backend
```bash
# Navigate to the backend directory
cd backend/backend

# Install composer dependencies
composer install

# Configure local environmental file
cp .env.example .env
# Edit database and WebSocket credentials inside .env

# Run database migrations and seed default datasets
php artisan migrate --seed

# Install OAuth keys
php artisan passport:install

# Spin up local development server
php artisan serve
```

### 2. Initialize the Frontend
```bash
# Navigate to the frontend directory
cd frontend/frontend

# Install package dependencies
npm install

# Run the Vue dev environment
npm run dev
```

---

## 🔒 GitHub & Production Deployment Safety
This repository includes enterprise-grade guardrails to prevent credentials leaks on public version control system (VCS) repositories:
- All sensitive files (`.env`, `.env.production`, `.env.backup`) are pre-configured in their respective `.gitignore` directories.
- Passport cryptography keys (`/storage/*.key`) are strictly isolated and never committed to the source.
- Production-ready compilation is fully configured under `/frontend/frontend/dist` and `/backend/backend/public/build`, ready to be served securely on CDNs or static hosting providers.
