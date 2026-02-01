import type {
  UserEntity,
  UserData,
} from '../interfaces/user-entity.interface.ts';

// We pass the date in all methods that depend on current time to make testing easier
// also it removes dependency on system time which can be changed externally
// and helps to keep other parts of the system deterministic
class LocalUserEntity implements UserEntity {
  constructor(private props: UserData) {}

  public get data() {
    return this.props;
  }

  public get failedAttempts(): number {
    return this.props.failedLoginAttempts;
  }

  public shouldReleaseLock(currentTime: Date): boolean {
    const lockExpiry = this.props.accountLockedUntil;

    if (!lockExpiry) {
      return false;
    }

    return currentTime >= lockExpiry;
  }

  public incrementFailedAttempts(): void {
    this.props.failedLoginAttempts++;
  }

  public lock(currentTime: Date, durationMs: number): void {
    const lockUntil = new Date(currentTime.getTime() + durationMs);
    this.props.accountLockedUntil = lockUntil;
  }

  public unlock(): void {
    this.props.accountLockedUntil = null;
  }

  public resetFailedAttempts(): void {
    this.props.failedLoginAttempts = 0;
  }

  public isLocked(currentTime: Date): boolean {
    if (!this.props.accountLockedUntil) return false;
    return currentTime < this.props.accountLockedUntil;
  }

  public recordLogin(currentTime: Date): void {
    this.props.lastLoginAt = currentTime;
  }
}

export { LocalUserEntity };
