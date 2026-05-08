# ParaTest Database Isolation Setup Guide

## Overview

This project now has a complete **parallel database isolation setup** for running tests with Paratest. The setup ensures that tests running in parallel processes don't interfere with each other.

## Architecture

### Three-Layer Isolation Strategy

```
Layer 1: Pre-Test Database Setup (migrate:fresh)
  └─ Clears test database
  └─ Runs all migrations
  └─ Ensures clean starting state

Layer 2: Per-Process Isolation (RefreshDatabase trait)
  └─ Each test wrapped in transaction
  └─ Automatic rollback after test
  └─ No data leakage between tests

Layer 3: Multi-Process Parallelization (Paratest)
  └─ Spawns N worker processes
  └─ Each process runs assigned test batch
  └─ Processes share same test database via transactions
```

## Configuration Files

### 1. **src/bootstrap-paratest.php** - Process Bootstrap
Loaded once per parallel process to:
- Initialize the test environment
- Store process token (unique per process)
- (Optional) Enable database-level isolation

**Key Features:**
```php
$testToken = getenv('TEST_TOKEN');  // Unique per process
// Can be used for database naming, logging, etc.
```

### 2. **src/paratest.xml** - Paratest Configuration
PHPUnit configuration specifically for Paratest:
- References the bootstrap file
- Same test suite definitions as phpunit.xml
- Optimized settings for parallel execution

**Key Attributes:**
```xml
<phpunit bootstrap="bootstrap-paratest.php" ...>
```

### 3. **src/tests/Paratest/DatabaseHook.php** - Database Lifecycle
Helper class for database operations:
- `beforeProcess()` - Called before each process
- `afterProcess()` - Called after each process
- `getProcessDatabaseName()` - Generate isolated database names
- `isIsolatedDatabaseEnabled()` - Check isolation status

## How Parallel Database Isolation Works

### Current Setup (Recommended - Shared Database with Transactions)

```
Test Database: cinemacloud_test (single database)

Process 1              Process 2              Process 3
├─ Connection 1       ├─ Connection 2       ├─ Connection 3
├─ BEGIN              ├─ BEGIN              ├─ BEGIN
├─ Run TestA          ├─ Run TestD          ├─ Run TestG
├─ ROLLBACK           ├─ ROLLBACK           ├─ ROLLBACK
├─ BEGIN              ├─ BEGIN              ├─ BEGIN
├─ Run TestB          ├─ Run TestE          ├─ Run TestH
└─ ROLLBACK           └─ ROLLBACK           └─ ROLLBACK
```

**Advantages:**
- ✓ No database duplication needed
- ✓ Shared migrations run once
- ✓ Fast setup and teardown
- ✓ Proven approach with Laravel testing

**When to Use:**
- Standard Laravel application
- Tests use `RefreshDatabase` trait
- MySQL can handle concurrent connections

### Alternative Setup (Database-Level Isolation)

If you need separate databases per process:

1. **Enable in bootstrap-paratest.php**:
```php
// Uncomment these lines:
$dbName = getenv('DB_DATABASE');
if ($dbName && $testToken) {
    $isolatedDbName = $dbName . '_' . $testToken;
    putenv("DB_DATABASE={$isolatedDbName}");
}
```

2. **Pre-create test databases**:
```bash
# Create databases for each process
for i in {1..4}; do
    mysql -u cinemacloud -psecret -e "CREATE DATABASE cinemacloud_test_$i;"
done
```

3. **Benefits**:
- ✓ Complete database isolation
- ✓ No transaction overhead
- ✓ No concurrency locks

4. **Drawbacks**:
- ✗ Requires multiple databases
- ✗ Longer setup time (migrate for each DB)
- ✗ More disk space usage

## Using in Tests

### Required: RefreshDatabase Trait

All database-dependent tests must use `RefreshDatabase`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YourFeatureTest extends TestCase
{
    use RefreshDatabase;  // ← REQUIRED for proper isolation
    
    public function test_example(): void
    {
        // Each test runs in its own transaction
        // Database is rolled back after this test
        $this->postJson('/api/endpoint', [])
            ->assertStatus(201);
    }
}
```

### Optional: Custom Setup/Teardown

```php
class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear shared state for parallel testing
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\RateLimiter::clear('ip:127.0.0.1');
        
        // Any other setup
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Any cleanup
    }
}
```

## Running Tests

### Via Docker (Recommended)

```bash
# Run tests in parallel with automatic database setup
make test-paratest

# Run with coverage report
make test-paratest-coverage

# Run in verbose mode for debugging
make test-paratest-verbose
```

Behind the scenes:
```bash
# The Make target runs:
php artisan migrate:fresh --force --quiet && vendor/bin/paratest
```

### Via Composer (Local Development)

```bash
cd src/

# All scripts automatically run migrate:fresh first
composer run test:paratest
composer run test:paratest:coverage
composer run test:paratest:verbose
```

### Direct Commands

```bash
cd src/

# Manual setup + run
php artisan migrate:fresh --force

