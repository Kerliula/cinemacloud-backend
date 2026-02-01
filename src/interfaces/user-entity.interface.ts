export interface CreateUserData {
  email: string;
  username: string;
  passwordHash: string;
  roleId: number;
}

export interface UserRoles {
  get id(): number;
  get name(): string;
  get createdAt(): Date;
}

export interface UserData {
  get id(): number;
  get email(): string;
  get username(): string;
  get passwordHash(): string;
  get failedLoginAttempts(): number;
  set failedLoginAttempts(value: number);
  set accountLockedUntil(value: Date | null);
  get accountLockedUntil(): Date | null;
  set lastLoginAt(value: Date | null);
  get lastLoginAt(): Date | null;
  get createdAt(): Date;
  get updatedAt(): Date;
  get roles(): UserRoles[];
}

export interface UserEntity {
  get data(): UserData;
  get failedAttempts(): number;
  incrementFailedAttempts(): void;
  resetFailedAttempts(): void;
  lock(currentTime: Date, durationMs: number): void;
  unlock(): void;
  isLocked(currentTime: Date): boolean;
  recordLogin(currentTime: Date): void;
  shouldReleaseLock(currentTime: Date): boolean;
}
