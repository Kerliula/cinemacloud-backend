import { Prisma } from '@prisma/client';

import { config } from '../config/env.ts';
import HTTP_STATUS from '../constants/httpStatus.ts';
import AppError from '../errors/app-error.ts';
import { UserModel } from '../models/user.model.ts';
import type { RegisterRequest, AuthResponse } from '../types/auth.types.ts';
import { bcryptUtils, jwtUtils } from '../utils/index.ts';

export class AuthService {
  async register(data: RegisterRequest): Promise<AuthResponse> {
    const { email, password, username } = data;

    try {
      const hashedPassword = await bcryptUtils.hash(password);
      const user = await UserModel.create({
        email,
        hashedPassword,
        username,
      });

      // Generate token and build response
      const token = this.generateTokenForUser(user);
      return this.buildAuthResponse(user, token);
    } catch (error) {
      // Handle Prisma unique constraint error
      if (error instanceof Prisma.PrismaClientKnownRequestError) {
        const prismaError = error as Prisma.PrismaClientKnownRequestError;
        if (prismaError.code === 'P2002') {
          throw new AppError(HTTP_STATUS.CONFLICT);
        }
      }

      // Re-throw if it's already an AppError
      if (error instanceof AppError) {
        throw error;
      }

      // Throw generic error for unexpected cases
      throw new AppError(HTTP_STATUS.INTERNAL_SERVER_ERROR);
    }
  }

  private generateTokenForUser(user: {
    id: number;
    email: string;
    username: string;
  }): string {
    const payload = {
      id: user.id,
      email: user.email,
      username: user.username,
    };

    return jwtUtils.generateToken(
      payload,
      config.jwtSecret,
      config.jwtExpiresIn
    );
  }

  private buildAuthResponse(
    user: { id: number; email: string; username: string },
    token: string
  ): AuthResponse {
    return {
      user: {
        id: user.id,
        email: user.email,
        username: user.username,
      },
      token,
    };
  }
}

export const authService = new AuthService();
