import { Prisma, User } from '@prisma/client';

import { config } from '../config/env.ts';
import { HTTP_STATUS, PRISMA_ERROR_CODES } from '../constants/index.ts';
import {
  AppError,
  InvalidPasswordError,
  EntityNotFoundError,
} from '../errors/index.ts';
import type {
  AccountSecurityService,
  AuthService,
  ResponseTimingPolicy,
  UserRepository,
  UserEntity,
} from '../interfaces/index.ts';
import type {
  RegisterRequest,
  AuthResponse,
  JwtPayload,
  LoginRequest,
} from '../types/auth.types.ts';
import { bcryptUtils, jwtUtils } from '../utils/index.ts';

export class LocalAuthService implements AuthService {
  private readonly accountSecurityService: AccountSecurityService;
  private readonly responseTimingPolicy: ResponseTimingPolicy;
  private readonly userRepository: UserRepository;

  constructor(
    accountSecurityService: AccountSecurityService,
    responseTimingPolicy: ResponseTimingPolicy,
    userRepository: UserRepository
  ) {
    this.accountSecurityService = accountSecurityService;
    this.responseTimingPolicy = responseTimingPolicy;
    this.userRepository = userRepository;
  }

  public async register(data: RegisterRequest): Promise<AuthResponse> {
    const { email, password, username } = data;

    try {
      const passwordHash = await bcryptUtils.hash(password);

      const newUser = await this.userRepository.create({
        email,
        username,
        passwordHash: passwordHash,
        roleId: 1,
      });

      const accessToken = this.generateAccessTokenForUser(newUser);

      return this.buildAuthResponse(newUser, accessToken);
    } catch (error) {
      this.handleRegistrationError(error);
    }
  }

  public async login(data: LoginRequest): Promise<AuthResponse> {
    const { email, password } = data;
    const startTime = Date.now();

    let user: UserEntity | undefined;

    try {
      user = await this.userRepository.findByEmailOrFail(email);

      // Guards
      await this.accountSecurityService.handleLoginAttempt(user);
      await this.ensurePasswordIsValid(password, user);

      // Success path
      await this.accountSecurityService.handleSuccessfulLogin(user);
      const accessToken = this.generateAccessTokenForUser(user);

      return this.buildAuthResponse(user, accessToken);
    } catch (error) {
      await this.handleAuthError(error, user);
    } finally {
      await this.responseTimingPolicy.enforce(startTime, 'login');
    }

    throw new Error('Unreachable code');
  }

  private async ensurePasswordIsValid(
    password: string,
    user: UserEntity
  ): Promise<void> {
    const isPasswordValid = await bcryptUtils.compare(
      password,
      user.data.passwordHash
    );

    if (!isPasswordValid) {
      throw new InvalidPasswordError();
    }
  }

  private async handleAuthError(
    error: unknown,
    user: UserEntity | undefined
  ): Promise<never> {
    if (error instanceof InvalidPasswordError && user) {
      await this.accountSecurityService.handleFailedLogin(user);
      throw new AppError(HTTP_STATUS.UNAUTHORIZED);
    }
    if (error instanceof EntityNotFoundError) {
      throw new AppError(HTTP_STATUS.UNAUTHORIZED);
    }
    if (error instanceof AppError) throw error;

    throw new AppError(HTTP_STATUS.INTERNAL_SERVER_ERROR);
  }

  private handleRegistrationError(error: unknown): never {
    if (error instanceof AppError) throw error;

    // Prisma unique constraint violation
    if (
      error instanceof Prisma.PrismaClientKnownRequestError &&
      error.code === PRISMA_ERROR_CODES.UNIQUE_CONSTRAINT_FAILED
    ) {
      throw new AppError(HTTP_STATUS.CONFLICT, 'This email is already in use');
    }

    throw new AppError(HTTP_STATUS.INTERNAL_SERVER_ERROR);
  }

  private generateAccessTokenForUser(user: UserEntity): string {
    const payload: JwtPayload = {
      id: user.data.id,
    };

    return jwtUtils.generateToken(
      payload,
      config.jwtSecret,
      config.jwtExpiresIn
    );
  }

  private buildAuthResponse(
    user: UserEntity,
    accessToken: string
  ): AuthResponse {
    return {
      user: {
        id: user.data.id,
        email: user.data.email,
        username: user.data.username,
        roles: user.data.roles,
      },
      accessToken,
    };
  }
}

export const localAuthService = (
  accountSecurityService: AccountSecurityService,
  responseTimingPolicy: ResponseTimingPolicy,
  userRepository: UserRepository
) =>
  new LocalAuthService(
    accountSecurityService,
    responseTimingPolicy,
    userRepository
  );
