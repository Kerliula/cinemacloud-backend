import { HTTP_STATUS } from '../constants/index.ts';
import { AppError } from '../errors/app-error.ts';
import type {
  AccountSecurityService,
  UserRepository,
} from '../interfaces/index.ts';
import { UserModel } from '../models/user.model.ts';
import type { UserWithRelations } from '../types/user.types.ts';

export class LocalAccountSecurityService implements AccountSecurityService {
  private readonly userRepository: UserRepository;

  constructor(userRepository: UserRepository) {
    this.userRepository = userRepository;
  }

  private readonly MAX_FAILED_ATTEMPTS = 5;
  private readonly LOCK_DURATION_MS = 15 * 60 * 1000; // 15 minutes

  public async handleLoginAttempt(user: UserWithRelations): Promise<void> {
    if (await UserModel.isAccountStillLocked(user.id)) {
      throw new AppError(HTTP_STATUS.FORBIDDEN);
    } else {
      const accountWasLocked = !!user.accountLockedUntil;
      if (accountWasLocked) {
        await UserModel.resetFailedAttempts(user.id);
        await UserModel.removeAccountLock(user.id);
      }
    }
  }

  public async handleFailedLogin(user: UserWithRelations): Promise<void> {
    await UserModel.incrementFailedAttempts(user.id);

    const updatedUser = await this.userRepository.findByIdOrFail(user.id);

    if (updatedUser.failedLoginAttempts >= this.MAX_FAILED_ATTEMPTS) {
      await UserModel.lockAccount(user.id, this.LOCK_DURATION_MS);
    }
  }

  public async handleSuccessfulLogin(user: UserWithRelations): Promise<void> {
    await UserModel.resetFailedAttempts(user.id);
    await UserModel.markLogin(user.id);
  }
}

export const localAccountSecurityService = (userRepository: UserRepository) =>
  new LocalAccountSecurityService(userRepository);