# Then run tests
./vendor/bin/paratest
./vendor/bin/paratest --processes=4
./vendor/bin/paratest --testsuite=Feature
./vendor/bin/paratest --filter=AuthControllerTest
```

## Database Setup Process

### Step-by-Step Execution

1. **Initial Setup** (Makefile/Composer Script)
   ```bash
   php artisan migrate:fresh --force
   # - Drops all tables
   # - Re-runs all migrations
   # - Database is now clean and ready
   ```

2. **ParaTest Spawns Processes**
   ```bash
   vendor/bin/paratest
   # - Detects number of CPU cores
   # - Spawns N worker processes
   # - Distributes tests across processes
   ```

3. **Each Process Setup** (bootstrap-paratest.php)
   ```php
   // Loaded once per process
   $testToken = getenv('TEST_TOKEN');
   // Initialize process-specific settings
   ```

4. **Each Test Execution** (RefreshDatabase trait)
   ```php
   // Before test
   BEGIN TRANSACTION;
   // Run test with database writes
   // After test
   ROLLBACK;  // ← All changes undone
   ```

5. **Process Completion**
   ```php
   // DatabaseHook::afterProcess() called
   // Process exits cleanly
   ```

## Troubleshooting

### ❌ "Could not connect to debugging client"
**Cause**: Xdebug warning (not an error)  
**Fix**: Always set `XDEBUG_MODE=off` in test commands  
**Status**: Already configured in Makefile and composer scripts

### ❌ Tests fail only in parallel, but pass individually
**Cause**: Missing `RefreshDatabase` trait  
**Fix**: Add `use RefreshDatabase;` to test class  
**Verify**:
```bash
# Test individually (passes)
./vendor/bin/paratest --filter=YourTest

# Test in parallel (fails)
./vendor/bin/paratest
```

### ❌ "Table already exists" error
**Cause**: Database wasn't cleared before tests  
**Fix**: Ensure `migrate:fresh` runs before Paratest:
```bash
# Makefile does this automatically:
php artisan migrate:fresh --force && vendor/bin/paratest

# Or manually:
cd src/
php artisan migrate:fresh --force
./vendor/bin/paratest
```

### ❌ Database lock / "Deadlock found" error
**Cause**: Concurrent modifications on shared database  
**Solution 1**: Ensure `RefreshDatabase` uses transactions
```php
class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use RefreshDatabase;  // Uses transactions by default
}
```

**Solution 2**: Reduce concurrency
```bash
./vendor/bin/paratest --processes=2
```

**Solution 3**: Use SQLite (if configured)
```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:  # Or a file path
```

### ❌ Out of memory errors
**Cause**: Too many processes for available RAM  
**Fix**: Reduce process count:
```bash
./vendor/bin/paratest --processes=2
```

### ❌ Some tests not running in parallel
**Cause**: Tests don't use `RefreshDatabase` (unit tests), or static data issues  
**Fix**: Check which tests are parallelizable:
```bash
./vendor/bin/paratest --verbose
```

## Performance Optimization

### 1. Find Optimal Process Count
```bash
# Default: Auto-detects (CPU cores)
./vendor/bin/paratest

# Explicit: Test different values
./vendor/bin/paratest --processes=4   # Fewer = lower memory
./vendor/bin/paratest --processes=8   # More = faster if CPU available
./vendor/bin/paratest --processes=1   # Debug mode (serial)
```

### 2. Skip Coverage When Not Needed
```bash
# Much faster without coverage
./vendor/bin/paratest

# Only use when needed:
./vendor/bin/paratest --coverage-html coverage
```

### 3. Run Only Changed Tests
```bash
# During development, run specific test
./vendor/bin/paratest --filter=AuthControllerTest
./vendor/bin/paratest --filter=testLoginSucceeds

# Much faster feedback loop
```

### 4. Use Functional Mode for Data-Heavy Tests
```bash
# Parallelizes by test data sets instead of files
./vendor/bin/paratest --functional
```

## Database Isolation Advanced Topics

### Monitoring Parallel Execution

```bash
# Verbose output shows process assignments
./vendor/bin/paratest --verbose

# Example output:
# [ProcessX] Running TestClass::testMethod
# Multiple processes run simultaneously
```

### Custom Test Tokens Per Process

The `TEST_TOKEN` environment variable is automatically set by Paratest:
```php
// In bootstrap-paratest.php
$testToken = getenv('TEST_TOKEN');  // e.g., "1", "2", "3"

// Use for logging
error_log("Test running in process: " . $testToken);

// Or for custom database setup
$dbName = 'test_' . $testToken;
```

### Verifying Isolation

Create a test to verify transactions work:

```php
public function test_database_isolation(): void
{
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    
    // After test, transaction rolls back
    // User is NOT in database anymore
}

// Run twice - both should pass despite creating same user
./vendor/bin/paratest --filter=test_database_isolation
```

## Summary

✅ **Setup Complete!**

Your project now has:
- ✓ **Database isolation** via RefreshDatabase trait
- ✓ **Process isolation** via Paratest parallel execution
- ✓ **Automated setup** via migrate:fresh before tests
- ✓ **Bootstrap hooks** for custom initialization
- ✓ **Configuration files** optimized for parallel testing
- ✓ **Make targets** for easy test running
- ✓ **Composer scripts** for local development

**Next Steps:**
1. Ensure all Feature tests use `RefreshDatabase` trait
2. Run tests: `make test-paratest`
3. Monitor performance and adjust process count
4. Use database hooks if needed for custom setup

**Key Files:**
- `src/bootstrap-paratest.php` - Process initialization
- `src/paratest.xml` - Paratest configuration
- `src/tests/Paratest/DatabaseHook.php` - Database lifecycle helpers
- `Makefile` - Make targets (test-paratest)
- `src/composer.json` - Composer scripts (test:paratest)

