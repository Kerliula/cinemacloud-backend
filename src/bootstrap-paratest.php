<?php

/**
 * ParaTest Database Bootstrap
 *
 * This file is executed for each parallel test process to ensure proper
 * database isolation and setup.
 */

// Get the test token for this process
$testToken = getenv('TEST_TOKEN');

// Laravel's test database setup is handled by RefreshDatabase/DatabaseTransactions traits
// However, we need to ensure the test database is created and migrations are run

// The database migrations will be run by Laravel's testing traits (RefreshDatabase)
// which uses transactions for isolation between tests within a process.

// For Paratest, we just need to ensure the test database exists and is ready.
// This is typically handled by the COMPOSE_TEST setup in the Makefile.

// If you need to customize behavior per process:
if ($testToken) {
    // Each process gets a unique token
    putenv("PARATEST_PROCESS_TOKEN={$testToken}");
}

