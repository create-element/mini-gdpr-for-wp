# PHPUnit Testing for Mini WP GDPR

This directory contains PHPUnit tests for the Mini WP GDPR plugin.

## Setup

### Prerequisites

- PHP 7.4 or higher
- Composer
- MySQL/MariaDB
- SVN (for WordPress test library installation)

### Install Dependencies

```bash
composer install
```

### Install WordPress Test Library

Run the installation script to set up the WordPress testing environment:

```bash
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

**Parameters:**
1. `wordpress_test` - Test database name (will be created)
2. `root` - MySQL username
3. `''` - MySQL password (empty string if no password)
4. `localhost` - MySQL host
5. `latest` - WordPress version to test against

**Note:** The test database will be **destroyed and recreated** on each test run. Do not use a production database.

### Environment Variables

If you need to customize the WordPress test library location, set:

```bash
export WP_TESTS_DIR=/path/to/wordpress-tests-lib
```

Default location is `/tmp/wordpress-tests-lib`

## Running Tests

### Run All Tests

```bash
composer test
# or
vendor/bin/phpunit
# or
phpunit
```

### Run Specific Test File

```bash
phpunit tests/test-example.php
```

### Run with Coverage Report

```bash
phpunit --coverage-html tests/coverage
```

Then open `tests/coverage/index.html` in your browser.

### Run with Verbose Output

```bash
phpunit --verbose
```

## Writing Tests

### Test File Naming

- Test files must be in the `tests/` directory
- Test files must be named `test-*.php`
- Test class names must match the filename

### Example Test

```php
<?php

class Test_My_Feature extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        // Setup code here
    }

    public function test_something() {
        $this->assertTrue( true );
    }

    public function tearDown(): void {
        // Cleanup code here
        parent::tearDown();
    }
}
```

### Available Assertions

PHPUnit provides many assertion methods:

- `$this->assertEquals( $expected, $actual )`
- `$this->assertTrue( $condition )`
- `$this->assertFalse( $condition )`
- `$this->assertNull( $value )`
- `$this->assertFileExists( $filename )`
- `$this->assertContains( $needle, $haystack )`

See [PHPUnit documentation](https://phpunit.de/manual/9.5/en/assertions.html) for full list.

### WordPress Test Helpers

The `WP_UnitTestCase` class provides WordPress-specific methods:

- `factory()->post->create()` - Create test posts
- `factory()->user->create()` - Create test users
- `factory()->term->create()` - Create test terms
- `go_to( $url )` - Simulate page request
- `$this->expectException()` - Test exception throwing

## Test Coverage

To view coverage for specific directories:

```bash
phpunit --coverage-text --coverage-filter includes/
```

**Coverage targets:**

- Overall: >70%
- Critical classes (Settings, User_Controller): >80%
- Utility functions: >90%

## Continuous Integration

Tests run automatically on:

- Every commit (if pre-commit hook enabled)
- Every pull request
- Before release

## Troubleshooting

### "Could not find WordPress test library"

Run the installation script:

```bash
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

### "Database connection failed"

Check MySQL is running and credentials are correct:

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

### "Class not found"

Make sure the plugin is loaded in bootstrap.php and the class file is included:

```php
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
```

### Tests are slow

The WordPress test suite can be slow. To speed up:

- Use `--filter` to run specific tests
- Use `--stop-on-failure` to halt on first error
- Disable code coverage when not needed

### Permission denied on test database

Grant proper permissions:

```sql
GRANT ALL ON wordpress_test.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

## Resources

- [PHPUnit Documentation](https://phpunit.de/manual/9.5/en/index.html)
- [WordPress Unit Tests](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [WP_UnitTestCase Reference](https://developer.wordpress.org/reference/classes/wp_unittestcase/)
