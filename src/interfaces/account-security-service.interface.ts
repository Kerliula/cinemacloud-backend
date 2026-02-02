import type { UserEntity } from './user-entity.interface.ts';

export interface AccountSecurityService {
  handleLoginAttempt(user: UserEntity): Promise<void>;
  handleFailedLogin(user: UserEntity): Promise<void>;
  handleSuccessfulLogin(user: UserEntity): Promise<void>;
}
