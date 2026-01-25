import HTTP_STATUS from '../constants/httpStatus.ts';

import { AppError } from './app-error.ts';

export class UserNotFoundError extends AppError {
  constructor() {
    super(HTTP_STATUS.NOT_FOUND, 'User not found');
    this.name = 'UserNotFoundError';
  }
}
