import HTTP_STATUS from '../constants/httpStatus.ts';
import { AppError } from './app-error.ts';

export class EntityNotFoundError extends AppError {
  constructor(entityName: string, identifier?: string | number) { 

    const message = identifier 
      ? `${entityName} with identifier [${identifier}] not found` 
      : `${entityName} not found`;

    super(HTTP_STATUS.NOT_FOUND, message); 

    this.name = 'EntityNotFoundError'; 
    
    if (Error.captureStackTrace) {
      Error.captureStackTrace(this, EntityNotFoundError);
    }
  }
}