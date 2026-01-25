import type {
  RegisterRequest,
  AuthResponse,
  LoginRequest,
} from '../types/auth.types.ts';

export interface AuthService {
  register(data: RegisterRequest): Promise<AuthResponse>;
  login(data: LoginRequest): Promise<AuthResponse>;
}
