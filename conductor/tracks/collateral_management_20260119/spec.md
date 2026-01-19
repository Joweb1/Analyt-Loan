# Specification: Collateral Management (Vault)

## 1. Overview

This specification details the requirements for the "Collateral Management (Vault)" feature. This feature allows for the tracking and management of physical items held as security for loans, as described in the Product Guide as "Room 3: The Vault."

## 2. Core Feature Requirements

### 2.1. The Vault

- **Functionality:** Create, Read, Update, and Delete (CRUD) operations for collateral items.
- **Data Model:** A collateral item should have the following attributes:
  - Name/Title
  - Description
  - Estimated Value
  - Photo(s)
  - Associated Loan ID
  - Status (e.g., in vault, returned)

### 2.2. The 50% Rule

- **Business Logic:** The system must enforce that the estimated value of the collateral is at least 50% of the principal loan amount.
- **Implementation:** This rule should be checked when a new loan is created or when the collateral for an existing loan is changed. If the rule is not met, the system must prevent the operation and provide a clear error message.

## 3. User Interface & User Experience (UI/UX)

### 3.1. The Digital Shelf

- **Concept:** A visual interface that displays all collateral items as if they were on a shelf.
- **Layout:** A grid-based layout where each collateral item is represented by a card.
- **Card Content:** Each card should display a primary photo of the item, its name, and its estimated value.
- **Interaction:** Clicking on a card should open a more detailed view of the collateral item, potentially using the "Drawer UI" pattern for a smooth user experience.

### 3.2. Collateral Management in the Loan Process

- **Loan Creation:** When creating a new loan, the user should be able to add or select an existing collateral item. The 50% rule must be enforced at this stage.
- **Loan View:** When viewing a loan, the associated collateral should be clearly displayed.

## 4. Borrower's Remote (Mini-App) Integration

- **Functionality:** The mini-app for borrowers should display the photo and name of their collateral item associated with their loan.
- **Purpose:** This feature is intended to build trust with the borrower by providing transparency and assurance that their collateral is being safely tracked.

## 5. Non-Functional Requirements

- **Security:** Photos of collateral items should be stored securely. Access to collateral information should be restricted based on user roles.
- **Performance:** The "Digital Shelf" should load quickly, even with a large number of collateral items.
- **Reliability:** The 50% rule must be enforced reliably and consistently across the application.
