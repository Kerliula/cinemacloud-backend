import { Prisma } from '@prisma/client';

import { config } from '../config/env.ts';
import { HTTP_STATUS, PRISMA_ERROR_CODES } from '../constants/index.ts';
import AppError from '../errors/app-error.ts';
import { UserModel } from '../models/user.model.ts';
import type {
  RegisterRequest,
  AuthResponse,
  JwtPayload,
  LoginRequest,
} from '../types/auth.types.ts';
import type { UserWithRelations } from '../types/user.types.ts';
import { bcryptUtils, jwtUtils } from '../utils/index.ts';

export class AuthService {
  async register(data: RegisterRequest): Promise<AuthResponse> {
    const { email, password, username } = data;

    try {
      const hashedPassword = await bcryptUtils.hash(password);
      const user = await UserModel.create({
        email,
        password: hashedPassword,
        username,
      });

      const token = this.generateTokenForUser(user);
      return this.buildAuthResponse(user, token);
    } catch (error) {
      if (error instanceof Prisma.PrismaClientKnownRequestError) {
        if (error.code === PRISMA_ERROR_CODES.UNIQUE_CONSTRAINT_FAILED) {
          throw new AppError(HTTP_STATUS.CONFLICT, 'User already exists');
        }
      }

      if (error instanceof AppError) {
        throw error;
      }

      throw new AppError(HTTP_STATUS.INTERNAL_SERVER_ERROR);
    }
  }

  async login(data: LoginRequest): Promise<AuthResponse> {
    const { email, password } = data;

    const user = await UserModel.findByEmail(email);

    if (!user) {
      throw new AppError(HTTP_STATUS.UNAUTHORIZED, 'Invalid credentials');
    }

    const isPasswordValid = await bcryptUtils.compare(password, user.password);

    if (!isPasswordValid) {
      throw new AppError(HTTP_STATUS.UNAUTHORIZED, 'Invalid credentials');
    }

    const token = this.generateTokenForUser(user);

    return this.buildAuthResponse(user, token);
  }

  private generateTokenForUser(user: UserWithRelations): string {
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
    token: string
  ): AuthResponse {
    return {
      user: {
        id: user.id,
        email: user.email,
        username: user.username,
        roles: user.roles,
      },
      token,
    };
  }
}

export const authService = new AuthService();
