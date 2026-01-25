import HTTP_STATUS from '../constants/httpStatus.ts';

import { AppError } from './app-error.ts';

export class InvalidPasswordError extends AppError {
  constructor() {
    super(HTTP_STATUS.UNAUTHORIZED, 'Invalid email or password');
    this.name = 'InvalidPasswordError';
  }
}
