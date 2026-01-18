import type { User as PrismaUser } from '@prisma/client';

import type { Role } from './auth.types.ts';

// User without password
export type UserPublic = Omit<PrismaUser, 'password'>;

// User with relations
export interface UserWithRelations extends UserPublic {
  roles: Role[];
}

// User creation input
export interface CreateUserInput {
  email: string;
  password: string;
  username: string;
}
