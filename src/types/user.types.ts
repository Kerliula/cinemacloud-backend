import type { User } from '@prisma/client';

import type { Role } from './auth.types.ts';

export interface UserWithRelations extends User {
  roles: Role[];
}
export interface CreateUserInput {
  email: string;
  password: string;
  username: string;
}

export interface UpdateUserInput {
  email?: string;
  password?: string;
  username?: string;
  failedLoginAttempts?: number;
  accountLockedUntil?: Date | null;
  lastLoginAt?: Date | null;
}
