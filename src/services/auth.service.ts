import { config } from '../config/env.ts';
import HTTP_STATUS from '../constants/httpStatus.ts';
import AppError from '../errors/app-error.ts';
import { UserModel } from '../models/user.model.ts';
import type { RegisterRequest, AuthResponse } from '../types/auth.types.ts';
import { bcryptUtils, jwtUtils } from '../utils/index.ts';

export class AuthService {
  async register(data: RegisterRequest): Promise<AuthResponse> {
    const { email, password, username } = data;

    const existingUser = await UserModel.findByEmail(email);
    if (existingUser) {
      throw new AppError(
        HTTP_STATUS.CONFLICT,
        'User with this email already exists'
      );
    }

    const hashedPassword = await bcryptUtils.hash(password);
    const user = await UserModel.create({
      email,
      hashedPassword,
      username,
    });

    const token = this.generateTokenForUser(user);

    return this.buildAuthResponse(user, token);
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
