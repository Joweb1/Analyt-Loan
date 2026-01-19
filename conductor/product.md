# Product Guide

## 1. The Big Picture: The "Self-Driving" Lemonade Stand

Analyt Loan 2.0 is a "Self-Driving" Robot Assistant for a lending business. It replaces manual, error-prone tracking of loans with an automated system that remembers who owes money, calculates bills, and sends payment reminders, ensuring the lender doesn't have to be the "bad guy."

## 2. User Roles

The system will serve the following user roles:
- **Shop Owner (Administrator):** Manages the business, views the dashboard, and oversees all lending activities.
- **Workers (Field Agents):** Use the system to manage tasks, such as visiting borrowers to collect payments.
- **Customers (Borrowers):** Access a mini-app to view their loan status, outstanding balance, and collateral information.

## 3. Core Features

The system is organized into five main "rooms" or feature areas:

- **Room 1: The Scoreboard (Dashboard):** The main landing page, providing an instant overview of the business's health with large, clear cards.
  - **Pulse Chart:** A real-time line chart showing cash flow.
  - **Inbox/To-Do List:** Actionable alerts for late payments or new loan applications.

- **Room 2: The Address Book (People/CRM):** A comprehensive customer relationship management system.
  - **Visual Records:** Stores photos of borrowers and their ID cards.
  - **Drawer UI:** Clicking a name smoothly slides out a detail panel from the right, keeping the main view visible.
  - **Trust Score:** A 0-100 score for each borrower that adjusts based on their payment history.

- **Room 3: The Vault (Collaterals):** A new feature for managing physical items held as security for loans.
  - **Digital Shelf:** A visual interface displaying photos of all collateral items.
  - **The 50% Rule:** A strict business rule enforced by the system: the value of the collateral must be at least 50% of the loan amount.

- **Room 4: The Reminder Buddy (Tasks):** A task management system for field agents.
  - **Daily Checklist:** Automatically generates a daily, location-optimized list of borrowers to visit.

- **Room 5: The Borrower's Remote (Mini-App):** A simple, mobile-friendly application for borrowers.
  - **Simple Login:** Phone number-based login.
  - **Clear Status:** Shows the outstanding balance and a photo of their collateral to build trust.

### Key UI/UX Elements:

- **The Magic Eye (Omnibar):** A prominent, global search bar at the top of the screen for instantly finding any information.
- **The Magic Button (FAB):** A Floating Action Button in the bottom right corner for creating a new loan from anywhere in the app.

## 4. Design & Engineering Philosophy

### What It Looks & Feels Like (UI/UX)

- **Atmosphere (Material Design 3):** A clean, modern, uncluttered interface with ample white space and soft, rounded corners.
- **Colors (Traffic Lights):** A simple, intuitive color scheme to communicate status:
    - **Green:** Good (e.g., paid, secure).
    - **Red:** Bad (e.g., late, lost).
    - **Blue:** Active (e.g., normal).

### How We Build It (Engineering Manifesto)

- **The Tools (Tech Stack):**
    - **Skeleton:** Laravel & MySQL.
    - **Paint:** Tailwind CSS.
    - **Speed:** Livewire & Alpine.js.
- **The Safety Rules:**
    - **Trunk-Based Development:** All developers integrate their work into a shared mainline daily.
    - **Feature Flags (The Light Switch):** New features are developed behind a "curtain" and are only made visible to users when they are ready.
    - **The Beyoncé Rule:** "If you liked it, then you should have put a test on it." No new code is accepted without accompanying automated tests.
- **The Production Line (CI/CD):** An automated "Robot Guard" checks all code for linting errors and runs unit tests before it can be merged.

## 5. Why It Matters (The Vision)

The ultimate goal is to transform a messy, stressful business into a calm, "Self-Driving" machine. The software ensures that when a lender gives out money, it comes back, creating a more stable and reliable lending process.
