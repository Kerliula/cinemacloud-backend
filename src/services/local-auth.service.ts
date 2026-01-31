import { Prisma } from '@prisma/client';

import { config } from '../config/env.ts';
import { HTTP_STATUS, PRISMA_ERROR_CODES } from '../constants/index.ts';
import {
  AppError,
  InvalidPasswordError,
  UserNotFoundError,
} from '../errors/index.ts';
import type {
  AccountSecurityService,
  AuthService,
  ResponseTimingPolicy,
  UserRepository,
} from '../interfaces/index.ts';
import { UserModel } from '../models/user.model.ts';
import type {
  RegisterRequest,
  AuthResponse,
  JwtPayload,
  LoginRequest,
} from '../types/auth.types.ts';
import type { UserWithRelations } from '../types/user.types.ts';
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

      // TODO: Place in repository
      const newUser = await UserModel.create({
        email,
        password: passwordHash,
        username,
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

    let user: UserWithRelations | undefined;

    try {
      user = await this.userRepository.findByEmail(email);

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
    user: UserWithRelations
  ): Promise<void> {
    const isPasswordValid = await bcryptUtils.compare(password, user.password);

    if (!isPasswordValid) {
      throw new InvalidPasswordError();
    }
  }

  private async handleAuthError(
    error: unknown,
    user: UserWithRelations | undefined
  ): Promise<never> {
    if (error instanceof InvalidPasswordError && user) {
      await this.accountSecurityService.handleFailedLogin(user);
      throw new AppError(HTTP_STATUS.UNAUTHORIZED);
    }
    if (error instanceof UserNotFoundError) {
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

  private generateAccessTokenForUser(user: UserWithRelations): string {
    const payload: JwtPayload = {
      id: user.id,
    };

    return jwtUtils.generateToken(
      payload,
      config.jwtSecret,
      config.jwtExpiresIn
    );
  }

  private buildAuthResponse(
    user: UserWithRelations,
    accessToken: string
  ): AuthResponse {
    return {
      user: {
        id: user.id,
        email: user.email,
        username: user.username,
        roles: user.roles,
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
