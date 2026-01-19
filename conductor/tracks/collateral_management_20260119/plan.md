# Implementation Plan: Collateral Management (Vault) Feature

This plan outlines the steps to implement the "Collateral Management (Vault)" feature, including the 50% Rule and the Digital Shelf UI. The tasks are structured following a Test-Driven Development (TDD) approach, with automated tests preceding implementation.

---

## Phase 1: Backend Setup & Logic [checkpoint: 4b9eeb9]

This phase focuses on establishing the core data model, API endpoints, and business logic for collateral management.

- [x] **Task: Create Collateral Database Migration** [53be70b]
    - [ ] Write tests for the `collaterals` table structure, ensuring all required fields (name, description, value, image path, loan_id, status) are present with correct types and constraints.
    - [ ] Implement the migration to create the `collaterals` table in the database.
    - [ ] Run and verify tests.

- [x] **Task: Develop Collateral Eloquent Model** [47cadf8]
    - [ ] Write tests for the `Collateral` Eloquent model, including mass assignment protection, fillable properties, and relationships (e.g., `belongsTo` a `Loan`).
    - [ ] Implement the `Collateral` model with its attributes and relationships.
    - [ ] Run and verify tests.

- [x] **Task: Establish Loan-Collateral Relationship** [47cadf8]
    - [ ] Write tests to ensure a `Loan` can `haveOne` or `hasMany` `Collateral` and vice-versa.
    - [ ] Implement the relationship methods in `Loan` and `Collateral` models.
    - [ ] Run and verify tests.

- [x] **Task: Implement Collateral CRUD API Endpoints** [cc86931]
    - [ ] Write tests for API endpoints (e.g., `POST /api/collaterals`, `GET /api/collaterals/{id}`, `PUT /api/collaterals/{id}`, `DELETE /api/collaterals/{id}`) to manage collateral items.
    - [ ] Implement the API routes and controller methods for CRUD operations, ensuring proper authorization and validation.
    - [ ] Run and verify tests.

- [x] **Task: Develop 50% Rule Business Logic** [d3040cb]
    - [ ] Write tests for the `50% Rule` logic. This should cover scenarios where the rule passes and fails (e.g., collateral value < 50% of loan amount).
    - [ ] Implement the `50% Rule` logic, likely as a validation rule or a service method, to be invoked when creating or updating loans with collateral.
    - [ ] Run and verify tests.

- [ ] **Task: Conductor - User Manual Verification 'Phase 1: Backend Setup & Logic' (Protocol in workflow.md)**

## Phase 2: Frontend UI/UX Implementation

This phase focuses on building the user-facing components for managing and viewing collateral.

- [x] **Task: Create Digital Shelf Livewire Component** [bb7b535]
    - [ ] Write tests for the Livewire component that displays a grid of collateral items, ensuring it fetches and renders data correctly.
    - [ ] Implement the Livewire component for the "Digital Shelf" UI, including fetching collateral data and displaying it in a grid of cards.
    - [ ] Run and verify tests.

- [x] **Task: Develop Collateral Card UI** [af04bb2]
    - [ ] Write tests for the individual collateral card component, ensuring it displays the image, name, and estimated value correctly.
    - [ ] Implement the Blade view for a single collateral card, including styling with Tailwind CSS.
    - [ ] Run and verify tests.

- [x] **Task: Implement Collateral Detail Drawer UI** [92af70c]
    - [ ] Write tests for the "Drawer UI" that slides out from the right, ensuring it displays detailed collateral information upon card click.
    - [ ] Implement the Livewire/Alpine.js components for the collateral detail drawer, including data fetching and display.
    - [ ] Run and verify tests.

- [ ] **Task: Integrate Collateral Management into Loan Creation/Edit Forms**
    - [ ] Write tests for the loan creation/edit forms, ensuring users can associate collateral and the 50% rule is visually communicated (e.g., warnings, disable submit).
    - [ ] Modify existing loan creation/edit forms to include the ability to add/select collateral items, integrating the 50% rule validation feedback.
    - [ ] Run and verify tests.

- [ ] **Task: Integrate Collateral Display into Loan View Page**
    - [ ] Write tests for the loan view page, ensuring the associated collateral (if any) is clearly displayed.
    - [ ] Modify the loan view page to show details of the associated collateral.
    - [ ] Run and verify tests.

- [ ] **Task: Develop Borrower's Remote (Mini-App) Collateral View**
    - [ ] Write tests for the mini-app component that displays the borrower's collateral, ensuring it shows the photo and name.
    - [ ] Implement the mini-app view for borrowers to see their collateral details.
    - [ ] Run and verify tests.

- [ ] **Task: Conductor - User Manual Verification 'Phase 2: Frontend UI/UX Implementation' (Protocol in workflow.md)**

## Phase 3: Integration & Finalization

This phase focuses on end-to-end testing, documentation, and ensuring the feature is ready for deployment.

- [ ] **Task: Conduct End-to-End Testing for Collateral Feature**
    - [ ] Write comprehensive feature tests that simulate user flows for creating loans with collateral, enforcing the 50% rule, viewing collateral, and checking the mini-app display.
    - [ ] Perform end-to-end testing to ensure all components of the collateral management feature work seamlessly together.
    - [ ] Run and verify tests.

- [ ] **Task: Update User Documentation**
    - [ ] Document the new collateral management feature for both internal administrators and borrowers (mini-app).
    - [ ] Review and update any relevant user manuals or help guides.
    - [ ] Run and verify tests.

- [ ] **Task: Conductor - User Manual Verification 'Phase 3: Integration & Finalization' (Protocol in workflow.md)**
