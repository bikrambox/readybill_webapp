# Backend Web Application

A premium, high-performance, modular backend API built on **Laravel 10**, tailored for multi-region and multi-role operations (Admin, Agent, and core platforms across regions like Germany and India).

---

## 🚀 Tech Stack

- **Core Framework**: PHP 8.1+ & [Laravel 10.x](https://laravel.com)
- **Modular Architecture**: [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules) for highly decoupled, scalable domains
- **Authentication & Security**: [Laravel Passport](https://laravel.com/docs/passport) (OAuth2) and [Laravel Sanctum](https://laravel.com/docs/sanctum) (API tokens)
- **Real-Time WebSockets**: [Laravel Reverb](https://laravel.com/docs/reverb) (Next-generation WebSocket server)
- **Notifications**: [Kreait Firebase](https://github.com/kreait/laravel-firebase) integration
- **File & Document Processing**: 
  - [Barryvdh Laravel DOMPDF](https://github.com/barryvdh/laravel-dompdf) for dynamic PDF generation
  - [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) for high-volume excel exports/imports
  - [Intervention Image](https://image.intervention.io/) for dynamic media processing
- **Testing & Quality Assurance**: [PHPUnit](https://phpunit.de) & [Laravel Pint](https://laravel.com/docs/pint)

---

## 📂 Modular Architecture

The application is structured using a domain-driven modular approach inside the `Modules/` directory:

1. **`Core`**: Shares global business logic, base repositories, and cross-cutting helpers.
2. **`CoreWeb`**: Handles core frontend assets, templates, and general web routes.
3. **`Authentication`**: Centralized OAuth2/Passport and Sanctum login flows, MFA, and permission checks.
4. **`Admin`**: Administrative control panel APIs, configuration, and audit logs.
5. **`Agent`**: Dedicated APIs, workflows, and task managers for Agents.
6. **`GroceryGermany`**: Germany-specific grocery catalogues, pricing rules, tax handling, and logistics.
7. **`GroceryIndia`**: India-specific grocery inventories, local payment gateways, regional taxation, and supply chain rules.

---

## 🛠️ Installation & Setup

Follow these steps to set up the environment locally or prepare for a production deploy:

### 1. Prerequisites
- **PHP** >= 8.1 with required extensions (`mbstring`, `openssl`, `pdo`, `xml`, `zip`, `gd`)
- **Composer**
- **Node.js & NPM** (for compiling frontend assets via Vite)

### 2. Clone and Install Dependencies
```bash
# Clone the repository
git clone <your-repo-url>
cd backend

# Install PHP dependencies
composer install

# Install Javascript dependencies
npm install
```

### 3. Environment Configuration
Create your local environment file:
```bash
cp .env.example .env
```
Ensure you update the database credentials, Passport keys, Firebase settings, and Reverb WebSocket variables inside `.env`.

### 4. Database Initialization
Generate the application key, run migrations, and seed the initial schema:
```bash
php artisan key:generate
php artisan migrate --seed
```

### 5. Passport & OAuth Keys
Generate the encryption keys needed for secure token issuing:
```bash
php artisan passport:install
```

### 6. Development Server
Start the local server and Vite hot reloader:
```bash
# Start Laravel development server
php artisan serve

# Run Vite server for assets
npm run dev
```

---

## 🔒 GitHub & Production Deployment Readiness

This repository is **fully configured and ready for safe GitHub deployment**:

- **Environment Protection**: `.env` and `.env.production` are strictly excluded from commits in `.gitignore` to prevent any credential leaks. An up-to-date, safe template is kept in `.env.example`.
- **Private Key Isolation**: All Passport cryptographic keys (`*.key`) are safely gitignored in `/storage/*.key`.
- **Standardized Code Quality**: Code styles are enforced using `laravel/pint` configurations.
- **Production Asset Pipelines**: Configured with **Vite** for fast, optimized, and cached static asset delivery.
- **Deployment Pipelines**: Compatible with AWS Elastic Beanstalk, Laravel Forge, Heroku, Docker, or traditional VPS environments. Ensure you set your production environment variables securely inside your deployment platform's secret manager.
