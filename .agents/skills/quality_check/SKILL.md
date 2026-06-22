---
name: quality_check
description: Runs codebase quality checks including code style linting (Pint), static analysis (PHPStan), and feature/unit testing (PHPUnit).
---

# Quality Check Skill

This skill automates testing, code-style verification, and static analysis using the custom script inside this skill.

## Triggering Checks

To run lints, analysis, and tests, execute the script located at:
[run_checks.sh](file:///root/Analyt-Loan/.agents/skills/quality_check/scripts/run_checks.sh)

## Check Suite Summary

The check suite executes the following commands:
1.  **Pint (Code Style Linter):** Checks formatting and style standards (runs `./vendor/bin/pint --test`).
2.  **PHPStan (Static Analysis):** Validates strict typing and parameter types (runs `./vendor/bin/phpstan analyse --memory-limit=1G`).
3.  **PHPUnit (Testing Suite):** Runs functional integration and unit test scenarios (runs `php artisan test`).
