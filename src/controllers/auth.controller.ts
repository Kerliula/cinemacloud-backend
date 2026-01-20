import type { Request, Response, NextFunction } from 'express';

import HTTP_STATUS from '../constants/httpStatus.ts';
import { authService } from '../services/auth.service.ts';
import type { RegisterRequest, LoginRequest } from '../types/auth.types.ts';

export class AuthController {
  async register(
    req: Request<Record<string, never>, unknown, RegisterRequest>,
    res: Response,
    next: NextFunction
  ) {
    try {
      const validatedData = req.validatedData as RegisterRequest;
      const result = await authService.register(validatedData);

      res.status(HTTP_STATUS.CREATED).json(result);
    } catch (error) {
      next(error);
    }
  }

  async login(
    req: Request<Record<string, never>, unknown, LoginRequest>,
    res: Response,
    next: NextFunction
  ) {
    try {
      const validatedData = req.validatedData as LoginRequest;

      const result = await authService.login(validatedData);
      res.status(HTTP_STATUS.OK).json(result);
    } catch (error) {
      next(error);
    }
  }
}

export const authController = new AuthController();
