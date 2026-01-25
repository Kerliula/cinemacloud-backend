import { type UserWithRelations } from '../types/user.types.ts';

export interface AccountSecurityService {
  handleLoginAttempt(user: UserWithRelations): Promise<void>;
  handleFailedLogin(user: UserWithRelations): Promise<void>;
  handleSuccessfulLogin(user: UserWithRelations): Promise<void>;
}
