---
name: improvements
description: Guides the implementation of suggested architectural improvements, optimizations, and technical debt reduction for Analyt Loan.
---

# Codebase Improvements & Optimization Guide

This skill guides the implementation of suggested architectural improvements for the Analyt Loan codebase. Use this when implementing performance optimizations, refactoring financial logic, or upgrading safety constraints.

## Suggested Improvements Catalog
The detailed checklist of recommended improvements can be found in the references directory:
[suggested_improvements.md](file:///root/Analyt-Loan/.agents/skills/improvements/references/suggested_improvements.md)

## Implementing Improvements

When tasked with implementing any of the improvements:
1. **Ensure Financial Precision:** Always use the [Money](file:///root/Analyt-Loan/app/ValueObjects/Money.php) value object for calculations. Never perform raw float math on currency fields.
2. **Adhere to Tenancy Scopes:** Maintain the organizational tenancy enforced by [BelongsToOrganization](file:///root/Analyt-Loan/app/Traits/BelongsToOrganization.php).
3. **Run Quality Checks:** Execute the quality verification script (defined in the `quality_check` skill) to ensure Pint lints, PHPStan analyses, and PHPUnit tests pass successfully.
