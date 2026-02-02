import type { Request, Response, NextFunction } from 'express';

// Use single PrismaClient from config to manage pooling and avoid connection overload
import prisma from '../config/database.ts';
import HTTP_STATUS from '../constants/httpStatus.ts';
import { LocalUserFactory } from '../factories/local-user.factory.ts';
import { perEndpointResponseTimingPolicy } from '../policies/per-endpoint-response-timing.policy.ts';
import { PrismaUserRepository } from '../repositories/index.ts';
import {
  LocalAccountSecurityService,
  LocalAuthService,
} from '../services/index.ts';
import type { RegisterRequest, LoginRequest } from '../types/auth.types.ts';

export class AuthController {
  private readonly authService;

  constructor() {
    const localUserEntityFactory = new LocalUserFactory();
    const userRepository = new PrismaUserRepository(
      prisma,
      localUserEntityFactory
    );

    this.authService = new LocalAuthService(
      new LocalAccountSecurityService(userRepository),
      perEndpointResponseTimingPolicy,
      userRepository
    );
  }

  register = async (
    req: Request<Record<string, never>, unknown, RegisterRequest>,
    res: Response,
    next: NextFunction
  ) => {
    try {
      const registerRequest = req.validatedData as RegisterRequest;
      const authResponse = await this.authService.register(registerRequest);

      res.status(HTTP_STATUS.CREATED).json(authResponse);
    } catch (error) {
      next(error);
    }
  };

  login = async (
    req: Request<Record<string, never>, unknown, LoginRequest>,
    res: Response,
    next: NextFunction
  ) => {
    try {
      const loginRequest = req.validatedData as LoginRequest;
      const authResponse = await this.authService.login(loginRequest);

      res.status(HTTP_STATUS.OK).json(authResponse);
    } catch (error) {
      next(error);
    }
  };
}

export const authController = new AuthController();
