-- AlterTable
ALTER TABLE `users` ADD COLUMN `accountLockedUntil` DATETIME(3) NULL,
    ADD COLUMN `failedLoginAttempts` INTEGER NOT NULL DEFAULT 0,
    ADD COLUMN `lastLoginAt` DATETIME(3) NULL;
