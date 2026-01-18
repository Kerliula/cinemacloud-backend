import type { Request, Response, NextFunction } from 'express';

import HTTP_STATUS from '../constants/httpStatus.ts';
import AppError from '../errors/app-error.ts';

export const globalErrorHandler = (
  err: Error | AppError,
  req: Request,
  res: Response,
  _next: NextFunction
) => {
  if (err instanceof AppError) {
    return res.status(err.statusCode).json();
  }

  res.status(HTTP_STATUS.INTERNAL_SERVER_ERROR).json();
};
