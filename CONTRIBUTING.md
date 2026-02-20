# Contributing to Analyt Loan 🤝

Thank you for your interest in contributing to Analyt Loan! We welcome contributions from developers of all skill levels to help make this platform better for micro-lenders everywhere.

---

## 📖 Navigation
[🏠 Home (README)](README.md) | [⚙️ Technical Docs](TECHNICAL_DOCUMENTATION.md) | [🤝 Contributing](CONTRIBUTING.md)

---

## 🚀 How to Contribute

### 1. Reporting Bugs 🐛
If you find a bug, please open an issue on GitHub and include:
*   A clear, descriptive title.
*   Steps to reproduce the issue.
*   Expected vs. actual behavior.
*   Your environment (PHP version, browser, etc.).

### 2. Suggesting Features ✨
We love new ideas! Please open an issue with the "feature request" tag and describe:
*   The problem this feature solves.
*   How you imagine it working.

### 3. Pull Requests 🛠️
1.  **Fork the repository** and create your branch from `main`.
2.  **Ensure code quality:** Run `./vendor/bin/pint` to fix styling and `./vendor/bin/phpstan analyse` to check for types.
3.  **Write tests:** Ensure any new feature or bug fix has corresponding tests.
4.  **Submit the PR:** Provide a clear description of your changes.

---

## 🎨 Coding Standards

### PHP & Laravel
*   We follow **PSR-12** coding standards via Laravel Pint.
*   Maintain **PHPStan Level 5** or higher.
*   Use Type Hinting everywhere possible.
*   Logic should be kept in **Services** or **Models**, not in Livewire components.

### Frontend
*   Use **Tailwind CSS 4** for all styling.
*   Use **Livewire 3** for reactive components.
*   Keep JavaScript minimal and localized to Alpine.js where absolutely necessary.

---

## ✅ Development Checklist
Before submitting a pull request, please ensure:
- [ ] `composer test` passes with 100% success.
- [ ] `./vendor/bin/pint` has been run.
- [ ] `./vendor/bin/phpstan analyse` returns no errors.
- [ ] You have updated the documentation if necessary.

---

## 📄 License
By contributing to Analyt Loan, you agree that your contributions will be licensed under its [MIT License](LICENSE.md).
