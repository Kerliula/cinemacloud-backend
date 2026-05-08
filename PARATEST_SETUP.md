# Paratest Setup Guide

Paratest is configured and ready to use in this project. It allows running PHPUnit tests in parallel for faster test execution.

## Quick Start

### Using Docker (Recommended)

Run all tests in parallel with Paratest:
```bash
make test-paratest
```

Run tests with code coverage:
```bash
make test-paratest-coverage
```

Run tests in verbose mode for detailed output:
```bash
make test-paratest-verbose
```

### Using Composer (Local Development)

From the `src/` directory:

```bash
# Run all tests in parallel
composer run test:paratest

# Run tests with code coverage
composer run test:paratest:coverage

# Run tests in verbose mode
composer run test:paratest:verbose
```

### Direct Paratest Commands

For advanced usage, run Paratest directly:

```bash
# Basic parallel test run
./vendor/bin/paratest

# Run specific test suite
./vendor/bin/paratest --testsuite=Unit
./vendor/bin/paratest --testsuite=Feature

# Run with specific number of processes
./vendor/bin/paratest --processes=4

# Run with code coverage
./vendor/bin/paratest --coverage-html coverage

# Run with verbose output
./vendor/bin/paratest --verbose

# Run with filter
./vendor/bin/paratest --filter=TestClassName

# Run with stop-on-failure
./vendor/bin/paratest --stop-on-failure
```

## Configuration

### Configuration Files

- **phpunit.xml** - Standard PHPUnit configuration (used when running single-threaded)
- **paratest.xml** - Paratest-specific configuration (optimized for parallel execution)

### Environment Variables

Paratest respects the same environment variables as PHPUnit. Key variables are configured in the XML files:

- `APP_ENV=testing` - Sets Laravel to testing mode
- `DB_CONNECTION=mysql` - Uses MySQL for testing
- `CACHE_STORE=array` - Uses array cache during tests
- `QUEUE_CONNECTION=sync` - Runs queues synchronously
- `JWT_SECRET` - Test JWT secret for authentication tests

## Default Behavior

- **Parallel Processes**: Auto-detects optimal number based on CPU cores
- **Database Isolation**: Each process gets its own database connection
- **Coverage Reports**: Generated in `coverage/` directory
- **Test Output**: Colored output by default

## Common Issues & Solutions

### "Could not connect to debugging client"
This is just a warning from Xdebug. Fix by disabling debug mode:
```bash
XDEBUG_MODE=off make test-paratest
```

### Tests are not isolated between processes
Ensure your test database is properly seeded in each process:
```bash
php artisan migrate:fresh --force
```

### Out of memory errors
Reduce the number of parallel processes:
```bash
./vendor/bin/paratest --processes=2
```

### Database locking issues
Use SQLite for testing or ensure proper database locking configuration:
- Update `DB_CONNECTION` to `sqlite` in test environment
- Or ensure your MySQL is configured to handle concurrent connections

## Performance Tips

1. **Run only changed tests during development**:
   ```bash
   ./vendor/bin/paratest --filter=YourTestName
   ```

2. **Increase concurrency for better performance**:
   ```bash
   ./vendor/bin/paratest --processes=8
   ```

3. **Disable code coverage if not needed**:
   ```bash
   ./vendor/bin/paratest  # faster than with --coverage-html
   ```

4. **Profile slow tests**:
   ```bash
   ./vendor/bin/paratest --verbose
   ```

## Additional Resources

- [Paratest Documentation](https://github.com/paratestphp/paratest)
- [PHPUnit Documentation](https://phpunit.de/)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)

