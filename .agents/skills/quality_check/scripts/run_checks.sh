#!/bin/bash

# Ensure we are in the root directory of the application
cd "$(dirname "$0")/../../../.." || exit 1

echo "=========================================="
echo "    Running Analyt Loan Quality Checks    "
echo "=========================================="

# 1. Code Linting (Pint)
echo ""
echo "[1/3] Running Pint Code Style Checks..."
./vendor/bin/pint --test
PINT_STATUS=$?

if [ $PINT_STATUS -eq 0 ]; then
    echo "✔ Pint Checks Passed."
else
    echo "✘ Pint Checks Failed. Run './vendor/bin/pint' to auto-fix code styling."
fi

# 2. Static Analysis (PHPStan)
echo ""
echo "[2/3] Running PHPStan Analysis (Level 5)..."
./vendor/bin/phpstan analyse --memory-limit=1G
PHPSTAN_STATUS=$?

if [ $PHPSTAN_STATUS -eq 0 ]; then
    echo "✔ PHPStan Analysis Passed."
else
    echo "✘ PHPStan Analysis Failed."
fi

# 3. Test Suite (PHPUnit)
echo ""
echo "[3/3] Running PHPUnit Test Suite..."
php artisan test
PHPUNIT_STATUS=$?

if [ $PHPUNIT_STATUS -eq 0 ]; then
    echo "✔ PHPUnit Test Suite Passed."
else
    echo "✘ PHPUnit Test Suite Failed."
fi

echo ""
echo "=============================="
echo "           SUMMARY            "
echo "=============================="

# Print Summary Status
if [ $PINT_STATUS -eq 0 ]; then
    echo "1. Pint:   PASSED"
else
    echo "1. Pint:   FAILED"
fi

if [ $PHPSTAN_STATUS -eq 0 ]; then
    echo "2. PHPStan: PASSED"
else
    echo "2. PHPStan: FAILED"
fi

if [ $PHPUNIT_STATUS -eq 0 ]; then
    echo "3. PHPUnit: PASSED"
else
    echo "3. PHPUnit: FAILED"
fi

# Exit with non-zero if any check failed
if [ $PINT_STATUS -eq 0 ] && [ $PHPSTAN_STATUS -eq 0 ] && [ $PHPUNIT_STATUS -eq 0 ]; then
    echo ""
    echo "🎉 All checks passed! Ready to commit."
    exit 0
else
    echo ""
    echo "🚨 Some checks failed. Please fix the errors before committing."
    exit 1
fi
