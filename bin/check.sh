#!/bin/bash
# Mini WP GDPR - Code Quality Check Script
# Run all code quality checks (PHPCS + PHPUnit)

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_ROOT"

echo "========================================="
echo "Mini WP GDPR - Code Quality Check"
echo "========================================="
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Error: Composer is not installed or not in PATH"
    exit 1
fi

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "⚠️  Vendor directory not found. Running composer install..."
    composer install --no-interaction
    echo ""
fi

# Run PHPCS
echo "📝 Running PHP CodeSniffer..."
echo "-----------------------------------------"
if composer run-script phpcs -- --report=summary; then
    echo "✅ PHPCS: No issues found"
else
    echo "❌ PHPCS: Issues found (run bin/fix.sh to auto-fix)"
    PHPCS_FAILED=1
fi
echo ""

# Run PHPUnit
echo "🧪 Running PHPUnit Tests..."
echo "-----------------------------------------"
if composer run-script test; then
    echo "✅ PHPUnit: All tests passed"
else
    echo "❌ PHPUnit: Some tests failed"
    PHPUNIT_FAILED=1
fi
echo ""

# Summary
echo "========================================="
echo "Summary"
echo "========================================="
if [ -z "$PHPCS_FAILED" ] && [ -z "$PHPUNIT_FAILED" ]; then
    echo "✅ All checks passed!"
    exit 0
else
    [ -n "$PHPCS_FAILED" ] && echo "❌ PHPCS failed"
    [ -n "$PHPUNIT_FAILED" ] && echo "❌ PHPUnit failed"
    exit 1
fi
