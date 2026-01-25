import { type UserWithRelations } from '../types/user.types.ts';

export interface UserRepository {
  findByEmail(email: string): Promise<UserWithRelations>;
  findById(id: number): Promise<UserWithRelations>;
}
