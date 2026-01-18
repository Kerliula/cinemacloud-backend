import type { Request, Response, NextFunction } from 'express';
import { z } from 'zod';

import HTTP_STATUS from '../constants/httpStatus.ts';
import AppError from '../errors/app-error.ts';

export const validate = (schema: z.ZodSchema) => {
  return (req: Request, res: Response, next: NextFunction) => {
    try {
      const validatedData = schema.parse(req.body);
      req.validatedData = validatedData;
      next();
    } catch (error) {
      if (error instanceof z.ZodError) {
        const errors = error.issues.map(err => ({
          field: err.path.join('.'),
          message: err.message,
        }));

        return next(
          new AppError(HTTP_STATUS.BAD_REQUEST, 'Validation error', true, {
            errors,
          })
        );
      } else {
        next(error);
      }
    }
  };
};
