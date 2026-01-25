declare global {
  namespace Express {
    interface Request {
      validatedData?: any;
    }
  }
}

export {};
