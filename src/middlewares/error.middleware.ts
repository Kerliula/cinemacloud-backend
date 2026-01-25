import type { Request, Response, NextFunction } from 'express';

import HTTP_STATUS from '../constants/httpStatus.ts';
import AppError from '../errors/app-error.ts';

export const globalErrorHandler = (
  err: Error | AppError,
  _req: Request,
  res: Response,
  _next: NextFunction
) => {
  if (err instanceof AppError) {
    const response: { message: string; details?: unknown } = {
      message: err.message,
    };

    if (err.details) {
      response.details = err.details;
    }

    return res.status(err.statusCode).json(response);
  }

  console.error(err);
  res
    .status(HTTP_STATUS.INTERNAL_SERVER_ERROR)
    .json({ message: 'Internal server error' });
};
