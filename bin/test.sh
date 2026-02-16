#!/bin/bash
# Mini WP GDPR - Run PHPUnit Tests
# Run the full test suite

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_ROOT"

echo "========================================="
echo "Mini WP GDPR - PHPUnit Tests"
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

# Check if WP test suite is installed
if [ ! -f "/tmp/wordpress-tests-lib/includes/functions.php" ]; then
    echo "⚠️  WordPress test library not found."
    echo "Run: bash bin/install-wp-tests.sh wordpress_test root '' localhost latest"
    echo ""
fi

# Run PHPUnit with coverage (if requested)
if [ "$1" = "--coverage" ]; then
    echo "🧪 Running PHPUnit with code coverage..."
    echo "-----------------------------------------"
    composer run-script test -- --coverage-html coverage
    echo ""
    echo "✅ Coverage report generated in coverage/"
else
    echo "🧪 Running PHPUnit..."
    echo "-----------------------------------------"
    composer run-script test
fi

echo ""
echo "========================================="
echo "Done!"
echo "========================================="
