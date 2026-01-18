export interface Role {
  id: number;
  name: string;
  createdAt: Date;
}

export interface UserBase {
  id: number;
  email: string;
  username: string;
}

export interface UserWithRoles extends UserBase {
  roles: Role[];
}

// Request types
export interface RegisterRequest {
  email: string;
  password: string;
  username: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface ChangePasswordRequest {
  oldPassword: string;
  newPassword: string;
}

// Response types
export interface AuthResponse {
  user: UserWithRoles;
  token: string;
}

// JWT Payload type
export interface JwtPayload {
  id: number;
}
