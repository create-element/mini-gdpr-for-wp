#!/bin/bash
# Mini WP GDPR - Auto-fix Code Style Issues
# Run PHP Code Beautifier and Fixer (PHPCBF) to automatically fix coding standard violations

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_ROOT"

echo "========================================="
echo "Mini WP GDPR - Auto-fix Code Style"
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

# Run PHPCBF
echo "🔧 Running PHP Code Beautifier and Fixer..."
echo "-----------------------------------------"
if composer run-script phpcbf; then
    echo "✅ PHPCBF: All issues fixed"
else
    # PHPCBF exits with code 1 when it fixes issues, which is expected
    if [ $? -eq 1 ]; then
        echo "✅ PHPCBF: Fixed some issues"
        echo ""
        echo "💡 Run bin/check.sh to verify remaining issues"
    else
        echo "❌ PHPCBF: Unexpected error"
        exit 1
    fi
fi
echo ""
echo "========================================="
echo "Done!"
echo "========================================="
