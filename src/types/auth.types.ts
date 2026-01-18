export interface RegisterRequest {
  email: string;
  password: string;
  username: string;
}

export interface AuthResponse {
  user: {
    id: number;
    email: string;
    username: string | null;
  };
  token: string;
}

declare module 'express-serve-static-core' {
  interface Request {
    validatedData?: unknown;
  }
}
