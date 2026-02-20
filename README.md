# Analyt Loan 🚀
### The Self-Driving Operating System for Micro-Lenders

[![CI/CD Pipeline](https://github.com/Joweb1/Analyt-Loan/actions/workflows/main.yml/badge.svg)](https://github.com/Joweb1/Analyt-Loan/actions/workflows/main.yml)
[![Latest Version](https://img.shields.io/github/v/release/Joweb1/Analyt-Loan?include_prereleases)](https://github.com/Joweb1/Analyt-Loan/releases)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-red.svg)](https://laravel.com/)

Analyt Loan is a specialized SaaS platform designed to replace manual processes for micro-lenders and cooperative societies. It acts as a "Self-Driving Money" engine, automating the entire lending lifecycle from borrower onboarding to automated collections.

---

## 📖 Navigation
[🏠 Home (README)](README.md) | [⚙️ Technical Docs](TECHNICAL_DOCUMENTATION.md) | [🤝 Contributing](CONTRIBUTING.md)

---

## 📖 Table of Contents
- [Core Philosophy](#-core-philosophy)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Getting Started](#-getting-started)
- [Environment Configuration](#-environment-configuration)
- [Deployment (cPanel / Shared Hosting)](#-deployment-cpanel--shared-hosting)
- [Automation & Cron](#-automation--cron)
- [Quality Assurance](#-quality-assurance)
- [Security](#-security)

---

## 🧠 Core Philosophy: "Self-Driving Money"
Small lenders often lose up to 20% of their capital due to poor organization. Analyt Loan operates on three main pillars:
1. **The Memory:** Zero-error tracking of every loan, borrower, and due date.
2. **The Nudge:** Automated borrower engagement via Email and Push Notifications to reduce bad debt.
3. **The Engine:** Automated interest (flat/reducing balance), fees, and penalty calculations.

---

## ✨ Key Features
| Feature | Description |
| :--- | :--- |
| **Multi-Tenancy** | Secure data isolation for multiple organizations on a single platform. |
| **Trust Score™** | Proprietary algorithm identifying high-risk vs. reliable borrowers based on history. |
| **Omnibar Search** | Command-K style search to find any borrower or loan record instantly. |
| **Digital Vault** | Integrated collateral management system with automated valuation rules. |
| **Flexible Repayments** | Support for custom installment logic, grace periods, and late penalties. |
| **Real-time Alerts** | Browser push notifications and in-app action centers for staff and borrowers. |

---

## 🛠 Tech Stack
- **Backend:** PHP 8.4 / [Laravel 12](https://laravel.com/)
- **Frontend:** [Livewire 3](https://livewire.laravel.com/) (Reactive UI without heavy JS)
- **Styling:** [Tailwind CSS 4](https://tailwindcss.com/)
- **Database:** SQLite (Dev), MySQL (Prod)
- **Static Analysis:** [Larastan](https://github.com/larastan/larastan) (Level 5)
- **CI/CD:** GitHub Actions

---

## 🚀 Getting Started

### Prerequisites
- PHP ^8.3
- Composer
- Node.js & NPM

### Local Installation
1. **Clone the repository:**
   ```bash
   git clone https://github.com/Joweb1/Analyt-Loan.git
   cd Analyt-Loan
   ```
2. **Install dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```
3. **Setup environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Database & Migrations:**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```
5. **Start the server:**
   ```bash
   php artisan serve
   ```

---

## ⚙️ Environment Configuration

| Key | Development (Local) | Production (Shared Hosting) |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `sqlite` | `mysql` |
| `DB_HOST` | N/A | `sql100.iceiy.com` |
| `DB_DATABASE` | `database/database.sqlite` | `icei_41195783_analytloan` |
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `CRON_TOKEN` | *Random String* | *Secure Random String* |

---

## 🌐 Deployment (cPanel / Shared Hosting)

This project is optimized for cPanel environments **without SSH access**.

1. **FTP Upload:** Automate file transfer using the included [GitHub Action](.github/workflows/main.yml).
2. **Web-Based Setup:** After deployment, visit your domain to run migrations:
   - **URL:** `https://yourdomain.com/deploy-setup.php?token=YOUR_DB_PASSWORD&seed=true`
3. **Storage Link:** The setup script automatically creates the `storage:link` for you.
4. **Shared Hosting Tip:** Upload the project *outside* `public_html` and symlink `public_html` to the project's `public` folder for maximum security.

---

## 🤖 Automation & Cron

Since shared hosting varies, use an external service (e.g., [cron-job.org](https://cron-job.org)) to trigger the following secure endpoints:

- **Scheduler (1 min):** `https://yourdomain.com/cron/schedule?token=YOUR_CRON_TOKEN`
- **Queue Worker (1 min):** `https://yourdomain.com/cron/queue?token=YOUR_CRON_TOKEN`

---

## ✅ Quality Assurance

We maintain a high standard of code quality through automated testing and static analysis.

- **Unit & Feature Tests:**
  ```bash
  php artisan test
  ```
- **Static Analysis (PHPStan):**
  ```bash
  ./vendor/bin/phpstan analyse --memory-limit=1G
  ```
- **Code Linting (Pint):**
  ```bash
  ./vendor/bin/pint
  ```

---

## 🛡 Security
- **Data Isolation:** Enforced via Laravel Global Scopes on the `Organization` ID.
- **Route Protection:** Web-based cron and setup scripts are protected by `DB_PASSWORD` or `CRON_TOKEN`.
- **Setup Cleanup:** Always delete `public/deploy-setup.php` after your initial installation.

---

## 📄 License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
