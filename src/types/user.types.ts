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
