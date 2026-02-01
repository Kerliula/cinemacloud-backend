import { HTTP_STATUS } from '../constants/index.ts';
import { AppError } from '../errors/app-error.ts';
import type {
  AccountSecurityService,
  UserRepository,
  UserEntity,
} from '../interfaces/index.ts';

class LocalAccountSecurityService implements AccountSecurityService {
  private readonly MAX_FAILED_ATTEMPTS = 5;
  private readonly LOCK_DURATION_MS = 15 * 60 * 1000; // 15 minutes

  private readonly userRepository: UserRepository;

  constructor(userRepository: UserRepository) {
    this.userRepository = userRepository;
  }

  public async handleLoginAttempt(user: UserEntity): Promise<void> {
    const now = new Date();

    if (user.isLocked(now)) {
      throw new AppError(HTTP_STATUS.FORBIDDEN);
    }

    if (user.shouldReleaseLock(now)) {
      user.resetFailedAttempts();
      user.unlock();
      await this.userRepository.update(user);
    }
  }

  public async handleFailedLogin(user: UserEntity): Promise<void> {
    user.incrementFailedAttempts();

    const isExceeded = user.failedAttempts >= this.MAX_FAILED_ATTEMPTS;

    if (isExceeded) {
      const now = new Date();
      user.lock(now, this.LOCK_DURATION_MS);
    }

    await this.userRepository.update(user);
  }

  public async handleSuccessfulLogin(user: UserEntity): Promise<void> {
    const now = new Date();

    user.resetFailedAttempts();
    user.recordLogin(now);

    await this.userRepository.update(user);
  }
}

export { LocalAccountSecurityService };
